# Simple File Manager

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.1-777BB4.svg)
![Laravel](https://img.shields.io/badge/Laravel-9.0-FF2D20.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3.0-4FC08D.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

A secure file manager allowing authenticated users to manage their own files through a modern and intuitive interface.

## 📋 Features

- **Secure Authentication**: Registration, login, and user profile management
- **File Isolation**: Each user can only access their own files
- **File Upload**: Support for PDF, DOCX, PNG, JPG, ODT with size limits
- **Complete Management**: List, search, sort, filter, download, and delete files

## 🚀 Installation

### Prerequisites
- [Docker](https://www.docker.com/products/docker-desktop/) (with Docker Compose)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/KheroubiAymen/filemanager.git
   cd filemanager
   ```

2. **Configure the environment file**
   ```bash
   # Copy the example file
   cp .env.example .env
   
   # Modify parameters if necessary, particularly:
   # - DB_HOST=db
   # - DB_DATABASE=filemanager
   # - DB_USERNAME=filemanager
   # - DB_PASSWORD=filemanager
   ```

3. **Launch Docker containers**
   ```bash
   docker-compose up -d
   ```

4. **Follow the logs to wait for the configuration to complete**
   ```bash
   docker-compose logs -f
   ```
   The container performs many initialization tasks:
   - Installation of PHP dependencies (Composer)
   - Installation of JavaScript dependencies (npm)
   - Compilation of assets (npm run build)
   - Creation of storage symbolic link
   - Application key generation
   - Database migration execution
   - Configuration and route caching
   
   Wait until you see "Apache/2.4.x (Debian) PHP/8.1.x configured -- resuming normal operations" in the logs, which indicates that the application is ready.

5. **Access the application**
   ```
   http://localhost:8080
   ```
   If you configured a different port in docker-compose.yml, use it instead of 8080.

### Troubleshooting

- **If port 8080 is already in use**, modify the port in docker-compose.yml:
  ```yaml
  ports:
    - "8081:80"  # Change 8080 to another available port
  ```

- **If initialization fails**, you can run the commands manually:
  ```bash
  # Check logs to identify the error
  docker-compose logs -f
  
  # Execute commands manually if necessary
  docker-compose exec app composer install
  docker-compose exec app npm install
  docker-compose exec app npm run build
  docker-compose exec app php artisan storage:link
  docker-compose exec app php artisan migrate --force
  docker-compose exec app chmod -R 777 storage bootstrap/cache
  ```
  
- **To completely restart the application**:
  ```bash
  docker-compose down
  docker-compose up -d
  ```

## 🏗 Architecture Choices

### Backend Architecture (Laravel)

- **Laravel 9 with PHP 8.1**: Modern, secure, and high-performance framework
- **MVC Architecture**: Clear separation of responsibilities
- **Authentication Middleware**: Route protection and user data isolation
- **Eloquent ORM**: Secure database interactions
- **Storage API**: Optimized file management with user isolation

### Frontend Architecture

- **Vue.js 3**: Reactive framework for a fluid user interface
- **Inertia.js**: Seamless communication between Laravel and Vue without a separate API
- **Tailwind CSS**: Responsive and modern design
- **Vite Compilation**: Optimal performance in production

### Containerization

- **Multi-container Docker**: Separation of services (application, database)
- **Automated Configuration**: Simplified installation and configuration
- **Isolated Environment**: Ensures identical operation across all machines
- **Persistent Volumes**: Data preservation between restarts

### Security

- **Robust Authentication**: Protection against brute force attacks
- **Strict Validation**: Verification of file types and sizes
- **Data Isolation**: Each user only accesses their own files
- **CSRF Protection**: Form and request security

## 📄 License

This project is licensed under the MIT License. See the LICENSE file for more details.
