# ApiProject

REST API desarrollada en PHP 8.2 sin frameworks, con arquitectura profesional en capas. Sistema completo de gestión de productos, inventario y usuarios con autenticación basada en JWT.

---

## Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Características](#características)
3. [Requisitos Previos](#requisitos-previos)
4. [Instalación y Configuración](#instalación-y-configuración)
5. [Uso](#uso)
6. [Testing con Postman](#testing-con-postman)
7. [Estructura del Proyecto](#estructura-del-proyecto)
8. [Endpoints Disponibles](#endpoints-disponibles)
9. [Credenciales de Testing](#credenciales-de-testing)
10. [Documentación Técnica](#documentación-técnica)

---

## Descripción General

ApiProject es una API REST profesional construida desde cero en PHP 8.2 sin uso de frameworks. Implementa patrones de diseño reconocidos como Repository Pattern, Service Layer y Middleware Pipeline para mantener una separación clara de responsabilidades y facilitar el mantenimiento y escalabilidad del código.

La aplicación está completamente containerizada con Docker, incluyendo PHP 8.2 con Apache y MySQL 8.0, lo que garantiza un entorno consistente entre desarrollo, testing y producción.

---

## Características

- **Gestión de Productos**: Catálogo de 20+ productos con categorización jerárquica
- **Sistema de Inventario**: Tracking de movimientos de entrada y salida de stock
- **Gestión de Categorías**: Estructura jerárquica de categorías de productos
- **Gestión de Proveedores**: Registro y manejo de proveedores
- **Sistema de Usuarios**: Gestión de usuarios con roles (admin, manager, viewer)
- **Autenticación**: Login con JWT y validación de tokens en endpoints protegidos
- **Arquitectura Profesional**: Separación clara en capas (Controller, Service, Repository)
- **Docker**: Entorno containerizado con PHP 8.2-Apache y MySQL 8.0
- **Base de Datos**: 5 tablas relacionadas con 40+ registros de datos de prueba
- **Middleware Pipeline**: Soporte para CORS, autenticación y validaciones

---

## Requisitos Previos

Para ejecutar este proyecto necesitas:

- Docker Engine (versión 20.10+)
- Docker Compose (versión 1.29+)
- Git
- Cliente para probar APIs (Postman, curl, Insomnia, etc.) - opcional

No requiere tener PHP, MySQL o Apache instalados localmente, ya que todo se ejecuta en contenedores.

---

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/ApiProject.git
cd ApiProject
```

### 2. Configurar Variables de Entorno

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Las variables de entorno por defecto están preconfiguradas para desarrollo. Si necesitas cambiar credenciales de BD o puertos, edita el archivo `.env`.

### 3. Construir e Iniciar los Contenedores

```bash
docker-compose up -d
```

Este comando:
- Construye la imagen de PHP 8.2-Apache
- Construye la imagen de MySQL 8.0
- Inicia ambos contenedores
- Ejecuta automáticamente migraciones de base de datos
- Carga datos de prueba

Verifica que los contenedores estén corriendo:

```bash
docker-compose ps
```

Deberías ver dos contenedores: `api_php` y `api_mysql`.

### Comandos de Docker Compose

A continuación se detalla la tabla de comandos más comunes para gestionar los contenedores:

| Comando | Descripción | Cuándo usarlo |
|---------|-------------|---------------|
| `docker-compose up -d` | Inicia los contenedores en segundo plano | Primer inicio o después de cambios en docker-compose.yml |
| `docker-compose down` | Detiene y elimina los contenedores | Cuando terminas de trabajar y quieres liberar recursos |
| `docker-compose ps` | Lista los contenedores en ejecución | Para verificar que todo esté corriendo correctamente |
| `docker-compose logs -f api_php` | Muestra los logs del contenedor PHP en tiempo real | Para debugging y monitoreo de errores |
| `docker-compose logs -f api_mysql` | Muestra los logs del contenedor MySQL en tiempo real | Para debugging de problemas de base de datos |
| `docker-compose restart api_php` | Reinicia solo el contenedor PHP | Después de cambios en código PHP (por Opcache) |
| `docker-compose restart api_mysql` | Reinicia solo el contenedor MySQL | Después de cambios en configuración de BD |
| `docker-compose restart` | Reinicia todos los contenedores | Cuando necesitas reiniciar todo el stack |
| `docker-compose stop` | Pausa los contenedores sin eliminarlos | Cuando necesitas detener temporalmente sin limpiar |
| `docker-compose start` | Reanuda los contenedores pausados | Después de usar `docker-compose stop` |
| `docker-compose exec api_php bash` | Abre una terminal dentro del contenedor PHP | Para ejecutar comandos o inspeccionar archivos |
| `docker-compose exec api_mysql mysql -u api_user -psecret api_db` | Accede a MySQL interactivamente | Para ejecutar queries o inspeccionar datos |

### 4. Verificar Instalación

Accede a la API:

```bash
curl http://localhost/api/v1/categories
```

Deberías recibir una respuesta JSON con el listado de categorías.

---

## Uso

### Acceso a la API

La API está disponible en: `http://localhost/api/v1`

Todos los endpoints retornan respuestas en formato JSON.

### Acceso a la Base de Datos

Para conectarse a MySQL desde un cliente (DBeaver, MySQL Workbench, etc.):

```
Host: localhost
Puerto: 3306
Usuario: api_user
Contraseña: secret
Base de datos: api_db
```

### Acceso a Contenedores

Para ejecutar comandos dentro del contenedor PHP:

```bash
docker exec -it api_php bash
```

Para ejecutar comandos MySQL:

```bash
docker exec -it api_mysql mysql -u api_user -psecret api_db
```

Para ver logs del contenedor PHP:

```bash
docker logs -f api_php
```

---

## Testing con Postman

Se incluye una colección de Postman con todos los endpoints pre-configurados para facilitar testing y desarrollo.

### Características

- Todos los endpoints configurados y organizados por categorías
- Variables de entorno (base_url, auth_token)
- Validaciones automáticas en respuestas
- Token JWT se guarda automáticamente después de login
- Tests pre-configurados en endpoints

### Archivos Incluidos

- **`postman/ApiProject.postman_collection.json`** — Colección completa de endpoints
- **`postman/ApiProject.postman_environment.json`** — Variables de entorno configuradas
- **`postman/README.md`** — Instrucciones detalladas de importación y uso

### Importación Rápida

1. En Postman: **Import** → **Upload Files**
2. Selecciona `postman/ApiProject.postman_collection.json`
3. Importa el environment desde `postman/ApiProject.postman_environment.json`
4. Selecciona el environment en el dropdown superior derecho
5. Haz login en `POST /auth/login` para obtener token

Para instrucciones completas, consulta `postman/README.md`.

---

## Estructura del Proyecto

```
ApiProject/
├── public/                 # Raíz web (accesible por HTTP)
│   ├── index.php          # Front controller
│   └── .htaccess          # Reescritura de URL para Apache
├── src/                    # Código fuente de la aplicación
│   ├── Core/              # Clases núcleo
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── Database.php
│   │   └── Middleware.php
│   ├── Controllers/       # Manejadores de HTTP (endpoints)
│   │   ├── CategoryController.php
│   │   └── AuthController.php
│   ├── Services/          # Lógica de negocio
│   │   ├── CategoryService.php
│   │   └── AuthService.php
│   ├── Repositories/      # Acceso a datos
│   │   └── CategoryRepository.php
│   ├── Models/            # Entidades (mapeo con BD)
│   │   └── Category.php
│   ├── Middleware/        # Implementaciones de middleware
│   │   ├── CorsMiddleware.php
│   │   └── AuthMiddleware.php
│   └── routes.php         # Definición de rutas
├── database/
│   ├── migrations/        # Creación de tablas
│   └── seeders/          # Datos iniciales de prueba
├── docker/               # Configuración de Docker
│   ├── php/
│   │   ├── Dockerfile
│   │   └── entrypoint.sh
│   └── mysql/
│       └── Dockerfile
├── cli/                   # Scripts de utilidad
│   └── setup.php          # Ejecuta migraciones y seeders
├── vendor/               # Dependencias de Composer (auto-generado)
├── .env                  # Variables de entorno (local)
├── .env.example          # Plantilla de variables de entorno
├── composer.json         # Dependencias PHP
├── composer.lock         # Versiones exactas de dependencias
├── docker-compose.yml    # Configuración Docker Compose
└── README.md            # Este archivo
```

---

## Endpoints Disponibles

### Categorías

**GET /api/v1/categories** - Listar categorías (paginado)

Parámetros de query:
- `page` (default: 1)
- `per_page` (default: 10, máximo: 100)

Ejemplo:
```bash
curl "http://localhost/api/v1/categories?page=1&per_page=10"
```

**GET /api/v1/categories/{id}** - Obtener detalle de una categoría

Ejemplo:
```bash
curl http://localhost/api/v1/categories/1
```

### Autenticación

**POST /api/v1/auth/login** - Login

Autentica un usuario y retorna un JWT token para usar en endpoints protegidos.

Body requerido:
```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```

Respuesta exitosa (200):
```json
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

Códigos de error:
- `400` - Email o contraseña vacíos
- `401` - Email o contraseña incorrectos
- `500` - Error interno del servidor

Ejemplo:
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

---

**POST /api/v1/auth/register** - Registrar nuevo usuario

Crea un nuevo usuario en el sistema. El email debe ser único y la contraseña será hasheada automáticamente.

Body requerido:
```json
{
  "name": "Nombre del Usuario",
  "email": "usuario@example.com",
  "password": "contraseña123",
  "role": "viewer"
}
```

Validaciones:
- `name` - Requerido, mínimo 3 caracteres
- `email` - Requerido, formato válido, debe ser único
- `password` - Requerido, mínimo 6 caracteres
- `role` - Requerido, valores permitidos: `admin`, `manager`, `viewer`

Respuesta exitosa (201):
```json
{
  "status": "success",
  "data": {
    "id": 4,
    "name": "Nombre del Usuario",
    "email": "usuario@example.com",
    "role": "viewer",
    "created_at": "2026-04-10T14:30:00Z"
  }
}
```

Códigos de error:
- `400` - Datos requeridos incompletos o formato inválido
- `422` - Validación fallida (email duplicado, password muy corta, role inválido)
- `500` - Error interno del servidor

Ejemplo:
```bash
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "segura123",
    "role": "viewer"
  }'
```

---

Nota: Para más detalles sobre endpoints adicionales y campos de respuesta, consulta la documentación técnica.

---

## Credenciales de Testing

### Usuarios API

La base de datos incluye tres usuarios de prueba:

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@example.com | admin123 | admin |
| manager@example.com | manager123 | manager |
| viewer@example.com | viewer123 | viewer |

### Base de Datos

```
Base de datos: api_db
Usuario: api_user
Contraseña: secret
Puerto: 3306
```

---

## Documentación Técnica

Para información detallada sobre la arquitectura, patrones de diseño, flujo de solicitudes y decisiones técnicas, consulta los siguientes archivos:

- **CLAUDE.md** - Notas técnicas completas sobre la arquitectura, setup, y decisiones de diseño

Documentación adicional está en desarrollo:
- Guía de patrones de diseño
- Flujo detallado de solicitudes HTTP
- Referencia de endpoints
- Guía de extensión y contribución

- **ManualTecnico.md** - Notas técnicas completas sobre el flujo de los endpoints existentes.

- **postman/README.md** - Archivos json collections y environment para probar en el cliente de Postman

---

## Comandos Útiles

### Docker

Iniciar contenedores:
```bash
docker-compose up -d
```

Detener contenedores:
```bash
docker-compose down
```

Reinicar contenedor PHP (después de cambios en código):
```bash
docker-compose restart api_php
```

Ver logs:
```bash
docker logs -f api_php
docker logs -f api_mysql
```

### Composer (dentro del contenedor)

Instalar dependencias:
```bash
docker exec api_php composer install
```

Agregar nueva dependencia:
```bash
docker exec api_php composer require nombre/del-paquete
```

Regenerar autoloader:
```bash
docker exec api_php composer dump-autoload -o
```

### MySQL (dentro del contenedor)

Conectar a la BD:
```bash
docker exec -it api_mysql mysql -u api_user -psecret -D api_db
```

Ejecutar un archivo SQL:
```bash
docker exec -i api_mysql mysql -u api_user -psecret api_db < archivo.sql
```

### Ver información de PHP

Extensiones instaladas:
```bash
docker exec api_php php -m
```

Versión de PHP:
```bash
docker exec api_php php -v
```

---

## Notas de Desarrollo

- Los cambios en archivos PHP requieren reiniciar el contenedor debido a opcache: `docker-compose restart api_php`
- Xdebug está configurado y disponible en puerto 9003
- Las variables de entorno se cargan desde `.env` en el startup
- No incluyas cambios en `vendor/` en commits - se regenera automáticamente

---

## Licencia

Este proyecto está bajo licencia MIT. Consulta el archivo LICENSE para más detalles.

---

## Soporte

Para reportar problemas, sugerencias o contribuciones, abre un issue en el repositorio.
