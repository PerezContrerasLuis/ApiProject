# ApiProject

API REST desarrollada en PHP 8.2 sin frameworks. Arquitectura limpia, escalable y lista para producción.

## ¿Qué es este proyecto?

Una API de catálogo de productos con inventario. Hecha en **PHP puro** (sin Laravel, Symfony, etc.), lo que la hace más ligera y totalmente personalizable.

**Características actuales:**
- ✅ Catálogo de 20+ productos
- ✅ Sistema de inventario con movimientos de entrada/salida
- ✅ Categorías jerárquicas de productos
- ✅ Gestión de proveedores
- ✅ Sistema de roles para usuarios (admin, manager, viewer)
- ✅ Base de datos completamente creada con datos de prueba

## Requisitos

- **Docker** — Ejecutar PHP y MySQL en contenedores
- **Docker Compose** — Orquestar múltiples contenedores

Descarga desde: https://www.docker.com/products/docker-desktop

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

**Espera 15 segundos** para que MySQL se inicie y se carguen las migraciones + datos de prueba.

Verifica que todo esté listo:

```bash
docker logs api_php
```

Deberías ver:
```
✨ Setup completado correctamente
✅ Iniciando Apache...
```

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

## Estructura del proyecto

```
ApiProject/
├── public/                          # Carpeta visible desde web
│   ├── index.php                   # Punto de entrada de la API
│   └── .htaccess                   # Redirige requests a index.php
├── src/                             # Código fuente (oculto desde web)
│   └── routes.php                  # Define rutas de la API
├── database/                        # Base de datos
│   ├── migrations/                 # Creación de tablas
│   │   ├── 001_create_users_table.sql
│   │   ├── 002_create_categories_table.sql
│   │   ├── 003_create_suppliers_table.sql
│   │   ├── 004_create_products_table.sql
│   │   └── 005_create_inventory_movements_table.sql
│   └── seeders/                    # Datos de prueba
│       ├── 001_seed_users.sql
│       ├── 002_seed_categories.sql
│       ├── 003_seed_suppliers.sql
│       ├── 004_seed_products.sql
│       └── 005_seed_inventory_movements.sql
├── cli/                             # Scripts PHP
│   └── setup.php                   # Ejecuta migraciones y seeders
├── docker/                          # Configuración Docker
│   └── php/
│       ├── Dockerfile             # Imagen PHP 8.2-Apache
│       ├── php.ini                # Configuración PHP
│       └── entrypoint.sh           # Script inicial del contenedor
├── docker-compose.yml               # Orquesta PHP + MySQL
├── .env                             # Variables de entorno (no compartir)
├── .env.example                     # Template de .env
├── .gitignore                       # Archivos ignorados por Git
├── CLAUDE.md                        # Documentación técnica
└── README.md                        # Este archivo
```

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

## Endpoints actuales

### GET /test

**Propósito:** Verificar que la API responde

```bash
curl http://localhost/test
```

**Respuesta:**
```json
{"message":"Welcome to ApiProject","version":"1.0.0"}
```



1. **Crear clases Core** en `src/Core/`:
   - Request.php — Maneja HTTP requests
   - Response.php — Maneja HTTP responses JSON
   - Router.php — Routing automático con parámetros
   - Database.php — PDO singleton para MySQL

2. **Crear Controllers** en `src/Controllers/`:
   - ProductsController
   - CategoriesController
   - SuppliersController
   - UsersController
   - InventoryController

3. **Crear Models** en `src/Models/`:
   - Product
   - Category
   - Supplier
   - User
   - InventoryMovement

4. **Crear Middleware** en `src/Middleware/`:
   - AuthenticationMiddleware
   - AuthorizationMiddleware
   - ValidationMiddleware

5. **Implementar endpoints** en `src/routes.php`:
   - CRUD para cada entidad
   - Filtros y búsqueda
   - Paginación

6. **Integrar Composer** cuando tengas estructura base:
   - PSR-4 autoloading
   - Dependencias opcionales (phpdotenv, etc.)

## Solucionar problemas

### Puerto 80 en uso

Otro programa usa el puerto. Cambia en `docker-compose.yml`:

```yaml
services:
  php:
    ports:
      - "8080:80"  # En lugar de "80:80"
```

Luego accede a `http://localhost:8080`

### MySQL no conecta

1. Espera 30 segundos a que inicie
2. Verifica logs: `docker logs api_mysql`
3. Asegúrate que `DB_HOST=mysql` en `.env`

### DBeaver no conecta a BD

- Usa `localhost`, no `127.0.0.1`
- Puerto debe ser `3306`
- Verifica credenciales en tabla arriba

### Cambios en PHP no se ven

PHP está en contenedor con opcache. Reinicia:

```bash
docker-compose restart api_php
```

## Contribuir

Reporta bugs en la sección de Issues.

## Licencia

Uso personal. Úsalo como necesites.


