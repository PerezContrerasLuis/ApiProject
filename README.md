# ApiProject

Una API REST desarrollada en PHP 8.2 sin frameworks, diseñada desde cero con arquitectura limpia y moderna.

## ¿Qué es este proyecto?

Es una API (Interfaz de Programación) que puedes usar para recibir y enviar datos. Está hecha en **PHP puro** sin usar frameworks como Laravel o Symfony, lo que la hace más ligera y personalizable.

## Requisitos

Necesitas tener instalado en tu computadora:

- **Docker** — para ejecutar PHP y MySQL sin instalarlos directamente
- **Docker Compose** — para manejar múltiples contenedores

Si no tienes Docker, descárgalo aquí: https://www.docker.com/products/docker-desktop

## Instalación rápida

### 1. Clonar o descargar el proyecto

```bash
cd /ruta/donde/quieras/el/proyecto
git clone <url-del-repositorio>
cd ApiProject
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

El archivo `.env` contiene las contraseñas y datos de la base de datos. **No lo compartas con nadie.**

### 3. Iniciar los contenedores

```bash
docker-compose up -d --build
```

Esto inicia:
- **PHP 8.2** con Apache en `http://localhost`
- **MySQL 8.0** en `localhost:3306`

### Comandos disponibles para controlar los contenedores

| Comando | Qué hace | Datos |
|---|---|---|
| `docker-compose up -d` | Crea y enciende contenedores | Se conservan |
| `docker-compose down` | Apaga y elimina contenedores | Se pierden |
| `docker-compose stop` | Solo apaga sin eliminar | ✅ Se conservan |
| `docker-compose start` | Enciende contenedores existentes | ✅ Se conservan |
| `docker-compose restart` | Reinicia los contenedores | ✅ Se conservan |

### 4. Verificar que funciona

```bash
curl http://localhost/test
```

Deberías ver:
```json
{"message":"Welcome to ApiProject","version":"1.0.0"}
```

## Estructura del proyecto

```
ApiProject/
├── public/               # Carpeta visible desde internet
│   ├── index.php        # Punto de entrada (carga src/routes.php)
│   └── .htaccess        # Redirige todas las requests a index.php
├── src/                 # Código fuente (oculto desde internet)
│   └── routes.php       # Define todas las rutas de la API
├── docker/              # Configuración de Docker
│   └── php/
│       ├── Dockerfile   # Imagen con PHP 8.2 y extensiones
│       └── php.ini      # Configuración de PHP
├── docker-compose.yml   # Define servicios (PHP + MySQL)
├── .env                 # Variables de entorno (NO COMPARTIR)
├── .env.example         # Plantilla de .env
├── .gitignore           # Archivos ignorados por Git
├── CLAUDE.md            # Documentación técnica
└── README.md            # Este archivo
```

## Estado actual

### ✅ Listo

- Docker + Apache + PHP 8.2 funcionando
- MySQL 8.0 accesible en puerto 3306
- `public/index.php` carga rutas
- `.htaccess` redirige todo a `index.php`

### 🔧 En construcción

- Estructura de carpetas en `src/` (Controllers, Models, etc.)
- Clases Core (Request, Response, Router, Database)
- Sistema de routing automático

## Endpoints disponibles

### GET /test
```bash
curl http://localhost/test
```

**Respuesta:**
```json
{"message":"Welcome to ApiProject","version":"1.0.0"}
```

## Cómo agregar nuevas rutas

Edita `src/routes.php`:

```php
<?php

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Ruta GET /usuarios
if ($path === '/usuarios' && $method === 'GET') {
    echo json_encode([
        'usuarios' => [
            ['id' => 1, 'nombre' => 'Juan'],
            ['id' => 2, 'nombre' => 'María']
        ]
    ]);
    exit;
}

// Ruta GET /usuarios/{id}
if (preg_match('#^/usuarios/(\d+)$#', $path, $matches) && $method === 'GET') {
    $id = $matches[1];
    echo json_encode(['id' => $id, 'nombre' => 'Usuario']);
    exit;
}

// Ruta 404 por defecto
http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada']);
exit;
```

**Nota:** Esto es temporal. Cuando construyas el Router, el routing será automático.

## Comandos Docker útiles

### Ver logs

```bash
# Logs de PHP
docker logs api_php

# Logs de MySQL
docker logs api_mysql

# Ver en tiempo real
docker logs -f api_php
```

### Entrar al contenedor PHP

```bash
docker exec -it api_php bash
```

### Ejecutar comandos PHP

```bash
docker exec api_php php -v
docker exec api_php php -m  # Ver extensiones
```

## Conectarse a MySQL

### Con DBeaver (recomendado)

- **Host:** `localhost`
- **Puerto:** `3306`
- **Usuario:** `root` o `api_user`
- **Contraseña:** `rootsecret` o `secret`
- **Base de datos:** `api_db`

### Desde línea de comandos

```bash
docker exec -it api_mysql mysql -u root -prootsecret -D api_db
```

## Tecnologías

| Tecnología | Versión | Para qué |
|---|---|---|
| PHP | 8.2 | Lenguaje de programación |
| Apache | 2.4 | Servidor web |
| MySQL | 8.0 | Base de datos |
| Xdebug | 3.5 | Debugging |
| Opcache | Nativo | Caché de PHP |

## Próximos pasos

1. Crear estructura en `src/Core/`:
   - `Request.php` — Maneja datos que llegan
   - `Response.php` — Envía respuestas JSON
   - `Router.php` — Routing automático
   - `Database.php` — Conexión a MySQL

2. Crear carpetas:
   - `src/Controllers/`
   - `src/Models/`
   - `src/Middleware/`
   - `src/Helpers/`

3. Reemplazar routing manual en `src/routes.php` con Router automático

4. Integrar Composer para autoload PSR-4

## Solucionar problemas

### Puerto 80 en uso

```bash
# Cambiar a puerto 8080 en docker-compose.yml
ports:
  - "8080:80"  # En lugar de "80:80"

# Luego acceder a http://localhost:8080/test
```

### MySQL no conecta

- Espera 30 segundos a que MySQL se inicie
- Verifica que `DB_HOST=mysql` en `.env`
- Revisa logs: `docker logs api_mysql`

### Cambios en PHP no aparecen

PHP está en un contenedor. Después de cambios:

```bash
docker-compose restart api_php
```

## Licencia

De uso personal. Úsalo como quieras.

---

**Notas técnicas:** Ver `CLAUDE.md`
