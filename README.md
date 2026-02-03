# PolaApp

PolaApp es una aplicación web integral para la gestión empresarial, diseñada para administrar ventas, inventario, clientes y finanzas. Construida con Laravel y Livewire, ofrece una experiencia de usuario fluida y reactiva.

## Características Principales

### 👥 Gestión de Clientes
- **Directorio de Clientes:** Listado completo con búsqueda y filtrado.
- **Perfiles Detallados:** Visualización de información de contacto e historial.
- **Gestión:** Creación y edición sencilla de la información de clientes.

### 📦 Inventario y Productos
- **Catálogo de Productos:** Vista general de todos los productos disponibles en inventario.
- **Gestión de Stock (Admin):** Creación, edición y actualización de productos.
- **Proveedores y Compras (Admin):** Registro de proveedores y gestión de compras de inventario para reabastecimiento.

### 💰 Ventas y Finanzas
- **Registro de Ventas:** Interfaz eficiente para registrar nuevas ventas a clientes.
- **Seguimiento de Gastos:** Registro y categorización de gastos operativos.
- **Liquidaciones:** Gestión de liquidaciones con funcionalidad de generación y descarga de PDF.

### 📊 Reportes y Análisis (Admin)
- **Panel de Rentabilidad:** Reporte detallado de Finanzas (`SalesProfitabilityReport`) para analizar márgenes y ventas.
- **Reporte de Devoluciones:** Seguimiento de productos devueltos.
- **Cuentas por Pagar:** Reportes detallados de obligaciones pendientes con exportación a PDF.

### 🔐 Seguridad y Roles
El sistema implementa un estricto Control de Acceso Basado en Roles (RBAC):
- **Administrador:** Acceso total al sistema, gestión de usuarios, inventario completo y reportes financieros.
- **Vendedor:** Acceso enfocado en la operativa diaria: gestión de clientes, registro de ventas y visualización de productos.

## Tecnologías Utilizadas

- **Backend:** [Laravel 12](https://laravel.com)
- **Frontend Interactivo:** [Livewire 3](https://livewire.laravel.com)
- **Estilos:** [Tailwind CSS](https://tailwindcss.com)
- **Base de Datos:** MySQL / MariaDB
- **Autenticación:** Laravel Breeze
- **Gestión de Permisos:** Spatie Laravel Permission
- **Generación de Documentos:** DomPDF / Laravel Excel

## Instalación y Configuración

Sigue estos pasos para levantar el proyecto en tu entorno local:

1. **Clonar el repositorio:**
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd PolaApp
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Frontend:**
   ```bash
   npm install && npm run build
   ```

4. **Configurar el entorno:**
   Duplica el archivo de ejemplo y genera la clave de la aplicación:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Asegúrate de configurar tus credenciales de base de datos en el archivo `.env`.*

5. **Ejecutar migraciones y datos de prueba (Seeders):**
   ```bash
   php artisan migrate --seed
   ```
   *Este comando creará las tablas necesarias y poblará la base de datos con usuarios y datos iniciales.*

## Credenciales de Acceso (Entorno Local)

Al ejecutar los seeders, se crean automáticamente los siguientes usuarios para pruebas:

| Rol | Email | Contraseña |
| --- | --- | --- |
| **Administrador** | `admin@polaapp.com` | `admin123` |
| **Vendedor** | `vendedor@polaapp.com` | `vendedor123` |

## Ejecución Local

Para iniciar el servidor de desarrollo, ejecuta:

```bash
php artisan serve
```

Accede a la aplicación en `http://localhost:8000`.

---
**PolaApp** - Simplificando la gestión comercial.
