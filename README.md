# 🎟️ Rifas La Paz

Proyecto PHP (MVC ligero) para presentar y participar en rifas. Usa un enrutador propio, plantillas de vistas y carga de assets (CSS/JS) organizada.

---

## ✨ Características

- Enrutamiento simple basado en controlador/método (por defecto `RifaController::index`).
- Sistema de vistas con plantillas (`header`, `footer`, `header-login`, `header-menu`).
- Carga de assets con soporte para aplicaciones en subcarpetas.
- Logs y manejo de errores básico.

---

## 📦 Requisitos

- PHP 8.0+
- Apache (XAMPP/WAMP) o servidor compatible
- MySQL/MariaDB (opcional, según funcionalidades)

---

## 🚀 Instalación (Windows + XAMPP)

1. Copia el proyecto a:

   - `C:\xampp\htdocs\rifas-lapaz` (o el nombre que prefieras)

2. Inicia Apache desde XAMPP.

3. Configura (opcional) la conexión a BD en `settings/init.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', '');
   define('DB_USER', 'root');
   define('DB_PASSWORD', '');
   ```

4. Accede en el navegador:

   - http://localhost/rifas-lapaz/

   El controlador por defecto es `rifa`, así que cargará `RifaController::index()`.

---

## ⚙️ Configuración clave

Archivo: `settings/init.php`

- `DEFAULT_CONTROLLER`: Controlador por defecto. Por omisión: `rifa`.
- `BASE_URL`: Se calcula automáticamente; si usas un virtual host o subcarpeta, puedes fijarlo manualmente.
- `ASSETS_PATH`: Por defecto apunta a `BASE_URL/assets/`.

Ejemplo de definición manual:

```php
define('BASE_URL', 'http://localhost/rifas-lapaz');
define('ASSETS_PATH', BASE_URL . '/assets/');
```

---

## 🧭 Enrutamiento

El enrutador (`libs/app.php`) interpreta URLs como:

- `/rifa/index` → `RifaController::index()`

Si no hay reescritura de URLs, también puedes usar:

- `/index.php?url=rifa/index`

Opcional (Apache .htaccess) para URLs limpias:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## 🗂️ Estructura

```
index.php
assets/
   css/
      rifa.css
   js/
      rifa.js
controllers/
   RifaController.php
libs/
   app.php, view.php, controller.php, model.php, db.php, ...
settings/
   init.php
views/
   Rifa/
      index.php
   templates/
      header.php, header-login.php, header-menu.php, footer.php
logs/
   auth.log, error.log, info.log
```

---

## 🖼️ Vistas y Assets

Desde un controlador, puedes renderizar una vista y solicitar CSS/JS específicos:

```php
$this->view->render('Rifa/index', [
   'pageTitle' => 'Rifas La Paz - Participa',
   'useSidebar' => false
], ['rifa.css', 'rifa.js']);
```

El motor de vistas generará:

- CSS en `<head>`: `BASE_URL/assets/css/rifa.css`
- JS en el footer: `BASE_URL/assets/js/rifa.js`

Notas:

- Si tu app está en subcarpeta (p. ej. `http://localhost/rifas-lapaz`), las rutas se calculan con `BASE_URL`/`getBaseUrl()` para evitar errores.
- Puedes usar `getAssetUrl('css/archivo.css')` o `getImageUrl('carpeta/imagen.png')` dentro de las vistas.

---

## 🪵 Logs y errores

- Archivos de log en `logs/` (info, error, auth).
- Configuración de errores en `libs/errorConfig.php` y manejador en `libs/errorHandler.php`.

---

## 🧩 Añadir una página nueva

1. Crea un controlador, p. ej. `controllers/ExampleController.php` con método `index`.
2. Crea la vista en `views/Example/index.php`.
3. Visita `/example/index` (o `?url=example/index`).

---

## 🛠️ Solución de problemas

- “El JS carga, pero el CSS no”:

  - Asegúrate de que el `head` se esté generando con `getHeadContent()` en el template (`header*.php`).
  - Verifica que `BASE_URL`/`ASSETS_PATH` apunten a la carpeta correcta.
  - Confirma que exista `assets/css/rifa.css` y que responda 200 en el navegador.

- “Las rutas se rompen en subcarpetas”:
  - Define `BASE_URL` manualmente en `settings/init.php` (ver ejemplo arriba).

---

## 📄 Licencia

MIT (si no se especifica lo contrario).

---

Hecho con 💙 para facilitar la participación en Rifas La Paz.
