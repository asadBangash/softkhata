# Soft Khata API

Node.js REST API for the Soft Khata credit ledger mobile app. Uses **Express** and **MySQL**.

## Local setup (Laragon)

1. Start **Laragon** and ensure **MySQL** is running.
2. Create the database and tables:

   ```bash
   mysql -u root softkhata < db/schema.sql
   ```

   Or create database `softkhata` in HeidiSQL/phpMyAdmin and import `db/schema.sql`.

3. Copy environment file and adjust if needed:

   ```bash
   copy .env.example .env
   ```

   Default Laragon values: `DB_HOST=localhost`, `DB_USER=root`, empty password, `DB_NAME=softkhata`.

4. Install and run:

   ```bash
   npm install
   npm start
   ```

API base URL: `http://localhost:5000/api/`

## Environment variables

| Variable       | Description        |
|----------------|--------------------|
| `DB_HOST`      | MySQL host         |
| `DB_PORT`      | MySQL port (3306)  |
| `DB_USER`      | MySQL user         |
| `DB_PASSWORD`  | MySQL password     |
| `DB_NAME`      | Database name      |
| `TOKEN_SECRET` | JWT signing secret |
| `PORT`         | HTTP port (5000)   |

## Endpoints (prefix `/api`)

- `POST /auth/signup`, `POST /auth/login`
- `POST /user/get`, `POST /user/update`
- `POST /customer/add`, `get`, `edit`, `delete`
- `POST /entry/add`, `get`, `update`, `delete`, `details`
