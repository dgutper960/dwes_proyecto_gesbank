# Gestión Bancaria - Proyecto 2DAW

Este proyecto es el trabajo realizado durante el curso 2DAW en el módulo de Desarrollo Web en Entorno Cliente. La aplicación web está desarrollada completamente en PHP sin uso de frameworks, utilizando estilos de Bootstrap y siguiendo la arquitectura MVC (Modelo-Vista-Controlador).

## Descripción

La aplicación consiste en la gestión de un banco, permitiendo el control de usuarios, roles y permisos, así como la gestión completa de clientes, cuentas y movimientos bancarios. Además, incluye funciones de autenticación, registro, y envío automático de correos electrónicos para diversas acciones.

## Características

- **PHP:** El lenguaje principal utilizado para el desarrollo del backend.
- **Bootstrap:** Utilizado para el diseño y los estilos del frontend, proporcionando una interfaz moderna y responsive.
- **MVC:** Arquitectura utilizada para separar la lógica de negocio, la presentación y la manipulación de datos.
- **Control de Usuarios, Roles y Permisos:** Mediante variables de sesión.
- **CRUD Completo:** Para clientes, cuentas y movimientos.
- **Validación y Saneamiento:** Validación del lado del servidor y saneamiento de datos del formulario.
- **Generación de Archivos:** Genera, descarga e importa CSV de clientes y cuentas, y genera y descarga PDF de clientes y cuentas.
- **Autenticación y Registro:** Gestión de perfiles de usuario.
- **Envío Automático de Emails:** Para registro de nuevos usuarios, cambios de perfil o credenciales, y baja de usuarios.

## Funcionalidades

### Control de Usuarios, Roles y Permisos
- Autenticación y registro de usuarios.
- Gestión de roles y permisos mediante variables de sesión.
- Gestión de perfiles de usuario.

### CRUD de Clientes
- Crear nuevo cliente.
- Mostrar clientes.
- Editar información de clientes.
- Borrar clientes.
- Filtrar y ordenar clientes.

### CRUD de Cuentas
- Crear nueva cuenta.
- Mostrar cuentas.
- Editar información de cuentas.
- Borrar cuentas.
- Filtrar y ordenar cuentas.

### CRUD de Movimientos
- Crear nuevo movimiento.
- Mostrar movimientos.
- Editar información de movimientos.
- Borrar movimientos.
- Filtrar y ordenar movimientos.

### Generación y Descarga de Archivos
- Generar y descargar CSV de clientes y cuentas.
- Importar CSV de clientes y cuentas.
- Generar y descargar PDF de clientes y cuentas.

### Envío Automático de Emails
- Envío de email para registro de nuevos usuarios.
- Envío de email para cambios de perfil o credenciales de usuario.
- Envío de email cuando un usuario es dado de baja.
