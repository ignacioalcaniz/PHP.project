# 🍷 Cava Noble

## Sistema de Administración de E-commerce

**Proyecto desarrollado para la Diplomatura en PHP y MySQL**

Autor: Ignacio Alcañiz

---

# Descripción

Cava Noble es una aplicación web desarrollada en PHP y MySQL que simula un comercio electrónico especializado en la venta de vinos.

El sistema permite administrar un catálogo completo de productos, gestionar clientes registrados, realizar compras mediante un carrito de compras, generar pedidos, administrar el stock automáticamente y controlar todo el proceso comercial desde un panel administrativo seguro.

Además de cumplir con los requisitos solicitados por la consigna, el proyecto incorpora funcionalidades propias de una aplicación profesional de comercio electrónico.

---

# Tecnologías utilizadas

- PHP 8
- MySQL
- HTML5
- CSS3
- JavaScript
- PDO
- Apache
- XAMPP

---

# Funcionalidades principales

## Cliente

- Registro de usuarios.
- Inicio de sesión.
- Recordarme por 30 días.
- Catálogo de productos.
- Búsqueda de productos.
- Detalle de cada vino.
- Carrito de compras.
- Modificación de cantidades.
- Eliminación de productos.
- Checkout completo.
- Confirmación de compra.
- Generación automática de pedidos.

---

## Administrador

- Panel administrativo.
- CRUD de productos.
- CRUD de categorías.
- CRUD de bodegas.
- Gestión de usuarios.
- Gestión de pedidos.
- Cambio de estados.
- Reportes comerciales.
- Auditoría administrativa.
- Centro de seguridad.

---

# Seguridad implementada

El sistema incorpora distintas medidas de seguridad propias de aplicaciones web modernas.

- Protección CSRF.
- Cloudflare Turnstile.
- Contraseñas protegidas mediante password_hash().
- Consultas preparadas (PDO Prepared Statements).
- Protección contra SQL Injection.
- Validación de formularios.
- Sesiones seguras.
- Cookies HttpOnly.
- Cookies SameSite.
- Rate Limiting para Login.
- Remember Me mediante tokens.
- Variables de entorno (.env).
- Transacciones SQL.
- Control automático de stock.
- Auditoría de acciones administrativas.
- Arquitectura preparada para incorporar autenticación de dos factores (2FA).

---

# Base de datos

El proyecto utiliza una base de datos relacional en MySQL.

Las tablas principales son:

- usuarios
- productos
- categorias
- bodegas
- pedidos
- pedido_items
- login_attempts
- remember_tokens
- admin_logs

Las relaciones entre tablas permiten mantener la integridad de la información utilizando claves foráneas.

---

# Adaptación de la consigna

La consigna propone un sistema genérico de administración de pedidos utilizando nombres de archivos de ejemplo.

En este proyecto dichos requerimientos fueron implementados y adaptados a una aplicación de comercio electrónico real denominada **Cava Noble**, manteniendo la funcionalidad solicitada pero utilizando una arquitectura más cercana a un entorno profesional.

## Equivalencia entre la consigna y la implementación

| Consigna | Implementación realizada |
|----------|---------------------------|
| mostrar_contenido.php | Panel Administrativo con accesos a Productos, Categorías, Bodegas, Usuarios, Reportes, Seguridad y Gestión de Pedidos. |
| Botonera "Realizar pedido" | Acceso directo al Catálogo para iniciar una compra. |
| Botonera "Ver pedidos" | Vista de Pedidos Activos dentro del Panel Administrativo. |
| Botonera "Finalizar pedidos" | Vista de Pedidos Finalizados (Entregados). |
| realizar_pedidos.php | Flujo profesional de compra: Catálogo → Carrito → Checkout. |
| cargar_pedido.php | Implementado mediante procesar-checkout.php, donde se validan los datos, se crea el pedido y se registran los productos comprados. |
| Estado inicial | Todos los pedidos se crean automáticamente con estado **Procesando**, tal como solicita la consigna. |
| verpedidos.php | Implementado mediante admin/pedidos.php mostrando pedidos activos y permitiendo acceder al detalle de cada uno. |
| finalizarpedidos.php | Implementado mediante la vista "Pedidos Finalizados", donde se muestran los pedidos cuyo estado fue actualizado a **Entregado**. |
| Botón Finalizar | El administrador puede modificar el estado del pedido desde el detalle del mismo hasta marcarlo como **Entregado**. |
| Verificar sesión | Todas las secciones administrativas requieren autenticación y permisos de administrador. |
| Manejo de imágenes | Implementado en el módulo de Productos y reutilizado automáticamente en catálogo, detalle del producto, carrito y pedidos. |

---

# Mejoras incorporadas

Además de cumplir la consigna, el proyecto incorpora funcionalidades adicionales propias de un e-commerce moderno.

- Sistema completo de autenticación.
- Roles de usuario.
- Carrito persistente.
- Checkout profesional.
- Validación automática de stock.
- Descuento automático del stock al confirmar la compra.
- Restauración automática del stock al cancelar pedidos.
- Reportes administrativos.
- Auditoría de acciones.
- Seguridad avanzada.
- Variables de entorno.
- Preparado para autenticación de dos factores (2FA).

---

# Manejo de archivos e imágenes

La consigna solicita incorporar manejo de archivos e imágenes.

Esta funcionalidad fue implementada en el módulo de Productos, donde el administrador puede asociar una imagen a cada vino del catálogo.

Las imágenes son reutilizadas automáticamente en:

- Catálogo.
- Detalle del producto.
- Carrito.
- Checkout.
- Detalle del pedido.

De esta forma el manejo de archivos forma parte del funcionamiento general del sistema.

---

# Instalación

1. Clonar el repositorio.

2. Copiar el proyecto dentro de la carpeta htdocs de XAMPP.

3. Crear una base de datos MySQL.

4. Importar el archivo SQL incluido.

5. Crear un archivo `.env` utilizando `.env.example`.

6. Configurar los datos de conexión a la base de datos.

7. Iniciar Apache y MySQL.

8. Acceder al proyecto desde:

```
http://localhost/proyecto_cava_Noble
```

---

# Credenciales de acceso

## Administrador (solicitado por la consigna)

Usuario:

```
admin
```

Contraseña:

```
admin123
```

Administrador adicional de desarrollo:

Usuario:

```
ignaalcaniz@gmail.com
```

Contraseña:

```
Secu2015$
```

---

# Mejoras futuras

- Integración con Mercado Pago.
- Envío automático de correos electrónicos.
- Dashboard con métricas en tiempo real.
- API REST.
- Docker.
- Redis.
- AWS.
- Integración continua (CI/CD).
- Autenticación de dos factores (2FA).

---

# Autor

**Ignacio Alcañiz**

Proyecto desarrollado para la Diplomatura en PHP y MySQL.

2026.