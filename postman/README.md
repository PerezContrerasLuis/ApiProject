# Postman Collection - ApiProject

Colección de endpoints API para testing y desarrollo con Postman.

## Archivos Incluidos

- **ApiProject.postman_collection.json** — Colección completa de endpoints
- **ApiProject.postman_environment.json** — Variables de entorno (localhost)

## Cómo Importar

### En Postman Desktop

1. Abre Postman
2. Haz clic en **"Import"** (botón superior izquierdo)
3. Selecciona **"Upload Files"**
4. Elige `ApiProject.postman_collection.json`
5. Haz clic en **"Import"**

### Importar Environment

1. Haz clic en el **ícono de engranaje** (Settings, esquina superior derecha)
2. Ve a **Environments**
3. Haz clic en **"Import"**
4. Elige `ApiProject.postman_environment.json`
5. Haz clic en **"Import"**

### En Postman Web

1. Ve a [postman.com](https://postman.com)
2. Inicia sesión en tu cuenta
3. Haz clic en **"Import"**
4. Sube los archivos `.json`

---

## Variables de Entorno

El environment incluye variables precargadas:

```
base_url     = http://localhost/api/v1
auth_token   = (se llena automáticamente después de login)
email_admin  = admin@example.com
password     = admin123
```

### Actualizar Token Automáticamente

Después de hacer login, el token JWT se guarda automáticamente en la variable `auth_token`.

**Cómo configurar (si no funciona automáticamente):**

1. Ve al endpoint `POST /auth/login`
2. Haz clic en **"Tests"** (tab superior)
3. Agrega este código:

```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.token);
}
```

4. Luego, en otros endpoints, usa el header:
   ```
   Authorization: Bearer {{auth_token}}
   ```

---

## Endpoints Disponibles

### Públicos (sin token)

- `GET /test` — Health check
- `POST /auth/login` — Obtener JWT token
- `POST /auth/register` — Registrar nuevo usuario (solo admin)

### Protegidos (requieren token)

- `GET /categories` — Listar categorías (paginado)
- `GET /categories/{id}` — Obtener una categoría

---

## Cómo Usar

1. **Asegúrate que la API esté corriendo:**
   ```bash
   docker-compose up -d
   ```

2. **Importa la colección y environment en Postman**

3. **Selecciona el environment:** Arriba a la derecha, dropdown que dice "No Environment" → selecciona "ApiProject"

4. **Haz login primero:**
   - Abre `POST /auth/login`
   - Haz clic en **"Send"**
   - El token se guarda automáticamente en `{{auth_token}}`

5. **Ahora puedes usar otros endpoints:** El token se incluye automáticamente en el header `Authorization`

---

## Estructura de la Colección

```
ApiProject
├── Health
│   └── GET /test
├── Auth
│   ├── POST /auth/login
│   └── POST /auth/register
└── Categories
    ├── GET /categories
    └── GET /categories/{id}
```

---

## Notas

- Los archivos `.json` contienen toda la configuración: headers, body, tests, variables
- Puedes compartir la **colección** pero ten cuidado con el **environment** si contiene credenciales reales
- Para trabajo en equipo, excluir credenciales del environment y compartir un `.env.example` en su lugar

---

## Sincronización en Cloud (Opcional)

Si tienes una cuenta Postman, puedes sincronizar la colección a la nube:

1. En Postman, ve a **File** → **Settings** → **Sync**
2. Inicia sesión en tu cuenta Postman
3. La colección se sincroniza automáticamente

---

## Troubleshooting

**Error 401 Unauthorized:**
- Verifica que hayas hecho login primero
- Revisa que el token no haya expirado (timeout: 1 hora)
- Asegúrate de que el environment esté seleccionado

**Error 422 Validation Failed:**
- Revisa que los campos requeridos estén presentes
- Verifica el body JSON (sintaxis válida, valores válidos)

**Error 500 Internal Server Error:**
- Verifica que la API esté corriendo: `docker-compose ps`
- Revisa los logs: `docker-compose logs -f api_php`

---

## Actualizar la Colección

Si agregas nuevos endpoints:

1. Exporta nuevamente la colección desde Postman
2. Reemplaza el archivo `ApiProject.postman_collection.json` en este directorio
3. Haz commit y push al repositorio

```bash
git add postman/
git commit -m "docs: update Postman collection"
git push
```
