<!DOCTYPE html>
<html lang="es">

<head>
    <!-- bootstrap  -->
    <?php require_once("template/partials/head.php"); ?>
    <title>Detalles de Usuario - Gesbank</title>
</head>

<body>
    <!-- menu fijo superior -->
    <?php require_once "template/partials/menuAut.php"; ?>
    <br><br><br>

    <!-- capa principal -->
    <div class="container">
        <!-- cabecera -->
        <?php include "views/usuarios/partials/header.php" ?>

        <legend>    Editando Usuario</legend>

        <!-- Mensaje de Error -->
        <?php include 'template/partials/error.php' ?>

        <!-- Formulario -->
        <form action="<?= URL . 'usuarios/update/' . $this->usuario->id ?>"
            method="POST">

            <!-- Nombre -->
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre"
                    value="<?= isset($this->usuario->name) ? $this->usuario->name : (isset($_SESSION['usuario']) ? $_SESSION['usuario']->name : '') ?>">

                <!-- Mostrar posible error -->
                <?php if (isset($this->errores['nombre'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['nombre'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email"
                    value="<?= isset($this->usuario->email) ? $this->usuario->email : (isset($_SESSION['usuario']) ? $_SESSION['usuario']->email : '') ?>">

                <!-- Mostrar posible error -->
                <?php if (isset($this->errores['email'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['email'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Rol-->
            <div class="mb-3">
                <label for="rol" class="form-label">Seleccionar Rol</label>
                <select class="form-select" name="rol">
                    <?php foreach ($this->roles as $rol): ?>
                        <option value="<?= $rol->id ?>" <?= ($rol->id == $this->usuario->role_id) ? 'selected' : '' ?>>
                            <?= $rol->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <input type="password"
                    class="form-control <?= (isset($this->errores['password'])) ? 'is-invalid' : null ?>"
                    name="password">

                <!-- Mostrar posible error -->
                <?php if (isset($this->errores['password'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['password'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                <input type="password"
                    class="form-control <?= (isset($this->errores['password_confirm'])) ? 'is-invalid' : null ?>"
                    name="password_confirm">

                <!-- Mostrar posible error -->
                <?php if (isset($this->errores['password_confirm'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['password_confirm'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Botón de guardar cambios -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="<?= URL ?>usuarios" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- footer -->
    <?php require_once "template/partials/footer.php" ?>

    <!-- Bootstrap JS y popper -->
    <?php require_once "template/partials/javascript.php" ?>
</body>

</html>