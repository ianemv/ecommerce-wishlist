# E-commerce Wishlist API

A Laravel-based REST API for an e-commerce wishlist system that allows users to manage their product wishlists with authentication.

## Features

- 🔐 **User Authentication** - Register, login, logout with token-based authentication
- 👥 **Role-Based Access Control** - Admin and user roles with middleware protection
- 📦 **Product Management** - CRUD operations for products (Admin only)
- ❤️ **Wishlist System** - Add/remove products to/from user wishlists
- 🧪 **Comprehensive Testing** - Unit and feature tests including role-based access
- 🐳 **Docker Support** - Easy deployment with Docker

## Tech Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Testing**: PHPUnit
- **PHP Version**: 8.2+

## Installation

Choose one of the following installation methods:

### Option 1: Docker Setup

#### Prerequisites
- Docker and Docker Compose

#### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone git@github.com:ianemv/ecommerce-wishlist.git
   cd ecommerce-wishlist
   ```

2. **Start Docker containers**
  ```
    create .env file in docker directory (check .env.example)
    Database's db name,  username and password should also match with .env at root directory
  ```
   ```bash
   cd docker
   docker-compose up -d
   ```

3. **Install PHP dependencies and Sanctum**
   ```bash
   docker-compose exec app composer install
   ```

4. **Environment setup**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   ```

5. **Storage directory persmission**
  ```
  docker-compose exec app bash -c "chmod -R ug+rw storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"
  ```

6. **Database setup**
   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

The API will be available at `http://localhost:8000`

### Option 2: Local Development Setup

#### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM (for frontend assets)

#### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone git@github.com:ianemv/ecommerce-wishlist.git
   cd ecommerce-wishlist
   ```

2. **Install PHP dependencies and Sanctum**
   ```bash
   composer install
   ```
3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Configure Sanctum (optional)**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

### Default Admin Credentials

After running the seeders, you can use these credentials for admin access:
- **Email**: `admin@ecommerce.com`
- **Password**: `admin123`
- **Role**: `admin`

## API Documentation

### Authentication Endpoints

#### Register
- **POST** `/api/register`
- **Body**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```

#### Login
- **POST** `/api/login`
- **Body**:
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```

#### Logout
- **POST** `/api/logout`
- **Headers**: `Authorization: Bearer {token}`

#### Get Current User
- **GET** `/api/me`
- **Headers**: `Authorization: Bearer {token}`

### Product Endpoints

#### Get All Products
- **GET** `/api/products`

#### Create Product (Admin Only)
- **POST** `/api/products`
- **Headers**: `Authorization: Bearer {admin_token}`
- **Body**:
  ```json
  {
    "name": "Product Name",
    "price": 99.99,
    "description": "Product description"
  }
  ```

#### Get Product (Admin Only)
- **GET** `/api/products/{id}`
- **Headers**: `Authorization: Bearer {admin_token}`

#### Update Product (Admin Only)
- **PUT** `/api/products/{id}`
- **Headers**: `Authorization: Bearer {admin_token}`
- **Body**:
  ```json
  {
    "name": "Updated Product Name",
    "price": 149.99,
    "description": "Updated description"
  }
  ```

#### Delete Product (Admin Only)
- **DELETE** `/api/products/{id}`
- **Headers**: `Authorization: Bearer {admin_token}`

### Wishlist Endpoints

#### Get User's Wishlist
- **GET** `/api/wishlist`
- **Headers**: `Authorization: Bearer {token}`

#### Add Product to Wishlist
- **POST** `/api/wishlist`
- **Headers**: `Authorization: Bearer {token}`
- **Body**:
  ```json
  {
    "product_id": 1
  }
  ```

#### Remove Product from Wishlist
- **DELETE** `/api/wishlist/{product_id}`
- **Headers**: `Authorization: Bearer {token}`

### Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors if applicable
  }
}
```

## Database Structure

### Users Table
- id (Primary Key)
- name
- email (Unique)
- password
- role (Default: 'user', Values: 'user', 'admin')
- email_verified_at
- created_at
- updated_at

### Products Table
- id (Primary Key)
- name
- price (Decimal)
- description (Text, Nullable)
- created_at
- updated_at

### Wishlists Table (Pivot)
- id (Primary Key)
- user_id (Foreign Key)
- product_id (Foreign Key)
- created_at
- updated_at
- Unique constraint on (user_id, product_id)

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### Test Coverage

The application includes comprehensive tests covering:
- **Authentication**: Registration, login, logout, profile access
- **Role-Based Access**: Admin middleware, role validation, permission testing
- **Products**: CRUD operations, validation, authorization
- **Wishlist**: Adding/removing products, duplicate prevention
- **Models**: Relationships, attributes, business logic

## Docker Commands

### Common Docker Operations

```bash
# Start containers
cd docker && docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash

# Run Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan test

# Install/update dependencies
docker-compose exec app composer install
docker-compose exec app composer update

# Clear caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### Docker Services
- **app**: PHP/Laravel application (PHP 8.3-FPM)
- **nginx**: Web server (Alpine)
- **db**: MySQL 8.0 database
- **db_test**: MySQL test database (Not use for now in this commit)
- **redis**: Redis cache

## Development

### Code Style
The project follows PSR-12 coding standards. Use Laravel Pint for formatting:

```bash
./vendor/bin/pint
```

### Database Migrations
```bash
# Create new migration
docker-compose app php artisan make:migration create_example_table

# Run migrations
docker-compose app php artisan migrate

# Rollback migrations
docker-compose app php artisan migrate:rollback
```

### Seeders
```bash
# Run all seeders
docker-compose app php artisan db:seed

# Run specific seeder
docker-compose app php artisan db:seed --class=ProductSeeder
```

## Security Features

- CSRF protection
- SQL injection prevention (Eloquent ORM)
- Password hashing (bcrypt)
- Token-based authentication (Sanctum)
- Request validation using Laravel Form Validation
- Rate limiting on authentication endpoints
