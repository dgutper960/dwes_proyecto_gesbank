<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("template/partials/head.php"); ?>
    <title>Editar Cuenta - GESBANK</title>

</head>

<body>
    <!-- menu principal fijo superior -->
    <?php require_once "template/partials/menuAut.php"; ?>
    <!-- capa principal -->
    <div class="container">
        <!-- cabecera o título -->
        <?php include "views/clientes/partials/header.php" ?>
        <!-- Mostramos el Mensaje en caso de error -->
        <?php include("template/partials/error.php") ?>

        <form action="<?= URL ?>cuentas/update/<?= $this->id ?>" method="POST">

            <!-- Número de cuenta -->
            <div class="mb-3">
                <label for="" class="form-label">Numero de cuenta</label>
                <input type="text" class="form-control" name="num_cuenta" value="<?= $this->cuenta->num_cuenta ?>">
                <!-- Si en el array de errores existe un índice con este cámpo cambiamos el estilo y mostramos el mensaje -->
                <?php if (isset($this->errores['num_cuenta'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['num_cuenta'] ?>
                    </span>
                <?php endif; ?>
            </div>
            <!-- Cliente -->
            <div class="mb-3">
                <label for="" class="form-label">Cliente</label>
                <select class="form-select <?= (isset($this->errores['id_cliente'])) ? 'is-invalid' : null ?>"
                    name="id_cliente" id="">
                    <option selected disabled>Seleccione un cliente </option>
                    <?php foreach ($this->clientes as $cliente): ?>
                        <div class="form-check">
                            <option value="<?= $cliente->id ?>" <?= ($this->cuenta->id_cliente == $cliente->id) ? "selected" : null; ?>>
                                <?= $cliente->cliente ?>
                            </option>
                        </div>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->errores['id_cliente'])): ?>
                    <span class="form-text text-danger" role="alert">
                        <?= $this->errores['id_cliente'] ?>
                    </span>
                <?php endif; ?>
            </div>
            <!-- Fecha -->
            <div class="mb-3">
                <label for="" class="form-label">Fecha alta</label>
                <input type="datetime-local"
                    class="form-control <?= (isset($this->errores['fecha_alta'])) ? 'is-invalid' : null ?>"
                    name="fecha_alta" value="<?= $this->cuenta->fecha_alta ?>">
            </div>

            <!-- Número de movimientos -->
            <div class="mb-3">
                <label for="" class="form-label">Número de Movimientos</label>
                <input type="number" class="form-control" name="num_movtos" id=""
                    value="<?= $this->cuenta->num_movtos ?>">
            </div>
            <!-- saldo  -->
            <div class="mb-3">
                <label for="" class="form-label">Saldo</label>
                <input type="number" class="form-control" name="saldo" id="" step="0.01"
                    value="<?= $this->cuenta->saldo ?>">
            </div>
            <!-- botones de acción -->
            <div class="mb-3">
                <a name="" id="" class="btn btn-secondary" href="<?= URL ?>cuentas" role="button">Cancelar</a>
                <button type="button" class="btn btn-danger">Borrar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>

            </div>
        </form>
    </div>

    <br><br><br>

    <!-- footer -->
    <?php require_once "template/partials/footer.php" ?>


    <!-- Bootstrap JS y popper -->
    <?php require_once "template/partials/javascript.php" ?>
</body>

</html>