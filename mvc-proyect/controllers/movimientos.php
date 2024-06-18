<?php

class Movimientos extends Controller
{
    // Genera la vista principal
    public function render($param = [])
    {

        # Inicio o continúo la sesión
        session_start();

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            // Si no hay variable se sesión id, redireccionamos a login
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");

        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['main']))) {
            // Si no hay privilegios
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');

        } else {

            # Comprobar si existe el mensaje
            if (isset($_SESSION['mensaje'])) {
                $this->view->mensaje = $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            }

            # Creo la propiedad title de la vista
            $this->view->title = "Panel Movimientos - GesBank";

            # Creo la propiedad movimientos dentro de la vista
            # Del modelo asignado al controlador ejecuto el método getAllMovimientos();
            $this->view->movimientos = $this->model->getAllMovimientos();
            // Mostramos el panel de movimientos
            $this->view->render("movimientos/main/index");
        }
    }

    // Método para Nuevo Movimiento
    function nuevo($param = [])
    {
        # Iniciamos o continuamos la sesión
        session_start();

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            // Si no hay variable se sesión id, redireccionamos a login
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");

        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['nuevo']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');

        } else {

            # Creamos un objeto vacío
            $this->view->movimiento = new classMovimiento();

            # Comprobamos si existen errores
            if (isset($_SESSION['error'])) {
                // Si existen errores, se añaden a la propiedad error de la vista
                $this->view->error = $_SESSION['error'];

                // Autorrellenamos el formulario
                $this->view->movimiento = unserialize($_SESSION['movimiento']);

                // Recuperamos los errores de la variable de sesión
                $this->view->errores = $_SESSION['errores'];

                // Liberamos las variables de sesión (evita bucle infinito)
                unset($_SESSION['error']);
                unset($_SESSION['errores']);
                unset($_SESSION['movimientos']);
            }

            // Añadimos el título a la vista
            $this->view->title = "Panel Movimientos - GesBank";
            // Cargamos las cuentas (selección dinámica de cuentas).
            $this->view->cuentas = $this->model->getAllCuentas();
            // Mostramos el formulario de nueva cuenta
            $this->view->render("movimientos/nuevo/index");
        }
    }

    # Método create
    # Envía los detalles para crear una nuevo movimiento
    function create($param = [])
    {
        // Iniciar sesión
        session_start();

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['nuevo']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');
        } else {

            // 1. Seguridad. Saneamos los datos del formulario

            // ??='' -> asignación de coalescencia nula
            $cuenta = filter_var($_POST['cuenta'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $fecha_hora = filter_var($_POST['fecha_hora'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $concepto = filter_var($_POST['concepto'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $tipo = filter_var($_POST['tipo'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $cantidad = filter_var($_POST['cantidad'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $saldo = $this->model->getSaldoCuenta($cuenta);

            // 2. Creamos Movimiento con los datos saneados
            //Cargamos los datos del formulario
            $movimiento = new classMovimiento(
                null,
                $cuenta,
                $fecha_hora,
                $concepto,
                $tipo,
                $cantidad,
                $saldo
            );

            # 3. Validación
            // Inicilizamos variable para cargar errores de validación (si los hay).
            $errores = [];

            // Fecha_hora
            //-> No obligatorio.
            //-> Fecha hora actual por defecto
            if (!isset($fecha_hora) || $fecha_hora == '0000-00-00 00:00') {
                $fecha_hora = date('Y-m-d\TH:i');
            }

            // Concepto
            //-> Valor obligatorio
            //-> Máximo 50 caracteres
            if (empty($concepto)) {
                $errores['concepto'] = 'Campo obligatorio';
            } else if (strlen($concepto) > 50) {
                $errores['concepto'] = 'El tamaño debe ser menor a 50 caracteres';
            }

            // Tipo - I o R (ingreso o reintegro)
            //-> Valor obligatorio.
            if (empty($tipo)) {
                $errores['tipo'] = 'Debe seleccionar I o R (I = ingreso / R = reintegro)';
            } else if (!in_array($tipo, ['I', 'R'])) {
                $errores['tipo'] = 'El campo tipo debe ser I o R';
            }

            // Cantidad
            //-> Valor tipo float. 
            /*
            Si el movimiento es de tipo = R:
                - La cantidad no podrá superar el saldo de la cuenta, 
                  en caso contrario, mostrará mensaje cantidad no disponible. 
                - La cantidad se almacenará con un número negativo, de esta forma, 
                  sumando todas las cantidades de los movimientos de una misma cuenta podré obtener el saldo.
             */
            if (empty($cantidad)) {
                $errores['cantidad'] = 'El campo es obligatorio';
            } else if (!is_numeric($cantidad)) {
                $errores['cantidad'] = 'El campo debe ser un valor numérico';
            } else {
                
                // Reintegro siempre menor o igual a saldo
                if ($tipo == 'R' && $cantidad > $saldo) {
                    $errores['cantidad'] = 'Cantidad no disponible, es superior al saldo de la cuenta';
                }
            }
            

            # 4. Comprobar validación
            if (!empty($errores)) {
                // Si existen errores de validación
                $_SESSION['movimiento'] = serialize($movimiento); // Autorrelleno
                $_SESSION['error'] = 'Formulario no validado'; // Mensaje
                $_SESSION['errores'] = $errores; // Cargamos errores en la sesión

                //Redireccionamos de nuevo al formulario
                header('location:' . URL . 'movimientos/nuevo/index');

            } else { // Bloque para accualizar el saldo del nuevo movimiento

                // Si Tipo = I
                if ($tipo == 'I') {
                    $saldo += $cantidad;
                }
                // Si Tipo = R
                else {
                    $saldo -= $cantidad;
                }

                //Actualizamos el saldo en el objeto movimiento
                $movimiento->saldo = $saldo;

                # Añadimos el registro a la tabla
                $this->model->create($movimiento);

                // Actualizamos el saldo de la cuenta en la BBDD
                $this->model->updateSaldoCuenta($cuenta, $saldo);

                // Mensaje FeedBack al usuario
                $_SESSION['mensaje'] = "Movimiento realizado con éxito.";

                // Redireccionamos a la vista principal de movimientos
                header("Location:" . URL . "cuentas");
            }
        }
    }

    # Método mostrar
    # Muestra los detalles de un movimiento (valores disabled)
    function mostrar($param = [])
    {

        //Iniciar o continuar sesión
        session_start();

        # id de la cuenta
        $id = $param[0];

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");

        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['mostrar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');

        } else {

            $this->view->title = "Panel Movimientos - GesBank";
            $this->view->movimiento = $this->model->getMovimiento($id);
            $this->view->cuenta = $this->model->getCuenta($this->view->movimiento->cuenta);

            $this->view->render("movimientos/mostrar/index");
        }
    }

    # Método ordenar
    # Permite ordenar la tabla cuenta a partir de alguna de las columnas de la tabla
    function ordenar($param = [])
    {
        //Inicio o continuo sesión
        session_start();

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['ordenar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');
        } else {

            $criterio = $param[0];
            $this->view->title = "Panel Movimientos - GesBank";
            $this->view->movimientos = $this->model->order($criterio);
            $this->view->render("cuentas/movimientos/index");
        }
    }

    # Método buscar
    # Permite realizar una búsqueda en la tabla cuentas a partir de una expresión
    function buscar($param = [])
    {
        //Inicio o continuo sesión
        session_start();

        // Comprobamos Autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['movimientos']['buscar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'movimientos');
        } else {


            $expresion = $_GET["expresion"];
            $this->view->title = "Panel Movimientos - GesBank";
            $this->view->movimientos = $this->model->filter($expresion);
            $this->view->render("movimientos/main/index");
        }
    }
}
