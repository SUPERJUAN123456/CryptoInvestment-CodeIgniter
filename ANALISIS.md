
# 📊 Análisis del Proyecto – CryptoInvestment

## 🧩 Objetivo General

Crear una aplicación web que permita visualizar en tiempo real la información de criptomonedas, con historial de precios, favoritos personalizados y gráficos interactivos, utilizando la API de CoinMarketCap y tecnologías modernas (PHP, JavaScript, Chart.js, etc.).

---

## ✅ Requisitos Funcionales (RF)

| Código | Descripción |
|--------|-------------|
| RF1 | Mostrar en tiempo real una tabla con información de las criptomonedas (precio, capitalización, variación) |
| RF2 | Permitir buscar criptomonedas por nombre |
| RF3 | Permitir marcar y desmarcar criptomonedas como favoritas |
| RF4 | Mostrar gráfico histórico del precio de una criptomoneda seleccionada |
| RF5 | Permitir filtrar los datos del gráfico por un rango de fechas |
| RF6 | Guardar datos históricos de precios en una base de datos MySQL |
| RF7 | Mostrar flechas visuales si el precio sube o baja en tiempo real |

---

## ❌ Requisitos No Funcionales (RNF)

| Código | Descripción |
|--------|-------------|
| RNF1 | La interfaz debe ser responsive y amigable |
| RNF2 | Los datos deben actualizarse automáticamente sin recargar la página |
| RNF3 | El sistema debe estar estructurado con un framework PHP (CodeIgniter) |
| RNF4 | El tiempo de respuesta al actualizar datos debe ser menor a 3 segundos |
| RNF5 | El sistema debe poder correr localmente bajo XAMPP |

---

## 🗃️ Estructura de datos definida

### 1. Datos desde la API (CoinMarketCap)

**Endpoint utilizado:**
- `/v1/cryptocurrency/listings/latest`
- `/v1/cryptocurrency/info`

**Datos capturados para cada criptomoneda:**

| Campo | Descripción |
|-------|-------------|
| name | Nombre completo de la criptomoneda |
| symbol | Símbolo corto (BTC, ETH, etc.) |
| price | Precio en USD |
| market_cap | Capitalización de mercado |
| percent_change_24h | Variación porcentual en 24h |
| logo | URL del logo |

### 2. Base de datos MySQL

#### Tabla: `historical_prices`

| Campo               | Tipo         | Descripción                       |
|---------------------|--------------|-----------------------------------|
| id                  | INT (AI, PK) | ID único                          |
| crypto_name         | VARCHAR      | Nombre de la criptomoneda         |
| symbol              | VARCHAR      | Símbolo                           |
| price               | DECIMAL      | Precio en USD                     |
| market_cap          | DECIMAL      | Capitalización de mercado         |
| percent_change_24h  | DECIMAL      | Cambio porcentual en 24h          |
| timestamp           | DATETIME     | Fecha y hora de la inserción      |

#### Tabla: `favorite_cryptos`

| Campo     | Tipo     | Descripción                  |
|-----------|----------|------------------------------|
| symbol    | VARCHAR  | Símbolo único de la cripto   |

---

## 🛠 Estructura del sistema

### Backend (CodeIgniter)

- `Crypto::index()` → carga la vista principal
- `Crypto::data()` → obtiene y guarda datos en la base de datos
- `Crypto::historicalData()` → entrega datos históricos filtrados por fecha
- `Crypto::favorites()` → agrega o elimina favoritos

### Frontend

- `crypto_view.php` (Vista principal)
- JS dinámico con:
  - fetch cada 30 segundos
  - gestión de favoritos
  - búsqueda en vivo
  - generación de gráficos con filtros
