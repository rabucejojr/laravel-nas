# File Management System API

This repository contains a Laravel 11-based API for a File Management System (FMS). The system enables users to upload, retrieve, update, and delete files seamlessly, along with managing associated metadata.

## Features
- RESTful API endpoints.
- File upload with validation.
- Metadata management (e.g., file name, size, and type).
- File retrieval and download.
- File update and replacement.
- File deletion.

## Requirements
- PHP 8.1 or later
- Composer
- Laravel 11
- MySQL or any other database supported by Laravel
- Node.js (for frontend dependencies if applicable)

## Installation
1. Clone the repository:
    ```bash
    git clone https://github.com/rabucejojr/laravel-api.git
    cd laravel-api
    ```

2. Install dependencies:
    ```bash
    composer install
    npm install
    ```

3. Configure environment variables:
    - Copy `.env.example` to `.env`:
      ```bash
      cp .env.example .env
      ```
    - Update `.env` with your database and other configurations.

4. Generate the application key:
    ```bash
    php artisan key:generate
    ```

5. Run migrations:
    ```bash
    php artisan migrate
    ```

6. (Optional) Seed the database:
    ```bash
    php artisan db:seed
    ```

## Usage
### Start the development server
```bash
php artisan serve
```
The API will be available at `http://127.0.0.1:8000`.

### API Endpoints
#### Files
- **GET** `/api/files` - List all files.
- **GET** `/api/files/{id}` - Retrieve a specific file.
- **POST** `/api/files` - Upload a new file.
  - Parameters:
    - `name` (string): Name of the file.
    - `file` (file): The file to upload.
- **PUT** `/api/files/{id}` - Update file metadata or replace the file.
  - Parameters:
    - `name` (string): Updated name of the file.
    - `file` (file): New file to replace the existing one.
- **DELETE** `/api/files/{id}` - Delete a file.

### Validation Rules
- File uploads are validated to ensure:
  - File size is within 10MB.
  - File type is one of the allowed MIME types (e.g., `image/png`, `application/pdf`).
- Both `name` and `file` fields are required for uploads and updates.

## Testing
Run tests using PHPUnit:
```bash
php artisan test
```

## Frontend Integration
This API is designed to work with any frontend framework (e.g., Vue.js, React). For a Laravel + Inertia.js implementation, ensure routes are properly configured.


## Contributing
Contributions are welcome! Please fork this repository and submit a pull request with your improvements.


## Contact
For any inquiries or support, please contact [rogerhapay@gmail.com].

