# ApiProject - Guía Completa del Flujo de Solicitudes

API REST pura en PHP 8.2 sin frameworks, con arquitectura profesional en capas.

**Características:**
- Catálogo de 20+ productos
- Sistema de inventario con movimientos de entrada/salida
- Categorías jerárquicas de productos
- Gestión de proveedores
- Sistema de roles para usuarios (admin, manager, viewer)
- Base de datos completamente creada con datos de prueba

---

## Tabla de Contenidos

1. [Cómo funciona una solicitud HTTP](#cómo-funciona-una-solicitud-http)
2. [Flujo detallado: GET /api/v1/categories](#flujo-detallado-get-apiv1categories)
3. [Estructura del código](#estructura-del-código)
4. [Requisitos e instalación](#requisitos)

---

## Cómo funciona una solicitud HTTP

Cuando haces una solicitud GET a `http://localhost/api/v1/categories?page=1&per_page=10`, el servidor debe:

1. **Recibir** la solicitud HTTP
2. **Entender** qué endpoint está pidiendo
3. **Ejecutar** la lógica correcta
4. **Consultar** la base de datos
5. **Devolver** los resultados en formato JSON

Nuestro API hace exactamente eso, paso a paso.

---

## Flujo Detallado: GET /api/v1/categories

### 1️⃣ Apache recibe la solicitud y redirige

Archivo involucrado: `public/.htaccess`

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Todas las solicitudes van a `public/index.php`

---

### 2️⃣ index.php carga autoloader y variables de entorno

Archivo involucrado: `public/index.php` (líneas 3-22)

```php
// Línea 4: Carga Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Líneas 7-19: Carga variables de entorno desde .env
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    // Lee el archivo .env y carga variables
}

// Línea 22: Carga archivo de rutas
require dirname(__DIR__) . '/src/routes.php';
```

**Qué pasa:**
- Carga el autoloader de Composer (permite usar clases automáticamente)
- Lee variables de `.env` (credenciales de BD, etc.)
- Carga el archivo `src/routes.php` que define las rutas

---

### 3️⃣ Router registra rutas y middleware

Archivo involucrado: `src/routes.php` (líneas 9-27)

```php
// Línea 9: Crea un nuevo Router
$router = new Router();

// Línea 12: Registra middleware CORS
$router->use(new CorsMiddleware());

// Línea 22: Registra ruta GET /api/v1/categories
$router->get('/api/v1/categories', [CategoryController::class, 'index']);

// Línea 26-27: Crea Request y despacha
$request = new Request();
$router->dispatch($request);
```

**Qué pasa:**
- Router es un "director de tránsito" que mapea URLs a controladores
- Se registra el middleware CORS (para permitir solicitudes desde otros dominios)
- Se define la ruta GET /api/v1/categories
- Se crea un objeto Request y se le pasa al router

---

### 4️⃣ Router busca la ruta que coincida

Archivo involucrado: `src/Core/Router.php` (líneas 55-81)

```php
// Línea 57-80: Itera todas las rutas registradas
foreach ($this->routes as $route) {
    // Línea 58-60: Verifica que el método HTTP coincida (GET, POST, etc)
    if ($route['method'] !== $request->method) {
        continue;
    }

    // Línea 62: Verifica que la URL coincida con el patrón
    if (preg_match($route['regex'], $request->uri, $matches)) {
        // Línea 64-68: Extrae parámetros dinámicos (como {id} en /categories/{id})
        foreach ($matches as $key => $value) {
            if (!is_numeric($key)) {
                $request->setAttribute($key, $value);
            }
        }

        // Línea 71-74: Ejecuta la cadena de middleware
        $this->executeMiddlewareChain(
            $request,
            fn() => $this->callHandler($route['handler'], $request)
        );
        return;
    }
}
```

**Qué pasa:**
- Método HTTP coincide: GET (es nuestro caso)
- URL coincide: /api/v1/categories
- Se ejecuta la cadena de middleware

---

### 5️⃣ Middleware CORS agrega headers HTTP

Archivo involucrado: `src/Middleware/CorsMiddleware.php` (líneas 18-32)

```php
// Línea 21-23: Si es solicitud OPTIONS (preflight), responde
if ($request->method === 'OPTIONS') {
    $this->sendPreflight();
}

// Línea 26-29: Agrega headers CORS
header("Access-Control-Allow-Origin: {$this->allowedOrigin}");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Línea 31: Llama al siguiente en la cadena (Controller)
$next();
```

**Qué pasa:**
- Se agregan headers CORS a la respuesta
- Se continúa al siguiente paso (controller)

---

### 6️⃣ CategoryController procesa la solicitud

Archivo involucrado: `src/Controllers/CategoryController.php` (líneas 24-47)

```php
// Línea 26-27: Obtiene parámetros de query string (?page=1&per_page=10)
$page = (int)$request->get('page', 1);
$perPage = (int)$request->get('per_page', 10);

// Línea 30-35: Valida que los parámetros sean válidos
if ($page < 1) {
    Response::validationError(['page' => 'Must be >= 1'])->send();
}
if ($perPage < 1 || $perPage > 100) {
    Response::validationError(['per_page' => 'Must be between 1 and 100'])->send();
}

// Línea 37: Llama al servicio para obtener datos
$result = $this->service->getCategoriesPaginated($page, $perPage);

// Línea 39-44: Empaqueta los datos en un DTO
$collection = new CategoryCollectionDTO(
    categories: $result['data'],
    total:      $result['total'],
    page:       $result['page'],
    perPage:    $result['perPage'],
);

// Línea 46: Responde con éxito
Response::success($collection->toDataArray(), $collection->toMetaArray())->send();
```

**Qué pasa:**
- Obtiene parámetros GET (page, per_page)
- Valida que sean números válidos
- Llama al servicio para obtener datos
- Empaqueta los datos en un DTO (Data Transfer Object)
- Responde con JSON

---

### 7️⃣ Constructor del Controller crea el servicio

Archivo involucrado: `src/Controllers/CategoryController.php` (líneas 16-19)

```php
public function __construct()
{
    // Línea 18: Crea el servicio via Factory
    $this->service = ServiceFactory::makeCategory();
}
```

**Qué pasa:**
- ServiceFactory crea una instancia de CategoryService con todas sus dependencias
- Es "inyección de dependencias": el servicio se inyecta en el controller

---

### 8️⃣ ServiceFactory crea el servicio con el repositorio

Archivo involucrado: `src/Factories/ServiceFactory.php` (líneas 9-13)

```php
public static function makeCategory(): CategoryService
{
    // Línea 11: Crea el repository
    $repository = RepositoryFactory::makeCategory();
    // Línea 12: Crea el servicio con el repository inyectado
    return new CategoryService($repository);
}
```

**Qué pasa:**
- RepositoryFactory crea el CategoryRepository
- CategoryService recibe el repository en el constructor
- Esto permite testear fácilmente

---

### 9️⃣ RepositoryFactory crea el repositorio

Archivo involucrado: `src/Factories/RepositoryFactory.php`

```php
public static function makeCategory(): CategoryRepository
{
    return new CategoryRepository();
}
```

**Qué pasa:**
- Crea una nueva instancia del CategoryRepository
- El repository obtiene automáticamente la conexión a BD (Singleton)

---

### 1️⃣0️⃣ CategoryService obtiene datos paginados

Archivo involucrado: `src/Services/CategoryService.php` (líneas 40-50)

```php
public function getCategoriesPaginated(int $page = 1, int $perPage = 10): array
{
    // Línea 42: Llama al repository
    $result = $this->repository->paginate($page, $perPage);

    // Línea 44-49: Retorna los datos formateados
    return [
        'data'    => $result['data'],
        'total'   => $result['total'],
        'page'    => $page,
        'perPage' => $perPage,
    ];
}
```

**Qué pasa:**
- Llama al repositorio para obtener datos
- El servicio es la capa de lógica de negocio (aquí iría validación, transformaciones, etc.)

---

### 1️⃣1️⃣ CategoryRepository consulta la base de datos

Archivo involucrado: `src/Repositories/CategoryRepository.php` (líneas 58-80)

```php
public function paginate(int $page = 1, int $perPage = 10): array
{
    // Línea 60: Calcula el OFFSET para SQL
    $offset = ($page - 1) * $perPage;

    // Línea 62-67: Prepara la consulta SQL
    $stmt = $this->db->prepare('
        SELECT id, name, slug, parent_id
        FROM categories
        ORDER BY id ASC
        LIMIT ? OFFSET ?
    ');
    
    // Línea 68-70: Vincula parámetros (previene SQL injection)
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Línea 72-73: Obtiene filas y convierte a objetos
    $rows = $stmt->fetchAll();
    $categories = array_map(
        fn(array $row) => Category::fromArray($row),
        $rows
    );
    
    // Línea 74: Obtiene el total de registros
    $total = $this->count();

    // Línea 76-79: Retorna datos
    return [
        'data' => $categories,
        'total' => $total,
    ];
}
```

**Qué pasa:**
- Calcula el OFFSET para paginación (página 1, 10 items = offset 0)
- Prepara una consulta SQL segura con placeholders (?)
- Vincula los parámetros para prevenir SQL injection
- Ejecuta la query
- Convierte cada fila en un objeto Category
- Retorna los datos

---

### 1️⃣2️⃣ Database obtiene la conexión (Singleton)

Archivo involucrado: `src/Core/Database.php` (líneas 13-48)

```php
// Línea 13-19: getInstance() retorna la misma conexión siempre
public static function getInstance(): PDO
{
    if (self::$connection === null) {
        self::connect();
    }
    return self::$connection;
}

// Línea 21-48: Conecta a MySQL la primera vez
private static function connect(): void
{
    try {
        // Línea 24-28: Lee credenciales de .env
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: 3306;
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        // Línea 30: Crea el string de conexión (DSN)
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

        // Línea 32-41: Crea la conexión PDO
        self::$connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        Response::error('Database connection failed', 500)->send();
    }
}
```

**Qué pasa:**
- Singleton Pattern: solo hay UNA conexión durante toda la ejecución
- Lee credenciales de .env
- Crea la conexión PDO con MySQL
- Lanzará excepciones si hay error

---

### 1️⃣3️⃣ CategoryRepository convierte filas en objetos

Archivo involucrado: `src/Models/Category.php` (líneas 17-38)

```php
// Línea 17-25: Crea un objeto Category desde una fila de BD
public static function fromArray(array $data): self
{
    return new self(
        id: (int)$data['id'],
        name: (string)$data['name'],
        slug: (string)$data['slug'],
        parent_id: $data['parent_id'] ? (int)$data['parent_id'] : null,
    );
}

// Línea 30-38: Convierte el objeto a array para JSON
public function toArray(): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'parent_id' => $this->parent_id,
    ];
}
```

**Qué pasa:**
- Las filas de BD son arrays
- Se convierten a objetos Category (estructura, type hints, métodos)
- Luego se convierten a arrays para JSON

---

### 1️⃣4️⃣ CategoryCollectionDTO empaqueta los datos

Archivo involucrado: `src/DTOs/CategoryCollectionDTO.php` (líneas 20-50)

```php
// Línea 20-34: Constructor empaqueta los datos
public function __construct(
    array $categories,
    int   $total,
    int   $page,
    int   $perPage,
) {
    // Línea 26-28: Convierte cada Category en CategoryDTO
    $this->items = array_map(
        fn(Category $c) => CategoryDTO::fromEntity($c),
        $categories
    );
    
    // Línea 30-33: Almacena metadata
    $this->total      = $total;
    $this->page       = $page;
    $this->perPage    = $perPage;
    $this->totalPages = (int) ceil($total / $perPage);
}

// Línea 37-40: Retorna datos como array para JSON
public function toDataArray(): array
{
    return array_map(
        fn(CategoryDTO $dto) => $dto->toArray(),
        $this->items
    );
}

// Línea 42-50: Retorna metadata
public function toMetaArray(): array
{
    return [
        'total'       => $this->total,
        'page'        => $this->page,
        'per_page'    => $this->perPage,
        'total_pages' => $this->totalPages,
    ];
}
```

**Qué pasa:**
- DTO = Data Transfer Object
- Empaqueta categorías + información de paginación
- Convierte todo a arrays para JSON

---

### 1️⃣5️⃣ Response envía el JSON al cliente

Archivo involucrado: `src/Core/Response.php` (líneas 17-74)

```php
// Línea 17-24: Método estático para crear respuesta exitosa
public static function success(array $data, array $meta = null, int $statusCode = 200): self
{
    return new self([
        'status' => 'success',
        'data' => $data,
        'meta' => $meta,
    ], $statusCode);
}

// Línea 64-74: Envía la respuesta al cliente
public function send(): void
{
    // Línea 66: Establece código HTTP
    http_response_code($this->statusCode);

    // Línea 68-70: Envía headers HTTP
    foreach ($this->headers as $key => $value) {
        header("$key: $value");
    }

    // Línea 72: Convierte datos a JSON e imprime
    echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;  // Línea 73: Detiene el script
}
```

**Qué pasa:**
- Estructura el JSON con status, data y meta
- Establece el código HTTP 200 (éxito)
- Envía los headers (Content-Type: application/json, CORS, etc.)
- Convierte los datos a JSON
- Detiene el script

---

## Ejemplo de respuesta JSON

Solicitud:
```bash
GET /api/v1/categories?page=1&per_page=2
```

Respuesta:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Electrónica",
      "slug": "electronica",
      "parent_id": null
    },
    {
      "id": 2,
      "name": "Smartphones",
      "slug": "smartphones",
      "parent_id": 1
    }
  ],
  "meta": {
    "total": 7,
    "page": 1,
    "per_page": 2,
    "total_pages": 4
  }
}
```

---

## Estructura del código

Archivos involucrados en el flujo GET /api/v1/categories:

```
public/
├── index.php                         Front Controller (punto de entrada)
└── .htaccess                         Redirige URLs a index.php

src/
├── Core/
│   ├── Database.php                 Conexión PDO (Singleton)
│   ├── Request.php                  Objeto HTTP Request
│   ├── Response.php                 Objeto HTTP Response
│   ├── Router.php                   Enrutador de solicitudes
│   └── Middleware.php               Interfaz de middleware
├── Middleware/
│   └── CorsMiddleware.php           Headers CORS
├── Controllers/
│   └── CategoryController.php       HTTP Handler
├── Services/
│   └── CategoryService.php          Lógica de negocio
├── Repositories/
│   └── CategoryRepository.php       Acceso a datos
├── Models/
│   └── Category.php                 Entidad de BD
├── DTOs/
│   ├── CategoryDTO.php              Un objeto categoría
│   └── CategoryCollectionDTO.php    Array de categorías + metadata
├── Factories/
│   ├── ServiceFactory.php           Crea servicios
│   └── RepositoryFactory.php        Crea repositorios
└── routes.php                       Definición de rutas

database/
├── migrations/                      Crea tablas
│   ├── 001_create_users_table.sql
│   ├── 002_create_categories_table.sql
│   ├── 003_create_suppliers_table.sql
│   ├── 004_create_products_table.sql
│   └── 005_create_inventory_movements_table.sql
└── seeders/                         Carga datos
    ├── 001_seed_users.sql
    ├── 002_seed_categories.sql
    ├── 003_seed_suppliers.sql
    ├── 004_seed_products.sql
    └── 005_seed_inventory_movements.sql

docker/
├── php/
│   ├── Dockerfile                  Imagen Docker PHP 8.2
│   ├── php.ini                     Config PHP
│   └── entrypoint.sh               Script inicial
└── mysql/
    └── Dockerfile                  Imagen Docker MySQL 8.0

cli/
└── setup.php                        Ejecuta migraciones y seeders

composer.json                        Dependencias y autoload PSR-4
docker-compose.yml                   Orquesta contenedores
.env                                 Variables de entorno (secreto)
.env.example                         Template de .env
CLAUDE.md                            Notas técnicas
README.md                            Este archivo
```

---

## Arquitectura en capas

```
Cliente HTTP (navegador, curl, Postman)
         |
         v
public/.htaccess (redirige a index.php)
         |
         v
public/index.php (carga autoloader y rutas)
         |
         v
src/routes.php + src/Core/Router.php (mapea URL a controller)
         |
         v
src/Middleware/CorsMiddleware.php (procesa middleware)
         |
         v
src/Controllers/CategoryController.php (valida entrada, coordina)
         |
         v
src/Services/CategoryService.php (lógica de negocio)
         |
         v
src/Repositories/CategoryRepository.php (consulta BD)
         |
         v
src/Core/Database.php (conexión PDO Singleton)
         |
         v
MySQL Database (tabla categories)
         |
         v
src/Models/Category.php (mapea filas a objetos)
         |
         v
src/DTOs/CategoryCollectionDTO.php (empaqueta datos)
         |
         v
src/Core/Response.php (genera JSON)
         |
         v
Cliente recibe respuesta JSON con código HTTP 200
```

---

## Conceptos clave

### Singleton Pattern (Database)

Solo hay UNA conexión a BD durante toda la ejecución:

```php
// Primera llamada: crea la conexión
$db = Database::getInstance();

// Segunda llamada: retorna la misma conexión
$db = Database::getInstance();  // es la MISMA instancia
```

### Inyección de Dependencias (Factories)

En lugar de que una clase cree sus dependencias:

```php
// MALO: CategoryService crea su repo
class CategoryService {
    public function __construct() {
        $this->repo = new CategoryRepository();
    }
}

// BIEN: Se le inyecta desde afuera
class CategoryService {
    public function __construct(CategoryRepository $repo) {
        $this->repo = $repo;
    }
}
```

Ventaja: En tests puedes pasar un mock.

### Prepared Statements (Seguridad)

Previene SQL injection:

```php
// MALO: vulnerable
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];

// BIEN: safe
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);
```

El `?` es un placeholder que se llena de forma segura.

### DTO (Data Transfer Object)

Empaqueta datos para transportar:

```php
$collection = new CategoryCollectionDTO(
    categories: $result['data'],
    total: $result['total'],
    page: 1,
    perPage: 10,
);

// Convierte a arrays para JSON
$collection->toDataArray();   // Los datos
$collection->toMetaArray();   // Metadata (paginación)
```

### Repository Pattern

Separa acceso a datos del resto de la lógica:

```
Controller -> Service -> Repository -> Database
```

Cada capa tiene una responsabilidad clara.

---

## Requisitos

- Docker
- Docker Compose

Descarga desde: https://www.docker.com/products/docker-desktop

---

## Instalación

1. Clonar el proyecto:
```bash
git clone <url-del-repositorio>
cd ApiProject
```

2. Configurar variables de entorno:
```bash
cp .env.example .env
```

3. Levantar el ambiente:
```bash
docker-compose up -d --build
```

Espera 15-20 segundos para que:
- MySQL se inicie
- Composer genere el autoloader (`vendor/autoload.php`)
- Se ejecuten migraciones y seeders de la BD

Verifica que todo esté listo:
```bash
docker logs api_php
```

Deberías ver:
```
Setup completado correctamente
Iniciando Apache...
```

---

## Acceso a la API

Prueba el endpoint de test:
```bash
curl http://localhost/test
```

Respuesta:
```json
{
  "status": "success",
  "data": [
    {
      "message": "Welcome to ApiProject",
      "version": "1.0.0"
    }
  ]
}
```

Prueba el endpoint de categorías:
```bash
curl "http://localhost/api/v1/categories?page=1&per_page=10"
```

---

## Base de datos

Tablas creadas:

| Tabla | Descripción |
|-------|-------------|
| users | Personal interno (admin, manager, viewer) |
| categories | Categorías de productos (jerárquicas) |
| suppliers | Proveedores |
| products | Catálogo de 20 productos |
| inventory_movements | Movimientos de entrada/salida de stock |

Datos de prueba:
- 3 usuarios con roles diferentes
- 7 categorías
- 4 proveedores
- 20 productos
- 40+ movimientos de inventario

---

## Credenciales para testing

MySQL:
- Host: localhost
- Puerto: 3306
- Usuario: api_user
- Contraseña: secret
- Base de datos: api_db

Acceso root: usuario `root`, contraseña `rootsecret`

Usuarios de la API:
- admin@example.com / admin123 (admin)
- manager@example.com / manager123 (manager)
- viewer@example.com / viewer123 (viewer)

---

## Comandos Docker

Controlar contenedores:
```bash
docker-compose up -d --build      # Inicia todo
docker-compose down                # Apaga y elimina
docker-compose stop                # Solo apaga
docker-compose start               # Solo enciende
docker-compose restart             # Reinicia
```

Ver logs:
```bash
docker logs api_php                # Logs de PHP
docker logs api_mysql              # Logs de MySQL
docker logs -f api_php             # Sigue en tiempo real
```

Entrar al contenedor:
```bash
docker exec -it api_php bash       # Terminal de PHP
docker exec -it api_mysql bash     # Terminal de MySQL
docker exec -it api_mysql mysql -u api_user -psecret -D api_db
```

---

## Endpoints actuales

GET /test - Prueba que la API responde

GET /api/v1/categories - Listado paginado (parámetros: page, per_page)

GET /api/v1/categories/{id} - Detalle de una categoría

---

## Próximos endpoints

- Products (CRUD)
- Suppliers (CRUD)
- Inventory movements (CRUD)
- Users (CRUD)
- Authentication (JWT)
- Search y filtros

---

## Troubleshooting

| Problema | Solución |
|----------|----------|
| Error 404 Endpoint not found | La ruta no está en src/routes.php |
| Database connection failed | Verifica .env (credenciales, host, puerto) |
| Class not found | Ejecuta: docker exec api_php composer dump-autoload |
| Puerto 80 en uso | Cambia puerto en docker-compose.yml |
| MySQL no conecta | Espera 30s, verifica: docker logs api_mysql |
| Cambios en PHP no se ven | Opcache: docker-compose restart api_php |
| DBeaver no conecta | Usa localhost (no 127.0.0.1), puerto 3306 |

---

## Recursos

- PHP 8.2: https://www.php.net/
- PDO Prepared Statements: https://www.php.net/manual/en/pdo.prepared-statements.php
- CORS: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
- Repository Pattern: https://martinfowler.com/eaaCatalog/repository.html
- Docker: https://docs.docker.com/
- PSR-4: https://www.php-fig.org/psr/psr-4/

---

## Resumen del flujo

```
Cliente HTTP
  |
  v
.htaccess → index.php
  |
  v
Carga autoloader y .env
  |
  v
routes.php registra rutas y middleware
  |
  v
Router busca la ruta que coincida
  |
  v
CorsMiddleware agrega headers CORS
  |
  v
CategoryController valida y obtiene datos del servicio
  |
  v
ServiceFactory crea el servicio con repository
  |
  v
CategoryService obtiene datos paginados del repository
  |
  v
CategoryRepository ejecuta query SQL y obtiene filas
  |
  v
Database (Singleton) conecta a MySQL
  |
  v
MySQL ejecuta query y retorna filas
  |
  v
Filas se convierten a objetos Category
  |
  v
CategoryCollectionDTO empaqueta datos + metadata
  |
  v
Response crea JSON y envía al cliente
  |
  v
Cliente recibe respuesta JSON con código 200
```

---

Última actualización: 2026-04-01
Licencia: Uso personal
