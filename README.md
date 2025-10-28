# 🌍 Country Currency & Exchange API

A Laravel-based RESTful API that fetches **country data**, **currency exchange rates**, computes **estimated GDP**, and caches results in MySQL.  
It also generates a **summary image** of the top 5 countries by GDP.

---

## 🚀 Features

- Fetch and cache all countries with currency & exchange data  
- Compute estimated GDP dynamically using population and exchange rate  
- CRUD operations for country data  
- Filter and sort by region, currency, or GDP  
- Summary image generation using Intervention Image  
- Consistent JSON error responses  
- Resilient handling of external API failures  

---

## 🧩 Tech Stack

| Component | Description |
|------------|-------------|
| **Framework** | Laravel 12 |
| **Database** | MySQL |
| **HTTP Client** | Laravel HTTP (powered by Guzzle) |
| **Image Generation** | Intervention Image |
| **Language** | PHP 8.2+ |

---

## ⚙️ Installation & Setup

### 1️⃣ Clone the repository
```bash
git clone https://github.com/Usenmfon/currency-exchange-api
cd country-api

composer install

cp .env.example .env

```
### Update your .env:
```bash
APP_NAME=CountryAPI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=country_api
DB_USERNAME=root
DB_PASSWORD=secret
```

### Generate|migrations|package app key:
```bash
php artisan key:generate

Run migrations
php artisan migrate

Install Intervention Image
composer require intervention/image-laravel
```

### API Overview
## Refresh Countries
```bash
Fetch data from external APIs, compute estimated GDP, and cache in DB.

POST /api/countries/refresh


✅ On success:

{
  "message": "Countries refreshed successfully"
}

❌ If external API fails:

{
  "error": "External data source unavailable",
  "details": "Could not fetch data from Countries API"
}

| Field               | Type      | Description                                    |
| ------------------- | --------- | ---------------------------------------------- |
| `id`                | int       | Auto-generated                                 |
| `name`              | string    | Country name                                   |
| `capital`           | string    | Capital city                                   |
| `region`            | string    | Region name                                    |
| `population`        | bigint    | Population count                               |
| `currency_code`     | string    | 3-letter ISO currency code                     |
| `exchange_rate`     | decimal   | Rate against USD                               |
| `estimated_gdp`     | decimal   | Population × random(1000–2000) ÷ exchange_rate |
| `flag_url`          | string    | Flag image URL                                 |
| `last_refreshed_at` | timestamp | When record was last updated                   |

