<?php
//Esta clase es el corazón del backend de la aplicación. Centraliza toda
// la lógica para mostrar la vista principal, consultar datos desde la API de CoinMarketCap,
//guardar el historial en base de datos, y gestionar favoritos.
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Crypto extends Controller
{

    // Carga la vista principal de la aplicación (crypto_view.php).
    public function index()
    {
        return view('crypto_view'); // Aquí se carga el index convertido a vista
    }

    //🔹 Se conecta a la API de CoinMarketCap y obtiene la lista de criptomonedas más recientes.
    //🔹 También realiza una segunda petición para obtener los logos de las monedas.
    //🔹 Guarda cada precio en la base de datos en la tabla historical_prices.
    //🔹 Consulta la tabla favorite_cryptos para marcar favoritos.
    //🔹 Devuelve todos los datos como JSON al frontend.

    public function data()
    {
        header('Content-Type: application/json');

        $apiKey = '30a28c76-3dcc-4a6e-aed7-23c428f895d6';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-CMC_PRO_API_KEY: $apiKey"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($data && isset($data['data'])) {
            $cryptoIds = array_column($data['data'], 'id');
            $idString = implode(',', $cryptoIds);

            $infoCh = curl_init();
            curl_setopt($infoCh, CURLOPT_URL, "https://pro-api.coinmarketcap.com/v1/cryptocurrency/info?id=$idString");
            curl_setopt($infoCh, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($infoCh, CURLOPT_HTTPHEADER, [
                "X-CMC_PRO_API_KEY: $apiKey"
            ]);
            $infoResponse = curl_exec($infoCh);
            curl_close($infoCh);

            $infoData = json_decode($infoResponse, true);

            // Conexión DB (usando MySQLi por ahora para mantenerlo igual)
            $conn = new \mysqli('localhost', 'root', '1234', 'crypto_db');

            if ($conn->connect_error) {
                echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
                return;
            }

            $favSymbols = [];
            $favQuery = $conn->query("SELECT symbol FROM favorite_cryptos");
            while ($row = $favQuery->fetch_assoc()) {
                $favSymbols[] = $row['symbol'];
            }

            $stmt = $conn->prepare("INSERT INTO historical_prices (crypto_name, symbol, price, market_cap, percent_change_24h) VALUES (?, ?, ?, ?, ?)");

            foreach ($data['data'] as &$crypto) {
                $id = $crypto['id'];
                $name = $crypto['name'];
                $symbol = $crypto['symbol'];
                $price = $crypto['quote']['USD']['price'];
                $marketCap = $crypto['quote']['USD']['market_cap'];
                $change24h = $crypto['quote']['USD']['percent_change_24h'];
                $logo = $infoData['data'][$id]['logo'] ?? null;

                $crypto['logo'] = $logo;
                $crypto['favorite'] = in_array($symbol, $favSymbols);

                $stmt->bind_param("ssddd", $name, $symbol, $price, $marketCap, $change24h);
                $stmt->execute();
            }

            $stmt->close();
            $conn->close();
        }

        echo json_encode($data);
    }

    //🔹 Recibe el nombre de una criptomoneda (GET['name'])
    //🔹 Consulta la tabla historical_prices y devuelve un historial de precios.
    //🔹 Soporta filtros por fecha (start, end) si se solicitan.
    //🔹 Ordena los resultados por fecha ascendente y los devuelve como JSON.
    // Esta función alimenta el gráfico histórico que aparece al hacer clic en una cripto.

    public function historicalData()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['name'])) {
            echo json_encode(['error' => 'Falta parámetro "name"']);
            return;
        }

        $name = $_GET['name'];
        $servername = "localhost";
        $username = "root";
        $password = "1234";
        $database = "crypto_db";

        $conn = new \mysqli($servername, $username, $password, $database);

        if ($conn->connect_error) {
            echo json_encode(['error' => 'Conexión fallida: ' . $conn->connect_error]);
            return;
        }

        $query = "SELECT timestamp, price FROM historical_prices WHERE crypto_name = ?";
        $params = [$name];
        $types = "s";

        if (isset($_GET['start'])) {
            $query .= " AND timestamp >= ?";
            $params[] = $_GET['start'] . " 00:00:00";
            $types .= "s";
        }

        if (isset($_GET['end'])) {
            $query .= " AND timestamp <= ?";
            $params[] = $_GET['end'] . " 23:59:59";
            $types .= "s";
        }

        $query .= " ORDER BY timestamp ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'time' => $row['timestamp'],
                'price' => floatval($row['price'])
            ];
        }

        echo json_encode(['data' => $data]);

        $stmt->close();
        $conn->close();
    }

    //Recibe una acción desde el frontend (POST):
    //"add": guarda la criptomoneda como favorita en la tabla favorite_cryptos.
    //remove": la elimina de esa tabla.

    public function favorites()
    {
        header('Content-Type: application/json');

        $conn = new \mysqli('localhost', 'root', '1234', 'crypto_db');

        if ($conn->connect_error) {
            echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
            return;
        }

        $symbol = $_POST['symbol'] ?? '';
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $stmt = $conn->prepare("REPLACE INTO favorite_cryptos (symbol) VALUES (?)");
            $stmt->bind_param("s", $symbol);
            $stmt->execute();
        } elseif ($action === 'remove') {
            $stmt = $conn->prepare("DELETE FROM favorite_cryptos WHERE symbol = ?");
            $stmt->bind_param("s", $symbol);
            $stmt->execute();
        }

        $conn->close();
        echo json_encode(['success' => true]);
    }
}
