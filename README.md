# 🌍 Translation Management Service

Rest APIs built with Laravel to manage multilingual translations efficiently, with token-based authentication, tagging support, and optimized export functionality.

---

## 🚀 Features

- Token-based API authentication (`api_token`)
- Translation CRUD with locale and tag filtering
- Streamed JSON export for high performance (100K+ records)
- Swagger/OpenAPI annotations included
- Docker-based development environment

---

## 🐳 Docker Setup

### 📦 Requirements

- Docker
- Docker Compose

---

### ⚙️ Installation Steps

```bash
# Clone the repository
git clone https://github.com/jawadzaib/translation-management-service
cd translation-management-service

# Create .env file
cp .env.example .env
```

Update the DB section in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

```bash
# Start Docker containers
docker-compose up -d --build

# Install dependencies
docker exec -it app composer install

# Generate application key
docker exec -it app php artisan key:generate

# Run migrations and seeders
docker exec -it app php artisan migrate --seed
```

---

## 🔐 Authentication

This API uses a manually generated `api_token` stored in the `users` table.

### Sample Login Response

```json
{
  "token": "your-generated-api-token"
}
```

Use this token in requests:

```http
Authorization: Bearer your-generated-api-token
```

---

## 🧪 Running Tests

```bash
# Run all tests
docker exec -it app php artisan test

# Run a specific test
docker exec -it app php artisan test tests/Feature/TranslationsControllerTest.php
```

---

## 🧰 Common Artisan Commands (in Docker)

```bash
docker exec -it app php artisan migrate:fresh --seed
docker exec -it app php artisan route:list
docker exec -it app php artisan config:cache
```

---

## 📤 Export Translations

Export all translations for a locale as JSON:

```http
GET /api/translations/export?locale=en
```

Response:

```json
{
  "home.title": "Welcome",
  "button.save": "Save"
}
```

Optimized for large exports using `stream()` with chunked DB reads.

---

## 🔧 API Endpoints Summary

| Method | URI                           | Description                        |
|--------|-------------------------------|------------------------------------|
| GET    | /api/translations             | List all translations              |
| POST   | /api/translations             | Create a new translation           |
| GET    | /api/translations/{id}        | View a specific translation        |
| PUT    | /api/translations/{id}        | Update a translation               |
| DELETE | /api/translations/{id}        | Delete a translation               |
| GET    | /api/translations/export      | Export translations as JSON        |

---

## 📝 Swagger Documentation

All controller methods are annotated with [OpenAPI]
Here is documentation link: `http://localhost:8000/api/documentation`

---

## 🧼 Cleanup

```bash
# Stop and remove Docker containers and volumes
docker-compose down -v
```

---

