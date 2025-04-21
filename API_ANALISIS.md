
# 🔍 Análisis del uso de la API de CoinMarketCap

## 🎯 Objetivo

Investigar, analizar y seleccionar los endpoints más adecuados de la API de CoinMarketCap para obtener datos en tiempo real y detalles gráficos de criptomonedas. Se utilizó Postman para validar manualmente las respuestas antes de implementarlas en el sistema.

---

## 🔧 Herramientas utilizadas

- **Postman**: para probar los endpoints y validar el formato de los datos recibidos.
- **CoinMarketCap Developer Portal**: para consultar la documentación oficial.
- **Clave API gratuita**: obtenida desde [https://coinmarketcap.com/api](https://coinmarketcap.com/api)

---

## ✅ Endpoints seleccionados

### 1. `/v1/cryptocurrency/listings/latest`

**Función**: Obtener una lista actualizada de criptomonedas con sus datos clave como precio, market cap, volumen, cambio porcentual, etc.

**Tipo de petición**: `GET`  
**Requiere API Key**: ✅  
**Parámetros útiles**:
- `start` (opcional): número de inicio de la lista
- `limit`: número máximo de criptomonedas a mostrar
- `convert`: moneda de conversión (USD, EUR, etc.)

**Ejemplo de uso en Postman**:
```
GET https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest?limit=100&convert=USD
Headers:
X-CMC_PRO_API_KEY: TU_API_KEY
```

**Campos importantes de la respuesta JSON**:
- `data[i].id`
- `data[i].name`
- `data[i].symbol`
- `data[i].quote.USD.price`
- `data[i].quote.USD.market_cap`
- `data[i].quote.USD.percent_change_24h`

### 2. `/v1/cryptocurrency/info`

**Función**: Obtener detalles adicionales como el logo de cada criptomoneda usando su ID.

**Tipo de petición**: `GET`  
**Requiere API Key**: ✅  
**Parámetros**:
- `id`: uno o varios IDs de criptomonedas separados por coma

**Ejemplo de uso en Postman**:
```
GET https://pro-api.coinmarketcap.com/v1/cryptocurrency/info?id=1,1027,825
Headers:
X-CMC_PRO_API_KEY: TU_API_KEY
```

**Campos importantes de la respuesta JSON**:
- `data[id].logo`
- `data[id].description` (opcional)
- `data[id].symbol`

---

## 📌 Resultados de las pruebas con Postman

- Se comprobó que los datos llegaban en formato `JSON` correctamente estructurado.
- Se validó que el endpoint de listings no incluía logos, por eso fue necesario hacer la segunda llamada (`info`).
- Las respuestas tenían buena velocidad y eran adecuadas para actualización cada 30 segundos.
- Se comprobó la validez de los headers y estructura antes de implementar en PHP.

---

## 🧠 Conclusión técnica

La API de CoinMarketCap ofrece una estructura clara y robusta para manejar precios en tiempo real. Se seleccionaron los endpoints más eficientes para lograr el objetivo del proyecto con mínimo esfuerzo de procesamiento y máxima calidad visual.

