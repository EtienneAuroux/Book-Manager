
# Yaraku Book Manager

A Laravel-based web application to manage a list of books. 
Yaraku Book Manager allows you to add, update, and delete books in a database.
You can search for books in your title by title and author.
You can also sort the list of books by alphabetical order or reversed alphabetical order.
CSV and XML exports are supported for the full database or either only the titles or only the authors.

The project is dockerized and thoroughly tested.

---

## Tech Stack

- Laravel 10+
- PHP 8.1+
- Docker + Laravel Sail
- SQLite for testing
- Blade templates + Bootstrap 5
---

## Getting Started
### 1. Clone the repo

```bash
git clone https://github.com/EtienneAuroux/yaraku-code-test.git
cd yaraku-book-manager
```

### 2. Start Docker

```bash
./vendor/bin/sail up -d
```

### 3. Set up environment

```bash
cp .env.example .env
./vendor/bin/sail artisan key:generate
```

If you are running Docker+Sail and MySQL like me, you should update your .env file with:
```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. Run migrations

```bash
./vendor/bin/sail artisan migrate
```

---

## Running Tests

Tests are available for (i) creating, updating, deleting books
(ii) exporting CSV/XML files (includes edge cases of invalid export parameters and empty exports)
(iii) sorting and (iv) searching (includes edge case of searching for a non-existing book).

Tests run against an **in-memory SQLite database** (`.env.testing`).

To run tests:

```bash
./vendor/bin/sail artisan test
```

---

## Project Structure

- `app/Models/Book.php` — Book model
- `app/Http/Controllers/BookController.php` — Handles all logic
- `resources/views/books/index.blade.php` — Frontend (HTML, Bootstrap, JS)
- `tests/Feature/BookTest.php` — Tests

---

## Export Routes

| Route | Description |
|-------|-------------|
| `/books/export/all/csv` | CSV with title and author |
| `/books/export/titles/csv` | CSV with titles only |
| `/books/export/authors/csv` | CSV with authors only |
| `/books/export/all/xml` | XML with title and author |
| `/books/export/titles/xml` | XML with titles only |
| `/books/export/authors/xml` | XML with authors only |

---

## MIT License

See LICENSE.md for details.

---

## Acknowledgements

Thanks to Yaraku for the assignment and the opportunity to learn a bit about PHP with Laravel and Docker.
