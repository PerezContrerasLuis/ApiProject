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

El archivo `.env` contiene las contraseñas y datos de la base de datos. No lo compartas con nadie.

### 3. Iniciar los contenedores

```bash
docker-compose up -d --build
```

Esto inicia:
- **PHP 8.2** con Apache en `http://localhost`
- **MySQL 8.0** en `localhost:3306`

  Comandos disponibles                                                                                                     
  ```bash                                                                                                                        
  ┌────────────────────────┬──────────────────────────────────┬──────────────────────────────────┐
  │        Comando         │             Qué hace             │              Datos               │
  ├────────────────────────┼──────────────────────────────────┼──────────────────────────────────┤
  │ docker-compose up -d   │ Crea y enciende contenedores     │ Se conservan                     │
  ├────────────────────────┼──────────────────────────────────┼──────────────────────────────────┤
  │ docker-compose down    │ Apaga y elimina contenedores     │ Se pierden (a menos que uses -v) │                         
  ├────────────────────────┼──────────────────────────────────┼──────────────────────────────────┤
  │ docker-compose stop    │ Solo apaga sin eliminar          │ (*) Se conservan                 │                         
  ├────────────────────────┼──────────────────────────────────┼──────────────────────────────────┤                         
  │ docker-compose start   │ Enciende contenedores existentes │ (*) Se conservan                 │
  ├────────────────────────┼──────────────────────────────────┼──────────────────────────────────┤                         
  │ docker-compose restart │ Reinicia los contenedores        │ (*) Se conservan                 │

```
### 4. Verificar que funciona

```bash
curl http://localhost/
```

Deberías ver una respuesta como:
```json
{"message":"Welcome to ApiProject","version":"1.0.0"}
```

## Estructura del proyecto

```
ApiProject/
├── public/               # Carpeta visible desde internet
│   ├── index.php        # Punto de entrada de la API
│   └── .htaccess        # Reglas para redirigir requests
├── src/                 # Código fuente (EN CONSTRUCCIÓN - vacío)
│   
├── docker/              # Configuración de Docker
│   └── php/
│       ├── Dockerfile   # Receta para crear el contenedor PHP
│       └── php.ini      # Configuración de PHP
├── docker-compose.yml   # Configura PHP + MySQL juntos
├── .env                 # Variables secretas (NO COMPARTIR)
├── .env.example         # Plantilla de .env
├── .gitignore           # Archivos que no se suben a Git
├── CLAUDE.md            # Notas técnicas
└── README.md            # Este archivo
```

> **⚠️ Nota:** La carpeta `src/` está vacía porque la estructura se va a crear más adelante. Por ahora solo tenemos la configuración de Docker.

## Endpoints disponibles

::TODO

## Cómo crear tus primeras rutas
::TODO

## Conectarse a la base de datos MySQL

Puedes usar **DBeaver** (programa gratuito) o **PhpMyAdmin** para ver la base de datos.

### Credenciales DBeaver:
- **Host:** `localhost`
- **Puerto:** `3306`
- **Usuario:** `root`
- **Contraseña:** `rootsecret`
- **Base de datos:** `api_db`

## Comandos útiles

### Ver logs de PHP
```bash
docker logs api_php
```

### Ver logs de MySQL
```bash
docker logs api_mysql
```

### Entrar al contenedor PHP (línea de comandos)
```bash
docker exec -it api_php bash
```

### Detener los contenedores
```bash
docker-compose down
```

### Reiniciar todo
```bash
docker-compose restart
```

### Eliminar datos de la base de datos (¡CUIDADO!)
```bash
docker-compose down -v
```

## Tecnologías usadas

| Tecnología | Versión | Para qué |
|---|---|---|
| PHP | 8.2 | Lenguaje de programación |
| Apache | 2.4 | Servidor web |
| MySQL | 8.0 | Base de datos |
| Xdebug | 3.5 | Debugging (encontrar errores) |
| Opcache | Nativo | Acelera PHP |

## Próximos pasos

El proyecto aún está en construcción. Aquí está el plan:

1. **Crear la estructura base** en `src/Core/`:
   - `Request.php` — Maneja datos que llegan
   - `Response.php` — Envía respuestas JSON
   - `Router.php` — Dirige requests a funciones
   - `Database.php` — Conecta a MySQL

2. **Crear archivo de rutas** en `src/routes.php`

3. **Crear Controllers** en `src/Controllers/`

4. **Crear Models** en `src/Models/` (clases que representan datos)

5. **Agregar Middleware** en `src/Middleware/` (validaciones)

6. **Integrar Composer** para autoload automático de clases

## Solucionar problemas

### "Connection refused" en el puerto 80
- Otro programa está usando el puerto 80
- Solución: cambia el puerto en `docker-compose.yml` a `8080:80`

### MySQL no conecta
- Espera 30 segundos a que MySQL se inicie
- Verifica que `DB_HOST=mysql` en el archivo `.env`

### No puedo acceder a archivos de MySQL
- Asegúrate de usar localhost, no 127.0.0.1 en DBeaver

## Contribuir

Si encuentras bugs o tienes ideas, avísame.

## Licencia

Este proyecto es de uso personal. Úsalo como quieras.

---

**¿Preguntas?** Revisa `CLAUDE.md` para notas técnicas más detalladas.
