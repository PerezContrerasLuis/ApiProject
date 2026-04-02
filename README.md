# 📚 ApiProject - Guía Completa del Flujo de Solicitudes

API REST pura en PHP 8.2 sin frameworks, con arquitectura profesional en capas.

**Características:**
- ✅ Catálogo de 20+ productos
- ✅ Sistema de inventario con movimientos de entrada/salida
- ✅ Categorías jerárquicas de productos
- ✅ Gestión de proveedores
- ✅ Sistema de roles para usuarios (admin, manager, viewer)
- ✅ Base de datos completamente creada con datos de prueba

---

## 🎯 Tabla de Contenidos

1. [¿Cómo funciona una solicitud?](#cómo-funciona-una-solicitud-http)
2. [Flujo detallado: GET /api/v1/categories](#flujo-detallado-get-apiv1categories)
3. [Explicación de cada componente](#explicación-de-cada-componente)
4. [Requisitos e instalación](#requisitos)

---

## 🎬 ¿Cómo funciona una solicitud HTTP?

Cuando haces una solicitud GET a `http://localhost/api/v1/categories?page=1&per_page=10`, el navegador envía esos datos al servidor. El servidor debe:

1. **Recibir** la solicitud HTTP
2. **Entender** qué endpoint está pidiendo
3. **Ejecutar** la lógica correcta
4. **Consultar** la base de datos
5. **Devolver** los resultados en formato JSON

Nuestro API hace exactamente eso, paso a paso.

---

## 🔄 Flujo Detallado: GET /api/v1/categories

### **Paso 1️⃣: Apache recibe la solicitud y redirige a index.php**

Cuando tecleas `http://localhost/api/v1/categories`:
- Apache recibe la solicitud
- El archivo **`.htaccess`** (en `/public/`) redirige TODAS las URLs a `index.php`

```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Resultado:** Todas las solicitudes van a `public/index.php`

---

### **Paso 2️⃣: public/index.php carga todo**

📄 **Archivo:** `public/index.php`

```php
// Línea 3-4: Carga Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';
```

Composer genera automáticamente un archivo especial (`vendor/autoload.php`) que permite usar clases como:
```php
use App\Controllers\CategoryController;  // Sin hacer require manual
```

```php
// Línea 6-18: Carga variables de entorno de .env
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}
```

Lee el archivo `.env` y pone los valores en memoria (ejemplo: `DB_HOST=mysql`, `DB_USER=api_user`).

```php
// Línea 21-22: Carga el archivo de rutas
require dirname(__DIR__) . '/src/routes.php';
```

Aquí es donde se define qué URL van a qué controlador.

---

### **Paso 3️⃣: src/routes.php registra rutas y Router**

📄 **Archivo:** `src/routes.php`

```php
// Línea 9: Crea un nuevo Router
$router = new Router();
```

El **Router** es como un "policía de tránsito" que decide dónde dirigir cada solicitud.

```php
// Línea 12: Registra el middleware CORS
$router->use(new CorsMiddleware());
```

**CORS** (Cross-Origin Resource Sharing) permite que otros sitios web usen tu API. Ejemplo: si tu API está en `localhost:8000` y tu frontend en `localhost:3000`, necesitan CORS para comunicarse.

```php
// Línea 22: Registra la ruta GET /api/v1/categories
$router->get('/api/v1/categories', [CategoryController::class, 'index']);
```

Esto dice: *"Si alguien hace GET a `/api/v1/categories`, ejecuta el método `index()` de `CategoryController`"*

```php
// Línea 26-27: Crea el Request y lo envía al Router
$request = new Request();
$router->dispatch($request);
```

Se crea un objeto `Request` (contiene URL, método HTTP, parámetros, headers) y se le pasa al router para que busque la ruta correcta.

---

### **Paso 4️⃣: Router busca la ruta que coincida**

📄 **Archivo:** `src/Core/Router.php` (línea 55-81)

```php
public function dispatch(Request $request): void
{
    // Línea 57: Recorre todas las rutas registradas
    foreach ($this->routes as $route) {
        
        // Línea 58-60: Verifica que el método HTTP coincida (GET, POST, etc)
        if ($route['method'] !== $request->method) {
            continue;
        }

        // Línea 62: Verifica que la URL coincida con el patrón de la ruta
        if (preg_match($route['regex'], $request->uri, $matches)) {
            
            // Línea 64-68: Extrae parámetros dinámicos
            // Ejemplo: en /api/v1/categories/{id} extrae el "id"
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
            return;  // Salimos, ya encontramos la ruta
        }
    }

    // Si llega aquí, ninguna ruta coincidió
    Response::notFound('Endpoint not found')->send();
}
```

**En nuestro caso:**
- Método: `GET` ✓ (coincide)
- URL: `/api/v1/categories` ✓ (coincide)
- El router ejecuta el middleware y luego el controlador

---

### **Paso 5️⃣: Se ejecuta el middleware CORS**

📄 **Archivo:** `src/Core/Router.php` (línea 99-110)

```php
private function executeMiddlewareChain(Request $request, callable $handler): void
{
    // Línea 101: Invierte el orden de los middleware
    $middlewares = array_reverse($this->middlewares);

    // Línea 103-107: Construye una cadena de funciones
    $chain = $handler;  // El handler final (CategoryController::index)
    foreach ($middlewares as $middleware) {
        $currentChain = $chain;
        // Cada middleware envuelve el siguiente
        $chain = fn() => $middleware->handle($request, $currentChain);
    }

    // Línea 109: Ejecuta la cadena completa
    $chain();
}
```

Es como una cadena de responsabilidad:
```
CORSMiddleware → CategoryController::index
```

---

### **Paso 6️⃣: CORSMiddleware agrega headers**

📄 **Archivo:** `src/Middleware/CorsMiddleware.php`

```php
// Línea 18-32: Maneja la solicitud CORS
public function handle(Request $request, callable $next): void
{
    // Línea 21-23: Si es una solicitud OPTIONS (preflight), responde rápido
    if ($request->method === 'OPTIONS') {
        $this->sendPreflight();
    }

    // Línea 26-29: Agrega headers CORS a la respuesta
    header("Access-Control-Allow-Origin: {$this->allowedOrigin}");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    // Línea 31: Llama al siguiente en la cadena
    $next();
}
```

**Resultado:** Se agregan headers CORS y se continúa al siguiente paso.

---

### **Paso 7️⃣: Se ejecuta CategoryController::index()**

📄 **Archivo:** `src/Controllers/CategoryController.php`

```php
// Línea 16-19: Constructor crea el servicio
public function __construct()
{
    $this->service = ServiceFactory::makeCategory();
}
```

El `ServiceFactory` es una "fábrica" que sabe cómo ensamblar todas las piezas necesarias del servicio.

```php
// Línea 24-47: Método index() - obtiene el listado de categorías
public function index(Request $request): void
{
    // Línea 26-27: Obtiene los parámetros de query string
    // ?page=1&per_page=10
    $page = (int)$request->get('page', 1);      // Página (default: 1)
    $perPage = (int)$request->get('per_page', 10);  // Items por página (default: 10)

    // Línea 30-35: Valida que los parámetros sean válidos
    if ($page < 1) {
        Response::validationError(['page' => 'Must be >= 1'])->send();
    }
    if ($perPage < 1 || $perPage > 100) {
        Response::validationError(['per_page' => 'Must be between 1 and 100'])->send();
    }

    // Línea 37: Llama al servicio para obtener los datos
    $result = $this->service->getCategoriesPaginated($page, $perPage);

    // Línea 39-44: Empaqueta los datos en un DTO
    // DTO es como una "caja" que contiene los datos formateados
    $collection = new CategoryCollectionDTO(
        categories: $result['data'],     // Array de objetos Category
        total:      $result['total'],    // Total de registros en BD
        page:       $result['page'],     // Página actual
        perPage:    $result['perPage'],  // Items por página
    );

    // Línea 46: Responde con éxito
    Response::success($collection->toDataArray(), $collection->toMetaArray())->send();
}
```

**Resumido:** El controller:
1. Valida entrada (parámetros)
2. Pide datos al servicio
3. Empaqueta en DTO
4. Responde con JSON

---

### **Paso 8️⃣: ServiceFactory crea el servicio**

📄 **Archivo:** `src/Factories/ServiceFactory.php`

```php
// Línea 9-13: Crea el CategoryService
public static function makeCategory(): CategoryService
{
    // Línea 11: Crea el repository
    $repository = RepositoryFactory::makeCategory();
    // Línea 12: Crea el servicio e inyecta el repository
    return new CategoryService($repository);
}
```

Esto se llama **"inyección de dependencias"**: en lugar de que `CategoryService` cree su repo, se le pasa desde afuera. Ventaja: fácil de testear.

---

### **Paso 9️⃣: CategoryService obtiene datos paginados**

📄 **Archivo:** `src/Services/CategoryService.php` (línea 40-50)

```php
public function getCategoriesPaginated(int $page = 1, int $perPage = 10): array
{
    // Línea 42: Llama al repository para obtener datos de BD
    $result = $this->repository->paginate($page, $perPage);

    // Línea 44-49: Retorna los datos formateados
    return [
        'data'    => $result['data'],      // Array de objetos Category
        'total'   => $result['total'],     // Total de registros
        'page'    => $page,
        'perPage' => $perPage,
    ];
}
```

**Nota:** El servicio es la "capa de lógica de negocio". Si tuvieras que calcular descuentos, validaciones complejas, transformaciones, va aquí.

---

### **Paso 🔟: CategoryRepository obtiene datos de la BD**

📄 **Archivo:** `src/Repositories/CategoryRepository.php` (línea 58-80)

```php
public function paginate(int $page = 1, int $perPage = 10): array
{
    // Línea 60: Calcula el OFFSET para SQL
    // Página 1, 10 items: offset = 0
    // Página 2, 10 items: offset = 10
    $offset = ($page - 1) * $perPage;

    // Línea 62-67: Prepara la consulta SQL
    $stmt = $this->db->prepare('
        SELECT id, name, slug, parent_id
        FROM categories
        ORDER BY id ASC
        LIMIT ? OFFSET ?
    ');
    
    // Línea 68-70: Vincula los parámetros (previene SQL injection)
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Línea 72: Obtiene todas las filas como arrays
    $rows = $stmt->fetchAll();
    
    // Línea 73: Convierte cada array en un objeto Category
    $categories = array_map(
        fn(array $row) => Category::fromArray($row),
        $rows
    );
    
    // Línea 74: Obtiene el total de registros
    $total = $this->count();

    // Línea 76-79: Retorna los datos
    return [
        'data' => $categories,
        'total' => $total,
    ];
}
```

**¿Qué es "prepared statements"?**
Es una forma segura de ejecutar SQL. Los `?` son placeholders que se llenan después con `bindValue()`. Esto **previene SQL injection**.

---

### **Paso 1️⃣1️⃣: Database obtiene la conexión (Singleton)**

📄 **Archivo:** `src/Core/Database.php` (línea 13-19)

```php
public static function getInstance(): PDO
{
    if (self::$connection === null) {
        // Línea 16: Si no existe, crea la conexión
        self::connect();
    }
    return self::$connection;  // Retorna la misma conexión siempre
}
```

**Singleton** significa que solo hay UNA conexión a BD durante toda la ejecución. Si pides la conexión 10 veces, obtienes la misma.

```php
// Línea 21-48: Conecta a MySQL
private static function connect(): void
{
    try {
        // Línea 24-28: Lee las credenciales de .env
        $host = getenv('DB_HOST');        // 'mysql'
        $port = getenv('DB_PORT') ?: 3306; // 3306
        $database = getenv('DB_DATABASE'); // 'api_db'
        $username = getenv('DB_USERNAME'); // 'api_user'
        $password = getenv('DB_PASSWORD'); // 'secret'

        // Línea 30: Crea el string de conexión (DSN)
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

        // Línea 32-41: Crea la conexión PDO
        self::$connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Lanza excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Retorna arrays
                PDO::ATTR_EMULATE_PREPARES => false,  // Usa prepared statements reales
            ]
        );
    } catch (PDOException $e) {
        // Línea 43-46: Si falla, retorna error
        Response::error('Database connection failed', 500)->send();
    }
}
```

**Resultado:** Se establece la conexión a MySQL. Las variables vienen del `.env`.

---

### **Paso 1️⃣2️⃣: CategoryRepository convierte filas en objetos**

📄 **Archivo:** `src/Models/Category.php` (línea 17-25)

```php
public static function fromArray(array $data): self
{
    return new self(
        id: (int)$data['id'],           // Convierte a int
        name: (string)$data['name'],    // Convierte a string
        slug: (string)$data['slug'],    // Convierte a string
        parent_id: $data['parent_id'] ? (int)$data['parent_id'] : null,  // int o null
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

**¿Por qué?**
- En BD los datos son filas (arrays)
- En PHP queremos objetos (Category)
- Los objetos dan estructura, validación y métodos

---

### **Paso 1️⃣3️⃣: CategoryCollectionDTO empaqueta todo**

📄 **Archivo:** `src/DTOs/CategoryCollectionDTO.php` (línea 20-50)

```php
// Constructor - empaqueta los datos
public function __construct(
    array $categories,  // Array de objetos Category
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

// Línea 37-40: Retorna los datos como array para JSON
public function toDataArray(): array
{
    return array_map(
        fn(CategoryDTO $dto) => $dto->toArray(),
        $this->items
    );
}

// Línea 42-50: Retorna la metadata
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

**DTO = Data Transfer Object:** Es una "caja" que empaqueta datos para transportarlos (en este caso, como JSON).

---

### **Paso 1️⃣4️⃣: Response envía el JSON**

📄 **Archivo:** `src/Core/Response.php` (línea 17-74)

```php
// Línea 17-24: Crea una respuesta exitosa
public static function success(array $data, array $meta = null, int $statusCode = 200): self
{
    return new self([
        'status' => 'success',  // Indica éxito
        'data' => $data,        // Los datos (categorías)
        'meta' => $meta,        // Metadata (paginación)
    ], $statusCode);
}

// Línea 64-74: Envía la respuesta al cliente
public function send(): void
{
    // Línea 66: Establece el código HTTP
    http_response_code($this->statusCode);

    // Línea 68-70: Envía los headers HTTP
    foreach ($this->headers as $key => $value) {
        header("$key: $value");  // Content-Type, CORS, etc
    }

    // Línea 72: Convierte datos a JSON e imprime
    echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;  // Línea 73: Detiene el script
}
```

---

## 📊 Ejemplo de Respuesta

**Solicitud:**
```
GET /api/v1/categories?page=1&per_page=2
```

**Respuesta JSON:**
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

**Headers HTTP:**
```
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
```

---

## Requisitos

- **Docker** — Ejecutar PHP y MySQL en contenedores
- **Docker Compose** — Orquestar múltiples contenedores

Descarga desde: https://www.docker.com/products/docker-desktop

---

## 🏗️ Arquitectura de Capas

```
┌─────────────────────────────────────────┐
│  public/index.php                       │  Front Controller
│  ├─ Carga autoloader (Composer)         │  (punto de entrada)
│  ├─ Carga variables de entorno          │
│  └─ Carga routes.php                    │
└─────────────────────────────────────────┘
                    ↓ HTTP Request
┌─────────────────────────────────────────┐
│  src/routes.php + Router                │  Router
│  ├─ Define qué URL va a qué controller  │  (mapea URLs a
│  ├─ Registra middleware                 │   controladores)
│  └─ Despacha solicitud                  │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│  src/Middleware/CorsMiddleware.php      │  Pipeline
│  └─ Agrega headers CORS, valida request │  (procesa headers,
└─────────────────────────────────────────┘  prepara request)
                    ↓
┌─────────────────────────────────────────┐
│  src/Controllers/CategoryController.php │  HTTP Handler
│  ├─ Valida entrada (parámetros)        │  (coordina
│  ├─ Pide datos al servicio              │   la lógica)
│  └─ Empaqueta en DTO y responde         │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│  src/Services/CategoryService.php       │  Lógica de Negocio
│  ├─ Aplica reglas de negocio           │  (transformaciones,
│  └─ Llama al repository                │   validaciones)
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│  src/Repositories/CategoryRepository.php│  Acceso a Datos
│  ├─ Ejecuta queries SQL                │  (comunica con BD)
│  ├─ Usa prepared statements             │
│  └─ Retorna objetos                    │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│  src/Core/Database.php                  │  Conexión (Singleton)
│  ├─ Singleton PDO                       │  (una sola conexión
│  └─ Maneja conexión MySQL               │   para toda la app)
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│  MySQL Database                         │  Persistencia
│  ├─ Tabla: categories                   │  (almacena datos
│  ├─ Tabla: products                     │   de forma segura)
│  └─ Más tablas...                       │
└─────────────────────────────────────────┘
```

**Ventajas de esta arquitectura:**

| Aspecto | Ventaja |
|---------|---------|
| **Mantenibilidad** | Cada capa tiene una responsabilidad clara |
| **Testabilidad** | Fácil mockear el repositorio en tests |
| **Reutilización** | Un servicio puede usarse en múltiples controladores |
| **Escalabilidad** | Agregar features sin cambiar código existente |
| **Seguridad** | Validación en capas, SQL injection prevenido |
| **Separación de responsabilidades** | Cada clase hace una cosa bien |

---

## 📝 Tipos de Datos en la Arquitectura

### **1. Entity (Category.php)**
Representa una fila en la base de datos:
```php
$category = Category::fromArray($row);  // Desde BD
$category->name;  // Accedo a propiedades
$category->toArray();  // Convierto a array
```

### **2. DTO (CategoryDTO.php)**
Empaqueta datos para transportar:
```php
$dto = CategoryDTO::fromEntity($category);
$dto->toArray();  // Para JSON
```

### **3. Request**
Contiene la solicitud HTTP:
```php
$page = $request->get('page', 1);        // Parámetro GET
$id = $request->getAttribute('id');      // Parámetro de ruta
```

### **4. Response**
Contiene la respuesta JSON:
```php
Response::success($data, $meta)->send();
Response::error('Mensaje', 400)->send();
```

---

## 🔒 Seguridad: Inyección de Dependencias y Prepared Statements

### **Inyección de Dependencias**

```php
// ❌ MALO: CategoryService crea su propio repo
class CategoryService {
    public function __construct() {
        $this->repo = new CategoryRepository();
    }
}

// ✅ BIEN: Se pasa el repo desde afuera (Factory)
class CategoryService {
    public function __construct(CategoryRepository $repo) {
        $this->repo = $repo;
    }
}
```

**Ventaja:** En tests puedes pasar un mock repository.

### **Prepared Statements**

```php
// ❌ MALO: Vulnerable a SQL injection
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];

// ✅ BIEN: Safe con prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);
```

**El `?` es un placeholder:** Se llena después de forma segura.

---

## 📦 Estructura de Carpetas Completa

```
ApiProject/
├── public/
│   ├── index.php           ← Front Controller (punto de entrada)
│   └── .htaccess           ← Redirige todo a index.php
├── src/
│   ├── Core/
│   │   ├── Database.php    ← Conexión PDO (Singleton)
│   │   ├── Request.php     ← Objeto HTTP Request
│   │   ├── Response.php    ← Objeto HTTP Response
│   │   ├── Router.php      ← Enrutador de solicitudes
│   │   ├── Middleware.php  ← Interfaz de middleware
│   ├── Middleware/
│   │   └── CorsMiddleware.php  ← Headers CORS
│   ├── Controllers/
│   │   └── CategoryController.php  ← HTTP Handler
│   ├── Services/
│   │   └── CategoryService.php     ← Lógica de negocio
│   ├── Repositories/
│   │   └── CategoryRepository.php  ← Acceso a datos
│   ├── Models/
│   │   └── Category.php            ← Entidad
│   ├── DTOs/
│   │   ├── CategoryDTO.php         ← Un objeto
│   │   └── CategoryCollectionDTO.php ← Array + metadata
│   ├── Factories/
│   │   ├── ServiceFactory.php      ← Crea servicios
│   │   └── RepositoryFactory.php   ← Crea repositorios
│   └── routes.php          ← Definición de rutas
├── docker/
│   ├── php/
│   │   ├── Dockerfile      ← Imagen Docker
│   │   └── entrypoint.sh   ← Script inicial
│   └── mysql/
│       └── Dockerfile
├── database/
│   ├── migrations/         ← Crea tablas
│   └── seeders/            ← Carga datos
├── .env                    ← Variables (secreto)
├── .env.example            ← Template
├── composer.json           ← Dependencias
├── docker-compose.yml      ← Orquesta contenedores
├── README.md               ← Este archivo
└── CLAUDE.md               ← Notas técnicas
```

---

## Instalación en 3 pasos

### 1. Clonar el proyecto

```bash
git clone <url-del-repositorio>
cd ApiProject
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

El archivo `.env` tiene las credenciales de base de datos. 

### 3. Levantar el ambiente

```bash
docker-compose up -d --build
```

**Espera 15-20 segundos** para que:
1. MySQL se inicie
2. Composer genere el autoloader (`vendor/autoload.php`)
3. Se ejecuten las migraciones y seeders de la BD

Verifica que todo esté listo:

```bash
docker logs api_php
```

Deberías ver:
```
✨ Setup completado correctamente
✅ Iniciando Apache...
```

> **Nota:** Composer se instala automáticamente en el contenedor durante el build. No necesitas tenerlo instalado en tu máquina local.

## Acceso a la API

```bash
curl http://localhost/test
```

**Respuesta:**
```json
{"message":"Welcome to ApiProject","version":"1.0.0"}
```

## Base de datos

**Dominio:** Catálogo de productos con inventario

### Tablas creadas

| Tabla | Descripción |
|---|---|
| `users` | Personal interno (admin, manager, viewer) |
| `categories` | Categorías de productos (jerárquicas) |
| `suppliers` | Proveedores de productos |
| `products` | Catálogo de 20 productos |
| `inventory_movements` | Historial de entrada/salida de stock |

### Datos de prueba

- **3 usuarios** con roles diferentes
- **7 categorías** incluyendo subcategorías
- **4 proveedores**
- **20 productos** distribuidos entre categorías
- **40+ movimientos** de inventario

Se cargan automáticamente al levantar el contenedor.

## Credenciales para testing

### Base de datos MySQL

| Campo | Valor |
|---|---|
| Host | localhost |
| Puerto | 3306 |
| Usuario | api_user |
| Contraseña | secret |
| Base de datos | api_db |

Acceso root: usuario `root`, contraseña `rootsecret`

### Usuarios de la API

| Nombre | Email | Contraseña | Rol |
|---|---|---|---|
| Admin User | admin@example.com | admin123 | admin |
| Manager User | manager@example.com | manager123 | manager |
| Viewer User | viewer@example.com | viewer123 | viewer |

## Herramientas recomendadas

### DBeaver (gestor de BD)

1. Descarga desde: https://dbeaver.io/
2. Crea nueva conexión MySQL
3. Usa credenciales de arriba
4. Conéctate a `api_db`

### Postman (cliente HTTP)

Para probar endpoints de la API:

```bash
GET http://localhost/test
```

## 🔧 Composer y Autoloading

Este proyecto usa **Composer** para gestionar el autoloading **PSR-4** de clases.

### **¿Qué es PSR-4?**

PSR-4 es un estándar que dice: *"Cada carpeta en el código mapea a un namespace, y cada clase tiene su propio archivo"*.

```php
// Archivo: src/Controllers/CategoryController.php
namespace App\Controllers;   // Namespace
class CategoryController {}   // Clase

// Se accede así:
$controller = new App\Controllers\CategoryController();
```

### **Cómo funciona en este proyecto**

1. **`composer.json`** define el mapeo:
   ```json
   {
     "autoload": {
       "psr-4": {
         "App\\": "src/"       // Namespace App\ → carpeta src/
       }
     }
   }
   ```

2. **Dockerfile** durante el build ejecuta:
   ```bash
   composer install --no-dev --optimize-autoloader --prefer-dist
   composer dump-autoload -o
   ```

3. **Genera `vendor/autoload.php`:** Un archivo que "mapea" automáticamente cada clase.

4. **`public/index.php`** carga el autoloader:
   ```php
   require dirname(__DIR__) . '/vendor/autoload.php';
   ```

### **Resultado**

Ya no necesitas hacer `require` manual de cada archivo:

```php
// ❌ VIEJO: Tedioso
require_once '../src/Controllers/CategoryController.php';
$c = new CategoryController();

// ✅ NUEVO: Automático
use App\Controllers\CategoryController;
$c = new CategoryController();
```

### **Notas importantes**

- `vendor/` se genera automáticamente (NO incluir en Git)
- Si agregas dependencias: `docker exec api_php composer require nombre/del-paquete`
- Para actualizar autoload: `docker exec api_php composer dump-autoload -o`

## Comandos Docker útiles

### Control de contenedores

| Comando | Qué hace | Datos |
|---|---|---|
| `docker-compose up -d --build` | Crea y enciende todo | Se conservan |
| `docker-compose down` | Apaga y elimina contenedores | Se pierden |
| `docker-compose stop` | Apaga sin eliminar | ✅ Se conservan |
| `docker-compose start` | Enciende lo existente | ✅ Se conservan |
| `docker-compose restart` | Reinicia contenedores | ✅ Se conservan |
| `docker-compose down -v` | Borra todo incluido datos | ❌ Se pierden TODOS |

### Ver logs

```bash
# Logs de PHP
docker logs api_php

# Logs de MySQL
docker logs api_mysql

# Seguir en tiempo real
docker logs -f api_php
```

### Entrar al contenedor

```bash
# Terminal en PHP
docker exec -it api_php bash

# Terminal en MySQL
docker exec -it api_mysql bash

# Acceder a MySQL desde terminal
docker exec -it api_mysql mysql -u api_user -psecret -D api_db
```

### Ejecutar comandos en PHP

```bash
# Ver extensiones instaladas
docker exec api_php php -m

# Versión de PHP
docker exec api_php php -v

# Ver configuración PHP
docker exec api_php php -i
```

## 📡 Endpoints Actuales

### **GET /test**

Verifica que la API responde:

```bash
curl http://localhost/test
```

**Respuesta:**
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

### **GET /api/v1/categories**

Obtiene el listado paginado de categorías:

```bash
curl "http://localhost/api/v1/categories?page=1&per_page=10"
```

**Parámetros:**
| Parámetro | Descripción | Default | Rango |
|-----------|-------------|---------|-------|
| `page` | Número de página | 1 | >= 1 |
| `per_page` | Items por página | 10 | 1-100 |

**Respuesta:** (Ver sección "Ejemplo de Respuesta" arriba)

### **GET /api/v1/categories/{id}**

Obtiene una categoría específica por ID:

```bash
curl "http://localhost/api/v1/categories/1"
```

**Respuesta:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Electrónica",
      "slug": "electronica",
      "parent_id": null
    }
  ],
  "meta": null
}
```

---

## 🚀 Próximos Endpoints

Plan futuro para completar la API:

- [ ] **Products** — CRUD de productos
- [ ] **Suppliers** — CRUD de proveedores
- [ ] **Inventory** — Movimientos de stock
- [ ] **Users** — Sistema de roles y autenticación
- [ ] **Auth** — Login con JWT
- [ ] **Search** — Búsqueda y filtros avanzados

---

## 🛠️ Solucionar Problemas

| Problema | Solución |
|----------|----------|
| **Error 404 Endpoint not found** | La ruta no está en `src/routes.php` |
| **Database connection failed** | Verifica `.env` (credenciales, host, puerto) |
| **Class not found** | Ejecuta `docker exec api_php composer dump-autoload` |
| **Puerto 80 en uso** | Cambia a puerto 8080 en `docker-compose.yml` |
| **MySQL no conecta** | Espera 30s, verifica `docker logs api_mysql` |
| **Cambios en PHP no se ven** | PHP usa opcache: `docker-compose restart api_php` |
| **DBeaver no conecta** | Usa `localhost` (no `127.0.0.1`), puerto `3306` |

---

## 📚 Recursos y Documentación

- **PHP 8.2 Documentation:** https://www.php.net/docs.php
- **PDO Prepared Statements:** https://www.php.net/manual/en/pdo.prepared-statements.php
- **CORS Explained:** https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
- **Repository Pattern:** https://martinfowler.com/eaaCatalog/repository.html
- **Docker Documentation:** https://docs.docker.com/
- **PSR-4 Autoloading:** https://www.php-fig.org/psr/psr-4/

---

## 📝 Notas Técnicas Adicionales

Ver archivo **`CLAUDE.md`** para:
- Decisiones arquitectónicas en detalle
- Comandos Docker avanzados
- Configuración de Xdebug
- Estructura de las tablas de BD
- Credenciales de testing

---

## ✨ Resumen del Flujo

```
Cliente HTTP
  ↓
.htaccess (redirige a index.php)
  ↓
public/index.php (carga autoloader y rutas)
  ↓
src/routes.php (Router registra rutas y middleware)
  ↓
src/Core/Router.php (busca la ruta que coincida)
  ↓
src/Middleware/CorsMiddleware.php (agrega headers CORS)
  ↓
src/Controllers/CategoryController.php (obtiene parámetros, valida)
  ↓
src/Services/CategoryService.php (lógica de negocio)
  ↓
src/Repositories/CategoryRepository.php (consulta BD)
  ↓
src/Core/Database.php (conexión PDO Singleton)
  ↓
MySQL (retorna datos)
  ↓
Mapeo a src/Models/Category.php (objetos)
  ↓
src/DTOs/CategoryCollectionDTO.php (empaqueta)
  ↓
src/Core/Response.php (genera JSON)
  ↓
Cliente recibe respuesta JSON con código 200 ✅
```

---

**Última actualización:** 2026-04-01  
**Autor:** ApiProject Team  
**Licencia:** Uso personal


