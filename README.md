# Instructions on how to run the project

### Prerequisites

Ensure you have the following installed:

- **PHP** v8.4.4 (with Zend Engine v4.4.4)
- **Composer** v2.8.5
- **Node.js** v22.14.0
- **MySQL** v8.4.4
- **Laravel** v11.44.0
- **Vue.js** v3.5.13

*Note: These versions are recommended but not mandatory; other versions will probably work as well.*

### Database Configuration

Create a `.env` file with the following database connection variables:

```ini
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mydb
DB_USERNAME=root
DB_PASSWORD=
```

You can copy from the `.env.example` file using:
```bash
cp .env.example .env
```

### Installation Steps

1. **Install PHP dependencies:**
```bash
composer install
```

2. **Install Node.js dependencies (optional):**
```bash
npm install
```

3. **Generate application key:**
```bash
php artisan key:generate
```

4. **Create symbolic link for storage:**
```bash
php artisan storage:link
```

5. **Set up the database:**
   - Ensure a database named `mydb` exists (or the name used in `.env`).

6. **Run database migrations:**
```bash
php artisan migrate
```

### CSV File Requirement

Place the file `product_categories.csv` in the following directory:
```plaintext
ProductCategoriesTask\storage\app\product_categories.csv
```
*If missing, the application will throw a 'file not found' error.*

### Running the Application

Open multiple terminals and run these commands:

- **Start the application:**
```bash
php artisan serve
```

- **Start the queue worker:**
```bash
php artisan queue:work
```

- **Import product categories from the CSV file:**
```bash
php artisan product:import product_categories.csv
```

### Testing the APIs

Use tools like **Postman** to test the available endpoints.

# API Routes Documentation

This document provides a brief description of the available API endpoints and their functionalities.

## Categories

- **GET /categories**
  - Retrieves a list of all categories.

- **PUT /categories/{id}**
  - Updates the category with the specified ID.
  - **Path Parameter:** `id` - The ID of the category to update.

- **DELETE /categories/{id}**
  - Deletes the category with the specified ID.
  - **Path Parameter:** `id` - The ID of the category to delete.

- **GET /categories/{id}/products**
  - Retrieves all products associated with a specific category.
  - **Path Parameter:** `id` - The ID of the category.

## Products

- **GET /products**
  - Retrieves a list of all products.

- **PUT /products/{id}**
  - Updates the product with the specified ID.
  - **Path Parameter:** `id` - The ID of the product to update.

- **DELETE /products/{id}**
  - Deletes the product with the specified ID.
  - **Path Parameter:** `id` - The ID of the product to delete.

## CSV Generation

- **GET /products/generate-csv/{categoryId}**
  - Generates a link to download a CSV file containing the products of a specific category.
  - **Path Parameter:** `categoryId` - The ID of the category for which the CSV should be generated.


**Enjoy! 😃**
