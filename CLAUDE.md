# ApiProject - Notas Técnicas Completas

REST API pura en PHP 8.2 sin frameworks, con arquitectura profesional en capas. Este documento contiene notas técnicas detalladas sobre la arquitectura, setup y decisiones de diseño.

---

## Tabla de Contenidos

1. [Estado Actual](#estado-actual)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Arquitectura en Capas](#arquitectura-en-capas)
4. [Componentes Principales](#componentes-principales)
5. [Decisiones Técnicas](#decisiones-técnicas)
6. [Configuración Docker](#configuración-docker)
7. [Base de Datos](#base-de-datos)
8. [Patrones de Diseño](#patrones-de-diseño)
9. [Credenciales para Testing](#credenciales-para-testing)
10. [Comandos Útiles](#comandos-útiles)
11. [Notas de Desarrollo](#notas-de-desarrollo)
12. [Roadmap y Consideraciones Futuras](#roadmap-y-consideraciones-futuras)

---

## Estado Actual

### Completado

- ✅ Docker + PHP 8.2-Apache + MySQL 8.0
- ✅ Composer integrado con autoload PSR-4
- ✅ Base de datos completa: 5 tablas + 40+ registros de prueba
- ✅ Front controller en `public/index.php`
- ✅ `.htaccess` reescritura de URL
- ✅ Variables de entorno (`.env` + `.env.example`)
- ✅ PHP extensions: pdo_mysql, mbstring, curl, intl, zip, xml, opcache, xdebug
- ✅ Router profesional con parámetros dinámicos
- ✅ Middleware Pipeline (CORS activo)
- ✅ Repository Pattern
- ✅ Service Layer
- ✅ Autenticación JWT
- ✅ Autorización basada en roles

### Endpoints Implementados

**GET /api/v1/categories** — Listado paginado de categorías
- Parámetros: `page`, `per_page` (1-100)
- Respuesta: `{status, data[], meta{total, page, per_page, total_pages}}`

**GET /api/v1/categories/{id}** — Detalle de categoría
- Validación de existencia
- Respuesta: `{status, data[], meta: null}`

**POST /api/v1/auth/login** — Autenticación
- Genera JWT token
- Respuesta: `{status, data{token, user}}`

**POST /api/v1/auth/register** — Registro de usuario
- Validación de campos
- Hash seguro de contraseña (bcrypt)
- Respuesta: `{status, data{id, name, email, role}}`

---

## Estructura del Proyecto

```
ApiProject/
├── public/                      # Raíz web (accesible por HTTP)
│   ├── index.php               # Front controller - punto de entrada
│   └── .htaccess               # Reescritura de URL para Apache
│
├── src/                         # Código fuente de la aplicación
│   ├── Core/                   # Clases núcleo del framework
│   │   ├── Request.php         # Manejo de solicitudes HTTP
│   │   ├── Response.php        # Respuestas JSON estandarizadas
│   │   ├── Router.php          # Enrutador con parámetros dinámicos
│   │   ├── Database.php        # Singleton PDO para BD
│   │   └── Middleware.php      # Interfaz base para middleware
│   │
│   ├── Controllers/            # Manejadores de HTTP (endpoints)
│   │   ├── CategoryController.php
│   │   ├── AuthController.php
│   │   └── (otros controllers)
│   │
│   ├── Services/               # Lógica de negocio
│   │   ├── CategoryService.php
│   │   ├── AuthService.php
│   │   └── (otros servicios)
│   │
│   ├── Repositories/           # Capa de acceso a datos
│   │   ├── CategoryRepository.php
│   │   └── (otros repositorios)
│   │
│   ├── Models/                 # Entidades (mapeo 1:1 con BD)
│   │   ├── Category.php
│   │   └── (otros modelos)
│   │
│   ├── Middleware/             # Implementaciones de middleware
│   │   ├── CorsMiddleware.php
│   │   ├── AuthMiddleware.php
│   │   └── (otros middleware)
│   │
│   └── routes.php              # Definición centralizada de rutas
│
├── database/
│   ├── migrations/             # Creación de tablas (SQL puro)
│   │   └── *.sql
│   └── seeders/               # Datos iniciales de prueba
│       └── *.sql
│
├── docker/                      # Configuración Docker
│   ├── php/
│   │   ├── Dockerfile         # Imagen PHP 8.2-Apache
│   │   └── entrypoint.sh       # Script de inicio del contenedor
│   └── mysql/
│       └── Dockerfile         # Imagen MySQL 8.0
│
├── cli/
│   └── setup.php               # Ejecuta migraciones y seeders
│
├── vendor/                      # Dependencias de Composer (auto-generado)
├── .env                        # Variables de entorno (local)
├── .env.example                # Plantilla de variables de entorno
├── composer.json               # Definición de dependencias PHP
├── composer.lock               # Versiones exactas de dependencias
├── docker-compose.yml          # Orquestación de contenedores
├── README.md                   # Documentación de usuario
└── CLAUDE.md                   # Este documento
```

---

## Arquitectura en Capas

### Modelo de Capas

La aplicación implementa una arquitectura clásica de 4 capas:

```
┌─────────────────────────────────────────┐
│   HTTP Request / Response (Servidor)    │
├─────────────────────────────────────────┤
│   Controllers (Capa de Presentación)    │  ← CategoryController, AuthController
│   Reciben solicitud, validan input,     │     Convierten DTOs a JSON
│   invocan servicios, retornan respuesta │
├─────────────────────────────────────────┤
│   Services (Capa de Lógica de Negocio)  │  ← CategoryService, AuthService
│   Reglas de negocio, validaciones,      │     Orquestación de operaciones
│   transformación de datos                │
├─────────────────────────────────────────┤
│   Repositories (Capa de Acceso a Datos) │  ← CategoryRepository
│   Consultas SQL, prepared statements,   │     Operaciones CRUD abstractas
│   manejo de PDO                          │
├─────────────────────────────────────────┤
│   Database (PDO Singleton)               │  ← Conexión a MySQL
│   Conexión y pool de BD                  │
└─────────────────────────────────────────┘
```

### Beneficios de esta arquitectura

1. **Separación de responsabilidades** — Cada capa tiene un rol específico
2. **Testabilidad** — Fácil crear mocks de cada capa
3. **Reutilización** — Services pueden usarse desde múltiples Controllers
4. **Mantenibilidad** — Cambios en BD no afectan Controllers
5. **Escalabilidad** — Agregar nuevos endpoints es rápido y predecible

### Flujo de Solicitud: GET /api/v1/categories

```
1. Cliente HTTP
   ↓
2. Apache recibe GET /api/v1/categories?page=1
   ↓
3. .htaccess redirige a public/index.php
   ↓
4. Carga Composer autoloader
   ↓
5. Carga variables de entorno desde .env
   ↓
6. Carga src/routes.php (instancia Router)
   ↓
7. Middleware Pipeline:
   ├── CorsMiddleware (agrega headers CORS)
   ├── AuthMiddleware (valida token si está protegida)
   └── [Siguiente Middleware]
   ↓
8. Router despacha a CategoryController::index()
   ↓
9. CategoryController recibe Request
   ├── Extrae parámetros: page=1, per_page=10
   ├── Invoca CategoryService::getCategoriesPaginated(1, 10)
   └── Instancia respuesta
   ↓
10. CategoryService
    ├── Realiza validaciones
    ├── Invoca CategoryRepository::paginate(1, 10)
    └── Transforma resultados
    ↓
11. CategoryRepository
    ├── Obtiene PDO desde Database::getInstance()
    ├── Prepara SQL: SELECT * FROM categories LIMIT ?, OFFSET ?
    ├── Ejecuta prepared statement (previene SQL injection)
    ├── Parsea filas en Category entities
    └── Retorna array de Category[]
    ↓
12. CategoryService
    ├── Transforma Category[] en DTOs
    ├── Calcula metadata (total, página actual, etc.)
    └── Retorna datos formateados
    ↓
13. CategoryController
    ├── Construye Response con status: "success"
    ├── Llama Response::success($data, $meta)
    └── Invoca send() que:
        • http_response_code(200)
        • Content-Type: application/json
        • echo json_encode({status, data, meta})
    ↓
14. Cliente HTTP recibe JSON:
    {
      "status": "success",
      "data": [{id, name, description, parent_id}, ...],
      "meta": {
        "total": 7,
        "page": 1,
        "per_page": 10,
        "total_pages": 1
      }
    }
```

### Flujo de Solicitud: POST /api/v1/auth/login

```
1. Cliente envía POST /api/v1/auth/login
   Body: {"email": "admin@example.com", "password": "admin123"}
   ↓
2. .htaccess redirige a public/index.php
   ↓
3. Inicialización estándar (autoloader, .env, routes)
   ↓
4. Middleware Pipeline:
   ├── CorsMiddleware (agrega headers)
   └── Sin autenticación (login no requiere token)
   ↓
5. Router despacha a AuthController::login()
   ↓
6. AuthController::login()
   ├── Obtiene body JSON
   ├── Extrae email y password
   ├── Validación básica (no vacíos)
   ├── Invoca AuthService::authenticate(email, password)
   └── Si falla: Response::error('Invalid credentials', 401)
   ↓
7. AuthService::authenticate()
   ├── Busca usuario por email
   ├── Si no existe: lanza exception
   ├── Verifica password con password_verify()
   ├── Si válido, genera JWT token:
   │   ├── Header: {"alg": "HS256", "typ": "JWT"}
   │   ├── Payload: {"user_id": 1, "email": "...", "role": "admin", "exp": ...}
   │   ├── Firma con APP_SECRET desde .env
   │   └── Retorna token encoded
   ├── Retorna usuario + token
   ↓
8. AuthController::login()
   ├── Response::success({"token": "...", "user": {...}})
   ├── http_response_code(200)
   └── Envía JSON
   ↓
9. Cliente recibe:
   {
     "status": "success",
     "data": {
       "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
       "user": {
         "id": 1,
         "name": "Admin User",
         "email": "admin@example.com",
         "role": "admin"
       }
     }
   }
```

---

## Componentes Principales

### Core Components

#### Request.php
Abstracción de `$_SERVER`, `$_GET`, `$_POST`, `$_FILES`:

```php
class Request {
    public function getMethod(): string        // GET, POST, etc.
    public function getPath(): string          // /api/v1/categories
    public function getQuery(): array          // $_GET
    public function getBody(): array           // json_decode($_POST)
    public function getAttribute(string $name) // Parámetros de ruta
    public function getHeader(string $name)    // Headers HTTP
}
```

#### Response.php
Respuestas JSON estandarizadas:

```php
class Response {
    public static function success($data, $meta = null): self
    public static function error(string $message, int $code = 500): self
    public static function validationError(array $errors): self
    public function send(): void  // Envía JSON + headers
}
```

#### Router.php
Enrutamiento profesional con soporte para:
- Rutas simples: `/api/v1/categories`
- Parámetros: `/api/v1/categories/{id}`
- Regex constraints: `/api/v1/categories/{id:\d+}`
- Middleware pipeline: `use(Middleware)`
- Multiple métodos HTTP: get(), post(), put(), delete()

#### Database.php
Singleton PDO:

```php
class Database {
    public static function getInstance(): PDO
    // Conexión reutilizada en toda la aplicación
}
```

### Middleware System

**Interfaz:**
```php
interface Middleware {
    public function handle(Request $request, callable $next): void;
}
```

**Flujo:**
```php
// En Router
$router->use(new CorsMiddleware());
$router->use(new AuthMiddleware());  // Solo si protected = true
$router->post('/endpoint', handler); // Handler se ejecuta al final de la cadena
```

Cada middleware recibe `$request` y `$next` (callable). Puede:
- Modificar request
- Llamar `$next()` para pasar al siguiente
- Detener ejecución sin llamar `$next()` (ej: auth falla)

**Implementaciones actuales:**

1. **CorsMiddleware** — Agrega headers CORS
   ```
   Access-Control-Allow-Origin: *
   Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
   Access-Control-Allow-Headers: Content-Type, Authorization
   ```

2. **AuthMiddleware** — Valida JWT en headers
   ```
   Extrae token de header Authorization: Bearer <token>
   Valida firma (HS256 con APP_SECRET)
   Valida expiración
   Agrega user data a request si válido
   Retorna 401 si inválido
   ```

---

## Decisiones Técnicas

### 1. Sin Framework (Framework-agnostic)

**Decisión:** Implementar arquitectura sin usar Laravel, Symfony, etc.

**Justificación:**
- Control completo de cada línea de código
- Sin overhead de framework innecesario
- Ideal para APIs REST simples
- Aprendizaje profundo de conceptos (Router, DI, Middleware)
- Peso total mínimo, mejor performance

**Desventajas reconocidas:**
- Más código boilerplate que frameworks
- Menos features out-of-the-box
- Menos ecosistema de packages

**Escalabilidad:** Si el proyecto crece significativamente (100+ endpoints), considerar migración a Symfony o Laravel.

### 2. PHP 8.2

**Decisión:** Usar PHP 8.2 (latest stable)

**Beneficios:**
- Constructor promotion (`public readonly int $id`)
- Match expressions (aunque no se usan aún)
- Named arguments
- Union types (`int|null`)
- Fiable en producción desde 2024

**Compatibilidad:** Requiere PHP >= 8.2 en `composer.json`

### 3. Arquitectura en Capas

**Decisión:** Controllers → Services → Repositories → Database

**Justificación:**
- Separación clara de responsabilidades
- Fácil de testear (cada capa independiente)
- Reutilización de Services en múltiples Controllers
- Cambios en BD no impactan Controllers
- Patrones reconocidos en industria

**Alternativas consideradas:**
- Anemic model (solo getters/setters) — descartado, poco valor
- Smart entities (lógica en modelos) — confunde responsabilidades

### 4. Repository Pattern

**Decisión:** Capa Repository abstrae acceso a datos

```php
// Controllers/Services NUNCA tocan SQL directamente
// Solo hablan con Repository

class CategoryService {
    public function __construct(private CategoryRepository $repo) {}
    public function getCategories() {
        return $this->repo->findAll();  // Abstracción
    }
}
```

**Beneficios:**
- Swap de BD sin cambiar lógica de negocio
- Testing: Mock Repository fácilmente
- SQL centralizado en un lugar

### 5. Front Controller Pattern

**Decisión:** Todas las solicitudes pasan por `public/index.php`

```php
// .htaccess redirige todo aquí
GET /api/v1/categories → public/index.php?path=/api/v1/categories
```

**Ventajas:**
- Punto único de entrada
- Carga consistente (autoloader, .env, rutas)
- Control sobre flujo HTTP

### 6. Document Root en `public/`

**Decisión:** Apache apunta a `/var/www/html/public`, no `/var/www/html`

```
DocumentRoot /var/www/html/public
```

**Seguridad:**
- `src/`, `.env`, `vendor/` NO accesibles vía web
- Ataque a través de path traversal limitado
- Separación clara entre assets públicos y código privado

### 7. Middleware Pipeline

**Decisión:** Chain of Responsibility para middleware

```php
$router->use(new CorsMiddleware());
$router->use(new AuthMiddleware());
$router->post('/protected', handler);
// Ejecución: CORS → Auth → handler
```

**Beneficios:**
- Middleware reutilizable entre rutas
- Fácil agregar/remover middleware
- Control de flujo claro

### 8. JWT para Autenticación

**Decisión:** Tokens JWT en lugar de sesiones/cookies

```
POST /api/v1/auth/login → Genera JWT
GET /api/v1/protected → Header: Authorization: Bearer <token>
```

**Ventajas:**
- Stateless (no requiere sesión en servidor)
- Ideal para APIs REST
- Compatible con múltiples clientes (web, móvil, IoT)
- Fácil de escalar (sin server-side session store)

**Token incluye:**
- `user_id`, `email`, `role` (payload)
- Firma con APP_SECRET
- Expiración (exp claim)

**Validación:**
- Valida firma (verifica que no fue modificado)
- Valida expiración
- Extrae datos desde payload

### 9. Respuesta JSON Estandarizada

**Decisión:** Formato consistente para todas las respuestas

**Éxito:**
```json
{
  "status": "success",
  "data": [...],
  "meta": {...}
}
```

**Error:**
```json
{
  "status": "error",
  "message": "Error description"
}
```

**Validación fallida:**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": "Email is required"
  }
}
```

**Beneficios:**
- Cliente sabe qué esperar
- Parseo consistente
- Headers HTTP para status code
- Meta para paginación, timestamps, etc.

### 10. MySQL 8.0 Nativa Password Authentication

**Decisión:** MySQL con `mysql_native_password` en lugar de `caching_sha2_password`

```dockerfile
ENV MYSQL_DEFAULT_AUTHENTICATION_PLUGIN=mysql_native_password
```

**Razón:**
- Compatible con DBeaver, MySQL Workbench
- Compatibilidad con clientes legacy
- DBeaver usa el protocolo nativo directamente

**En producción:**
- Considerar `caching_sha2_password` (más seguro)
- Requiere configuración específica en cliente

### 11. Variables de Entorno en .env

**Decisión:** Cargar desde archivo `.env` en `public/index.php`

```php
// public/index.php
require '.env'  // DB_HOST=localhost, DB_USER=api_user, etc.
$_ENV['DB_HOST']  // Disponible globalmente
```

**Ventajas:**
- No incluir `.env` en Git (en `.gitignore`)
- Variables secretas (passwords, keys) separadas de código
- Diferente config local vs staging vs producción

**Carga manual:** Sin librerías (vlucas/dotenv), implementamos nosotros mismos. Suficiente para caso de uso actual.

---

## Configuración Docker

### Docker Compose Structure

```yaml
version: '3.8'

services:
  api_php:        # Servicio PHP + Apache
    build:        # Construye desde Dockerfile
    ports:        # Mapea 80:80 (host:contenedor)
    environment:  # Variables de entorno del contenedor
    depends_on:   # Espera a que MySQL esté listo
    volumes:      # Mapea código local en contenedor

  api_mysql:      # Servicio MySQL
    image:        # mysql:8.0
    environment:  # MYSQL_DATABASE, MYSQL_USER, etc.
    ports:        # Mapea 3306:3306
    volumes:      # Persistencia de datos
```

### PHP Container (php:8.2-apache)

**Dockerfile:**
```dockerfile
FROM php:8.2-apache

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql mbstring curl intl zip xml

# Habilitar mod_rewrite para .htaccess
RUN a2enmod rewrite

# Cambiar DocumentRoot a public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Script de inicio
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
```

**Entrypoint (docker/php/entrypoint.sh):**
```bash
#!/bin/bash

# 1. Espera a que MySQL esté listo
wait-for-it mysql:3306 -s -- echo "MySQL is ready!"

# 2. Ejecuta migraciones y seeders
php /var/www/html/cli/setup.php

# 3. Inicia Apache en foreground
apache2-foreground
```

**Port mapping:**
- Host: `80`
- Contenedor: `80`
- Acceso: `http://localhost`

### MySQL Container (mysql:8.0)

**Dockerfile simple:**
```dockerfile
FROM mysql:8.0

ENV MYSQL_DEFAULT_AUTHENTICATION_PLUGIN=mysql_native_password
```

**Variables de entorno (docker-compose.yml):**
```yaml
environment:
  MYSQL_ROOT_PASSWORD: rootsecret
  MYSQL_DATABASE: api_db
  MYSQL_USER: api_user
  MYSQL_PASSWORD: secret
```

**Port mapping:**
- Host: `3306`
- Contenedor: `3306`
- Acceso desde host: `localhost:3306` (DBeaver, etc.)
- Acceso desde PHP container: `mysql:3306` (DNS interno)

**Persistencia:**
```yaml
volumes:
  - api_mysql_data:/var/lib/mysql
```
Datos persisten entre reinicios de contenedor.

### Volumes y Binding

**Code binding (desarrollo):**
```yaml
volumes:
  - .:/var/www/html
```
Cambios locales reflejados en contenedor inmediatamente.

**Data volume (persistencia):**
```yaml
volumes:
  api_mysql_data:
    driver: local
```
Datos de MySQL persisten aunque se destruya contenedor.

---

## Base de Datos

### Schema

#### Tabla: users

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'manager', 'viewer') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

**Datos de prueba:**
- admin@example.com / admin123 (admin)
- manager@example.com / manager123 (manager)
- viewer@example.com / viewer123 (viewer)

#### Tabla: categories

```sql
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  parent_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id)
)
```

**Datos:** 7 categorías jerárquicas (Electronics, Smartphones, Laptops, etc.)

#### Tabla: suppliers

```sql
CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

**Datos:** 4 proveedores

#### Tabla: products

```sql
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  category_id INT NOT NULL,
  supplier_id INT NOT NULL,
  sku VARCHAR(50) UNIQUE NOT NULL,
  stock INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
)
```

**Datos:** 20 productos con relaciones a categorías y proveedores

#### Tabla: inventory_movements

```sql
CREATE TABLE inventory_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type ENUM('in', 'out') NOT NULL,
  quantity INT NOT NULL,
  reference VARCHAR(255),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id)
)
```

**Datos:** 40+ movimientos (entrada/salida de stock)

### Relaciones

```
users
  └─ (roles: admin, manager, viewer)

categories
  └─ (self-referencing para jerarquía)

suppliers
  └─ (uno-a-muchos con products)

products
  ├─ category_id → categories(id)
  ├─ supplier_id → suppliers(id)
  └─ (uno-a-muchos con inventory_movements)

inventory_movements
  └─ product_id → products(id)
```

### Migraciones y Seeders

**Ubicación:**
- Migraciones: `database/migrations/`
- Seeders: `database/seeders/`

**Ejecución:**
- `cli/setup.php` ejecuta todas las migraciones y seeders al iniciar el contenedor
- SQL puro, sin ORM
- Migrations crean tablas, Seeders insertan datos de prueba

---

## Patrones de Diseño

### 1. Singleton Pattern (Database)

```php
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']
            );
        }
        return self::$instance;
    }
}

// Uso
$pdo = Database::getInstance();
$pdo = Database::getInstance();  // Misma instancia
```

**Beneficio:** Una sola conexión reutilizada en toda la app.

### 2. Repository Pattern

```php
// Interfaz
interface IRepository {
    public function findAll(): array;
    public function findById(int $id): ?Model;
}

// Implementación
class CategoryRepository implements IRepository {
    public function __construct(private PDO $db) {}

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM categories");
        $stmt->execute();
        return array_map(
            fn($row) => Category::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }
}
```

**Beneficio:** Abstracción de acceso a datos.

### 3. Service Layer Pattern

```php
class CategoryService {
    public function __construct(private CategoryRepository $repo) {}

    public function getAllCategories(): array {
        // Lógica de negocio aquí
        return $this->repo->findAll();
    }

    public function getCategoriesPaginated(int $page, int $perPage): array {
        // Validaciones, transformaciones, etc.
        return $this->repo->paginate($page, $perPage);
    }
}
```

**Beneficio:** Lógica centralizada, reutilizable.

### 4. Chain of Responsibility (Middleware)

```php
$router->use(new CorsMiddleware());
$router->use(new AuthMiddleware());
$router->get('/protected', [Controller::class, 'method']);

// Ejecución: CORS → Auth → Handler
```

**Beneficio:** Pipeline flexible de procesamiento.

### 5. Factory Pattern (implícito en Controllers)

```php
class CategoryController {
    public function __construct(
        private CategoryService $service,
        private CategoryRepository $repo
    ) {}
    // Service y Repository creados por contenedor DI
}
```

**Nota:** DI actualmente manual en `routes.php`, no hay contenedor IoC.

### 6. Data Transfer Object (DTO)

Aunque no hay clases DTO formales aún, seguimos el concepto:

```php
// Entity (mapa BD)
class Category {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        // ...
    ) {}
}

// DTO (respuesta HTTP)
// Actualmente: Category::toArray() → JSON
// Futuro: CategoryDTO con transformaciones específicas
```

---

## Credenciales para Testing

### Base de Datos MySQL

```
Host:     localhost (desde host)
          mysql (desde contenedor PHP)
Port:     3306
Database: api_db
User:     api_user
Password: secret
Root:     root / rootsecret
```

**Conexión con CLI:**
```bash
# Desde host
mysql -h localhost -u api_user -psecret -D api_db

# O con docker exec
docker exec -it api_mysql mysql -u api_user -psecret api_db
```

### Usuarios API

| Email | Password | Role | JWT Token |
|-------|----------|------|-----------|
| admin@example.com | admin123 | admin | Obtenido en /auth/login |
| manager@example.com | manager123 | manager | Obtenido en /auth/login |
| viewer@example.com | viewer123 | viewer | Obtenido en /auth/login |

**Obtener token:**
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

**Usar token en endpoint protegido:**
```bash
curl -H "Authorization: Bearer <token>" \
  http://localhost/api/v1/protected-endpoint
```

---

## Comandos Útiles

### Docker Compose

```bash
# Iniciar contenedores
docker-compose up -d

# Ver estado
docker-compose ps

# Logs en tiempo real
docker-compose logs -f api_php
docker-compose logs -f api_mysql

# Detener
docker-compose stop

# Reanudar
docker-compose start

# Reiniciar PHP (después de cambios de código)
docker-compose restart api_php

# Destruir contenedores (no afecta volumes)
docker-compose down

# Destruir todo (incluyendo datos)
docker-compose down -v
```

### Dentro del Contenedor PHP

```bash
# Entrar a bash
docker exec -it api_php bash

# Ver extensiones PHP
docker exec api_php php -m

# Ver versión PHP
docker exec api_php php -v

# Ejecutar Composer
docker exec api_php composer install
docker exec api_php composer require nombre/paquete
docker exec api_php composer dump-autoload -o
```

### MySQL desde Contenedor

```bash
# Entrar a MySQL interactivo
docker exec -it api_mysql mysql -u api_user -psecret api_db

# Ejecutar SQL file
docker exec -i api_mysql mysql -u api_user -psecret api_db < dump.sql

# Backup
docker exec api_mysql mysqldump -u api_user -psecret api_db > backup.sql
```

### Curl para Testing de Endpoints

```bash
# GET paginado
curl "http://localhost/api/v1/categories?page=1&per_page=10"

# POST login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Con header Authorization
curl -H "Authorization: Bearer eyJhbG..." \
  http://localhost/api/v1/protected

# POST registro
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Usuario",
    "email":"user@example.com",
    "password":"pass123",
    "role":"viewer"
  }'
```

---

## Notas de Desarrollo

### 1. Cambios en PHP No Se Recargan

**Problema:** Opcache almacena bytecode compilado. Los cambios en archivos `.php` no se reflejan inmediatamente.

**Solución:**
```bash
docker-compose restart api_php
```

Reinicia el contenedor, limpiando opcache.

**Alternativa (no recomendado):**
```bash
docker-compose exec api_php php -f cli/clear-cache.php
```
Script específico para limpiar cache (requiere implementación).

### 2. Xdebug Configurado

**Modo:** Step debugging

**Configuración (php.ini):**
```ini
xdebug.mode = debug
xdebug.start_with_request = trigger
xdebug.client_host = host.docker.internal
xdebug.client_port = 9003
```

**Uso:**
- Configurar IDE (VS Code, PhpStorm) para escuchar en puerto 9003
- Agregar breakpoints
- La ejecución se detiene en breakpoints

**VS Code:**
```json
{
  "name": "Listen for Xdebug",
  "type": "php",
  "port": 9003
}
```

### 3. Variables de Entorno

**Cargadas en:** `public/index.php`

```php
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    // Lee .env línea a línea
    // Establece en $_ENV y putenv()
}
```

**Acceso:**
```php
$_ENV['DB_HOST']    // Como variable superglobal
getenv('DB_HOST')   // Como función
```

**Nota:** Cambios en `.env` requieren `docker-compose restart api_php`.

### 4. Instalación de Paquetes Composer

```bash
# Agregar paquete
docker exec api_php composer require monolog/monolog

# Actualizar dependencias
docker exec api_php composer update

# Regenerar autoloader
docker exec api_php composer dump-autoload -o

# Limpiar cache
docker exec api_php composer clear-cache
```

**vendor/ nunca debe incluirse en Git** — Se regenera en `docker build`.

### 5. Prepared Statements (Prevención SQL Injection)

**Correcto:**
```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);  // Parametrizado
```

**Incorrecto (vulnerable):**
```php
$query = "SELECT * FROM users WHERE email = '$email'";  // SQL injection!
```

Todos los Repositories usan prepared statements. Verificar en PRs nuevos.

### 6. Contraseñas: Bcrypt Hash

**Hash (registro):**
```php
$hash = password_hash($password, PASSWORD_BCRYPT);
// Guarda en BD
```

**Verify (login):**
```php
if (password_verify($inputPassword, $storedHash)) {
    // Correcto
}
```

Nunca almacenar passwords en plain text.

---

## Roadmap y Consideraciones Futuras

### Corto Plazo (Próximas 2-4 semanas)

- [ ] Endpoints adicionales (Suppliers, Products, Inventory)
- [ ] Validación de entrada (FormValidator)
- [ ] Rate limiting en endpoints
- [ ] Logging estructurado (monolog)
- [ ] Tests unitarios (PHPUnit)
- [ ] Tests de integración (API testing)

### Mediano Plazo (1-3 meses)

- [ ] Paginación mejorada (cursor-based)
- [ ] Caching (Redis para queries frecuentes)
- [ ] Filtros avanzados (search, sort, filters)
- [ ] Documentación API (Swagger/OpenAPI)
- [ ] GraphQL layer (opcional, alternativa a REST)
- [ ] Background jobs (queues)
- [ ] Event sourcing (audit log)

### Largo Plazo (3-12 meses)

- [ ] Migración a Laravel/Symfony si escala significativamente
- [ ] Multi-tenancy
- [ ] WebSocket para real-time updates
- [ ] Subscriptions/billing (si aplicable)
- [ ] Analytics y reporting
- [ ] Machine learning integration (si aplicable)

### Mejoras de Arquitectura

1. **Inyección de Dependencias Formal**
   - Implementar contenedor IoC simple o usar `league/container`
   - Evitar instanciación manual en Controllers

2. **Validación Centralizada**
   ```php
   class FormValidator {
       public function validate(array $data, array $rules): array {}
   }
   ```

3. **Exception Handling**
   - Custom exceptions para diferentes escenarios
   - Global exception handler en front controller

4. **Events/Observers**
   - UserCreated → EnviarBienvenida
   - ProductUpdated → ActualizarCache

5. **Domain-Driven Design**
   - Organizar por dominio en lugar de por tipo (Controllers/, Services/)
   ```
   src/Users/UserController.php
   src/Users/UserService.php
   src/Users/UserRepository.php
   ```

### Performance

- [ ] Índices en base de datos (email, foreign keys)
- [ ] Query optimization (N+1 problem)
- [ ] Caching layer (Redis)
- [ ] CDN para assets estáticos
- [ ] Compression (gzip)
- [ ] Lazy loading en relaciones

### Seguridad

- [ ] Rate limiting por IP/usuario
- [ ] HTTPS/SSL en producción
- [ ] CORS whitelist (no permitir `*`)
- [ ] CSRF tokens si se agrega web form
- [ ] SQL injection audits
- [ ] XSS prevention
- [ ] Secrets management (env vars encriptadas)

---

## Notas Finales

Este documento debe mantenerse actualizado conforme el proyecto evoluciona. Cualquier decisión arquitectónica mayor debe documentarse aquí con:

1. La decisión (qué cambió)
2. Justificación (por qué)
3. Alternativas (qué se consideró)
4. Trade-offs (ventajas y desventajas)
5. Fecha de implementación

Para preguntas técnicas o cambios propuestos, referirse a este documento como base de discusión.
