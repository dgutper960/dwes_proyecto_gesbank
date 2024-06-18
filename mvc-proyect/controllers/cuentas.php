<?php

class Cuentas extends Controller
{

    # Método render
    # Principal del controlador Cuentas
    # Muestra los detalles de la tabla Cuentas
    function render($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['main']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            # Si existe un mensaje, lo mostramos
            if (isset($_SESSION['mensaje'])) {
                // Añadimos a la vista el mensaje
                $this->view->mensaje = $_SESSION['mensaje'];
                // Destruimos el mensaje
                unset($_SESSION['mensaje']);
            }
            $this->view->title = "Tabla Cuentas";
            $this->view->cuentas = $this->model->get();
            $this->view->render("cuentas/main/index");
        }
    }

    # Método nuevo
    # Permite mostrar un formulario que permita añadir una nueva cuenta
    function nuevo($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['new']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            # Creamos un objeto vacío
            $this->view->cuenta = new classCuenta();

            # Comprobamos si la variable de sesion 'errores' no esta vacia
            if (isset($_SESSION['mensaje'])) {
                // Se añade la propiedad mensaje a la vista con el valor de la variable de sesión
                $this->view->mensaje = $_SESSION['mensaje'];

                // Para el autorrelleno del formulario se requiere deserializar (string -> objeto)
                $this->view->cuenta = unserialize($_SESSION['cuenta']);

                // Creamos en la vista la propiedad errores y cargamos los valores de l variable de sesion
                $this->view->errores = $_SESSION['errores'];

                // Liberamos las variables de sesión para evitar bucles
                unset($_SESSION['mensaje']);
                unset($_SESSION['errores']);
                unset($_SESSION['cuenta']);
            }

            // Añadimos a la vista la propiedad title
            $this->view->title = "Formulario añadir cuenta";

            // Para generar la lista select dinámica de clientes
            $this->view->clientes = $this->model->getClientes();

            // Cargamos la vista del formulario para añadir una nueva cuenta
            $this->view->render("cuentas/nuevo/index");
        }
    }
    # Método create
    # Envía los detalles para crear una nueva cuenta
    function create($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['new']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {
            # Saneamos los datos del formulario para evitar inyecciones de código
            $num_cuenta = filter_var($_POST['num_cuenta'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_cliente = filter_var($_POST['id_cliente'] ??= '', FILTER_SANITIZE_NUMBER_INT);
            $fecha_alta = filter_var($_POST['fecha_alta'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $saldo = filter_var($_POST['saldo'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);

            # Creamos el ojeto a partir de los datos saneados
            $cuenta = new classCuenta(
                null,
                $num_cuenta,
                $id_cliente,
                $fecha_alta,
                date("d-m-Y H:i:s"), // Se debe indicar el formato de fecha
                0,
                $saldo,
                null,
                null
            );

            # Validación
            $errores = [];

            // Número Cuenta. 
            //->Campo obligatorio
            //-> Tamaño de 20 dígitos númericos
            //-> Valor único en la BBDD

            // Definimos la expresión regular para el núm de cuenta
            $optionsNumCuenta = [
                'options' => [
                    'regexp' => '/^[0-9]{20}$/'
                ]
            ];
            if (empty($num_cuenta)) {
                $errores['num_cuenta'] = 'Campo obligatorio';
            } else if (!filter_var($num_cuenta, FILTER_VALIDATE_REGEXP, $optionsNumCuenta)) {
                $errores['num_cuenta'] = 'Se requieren 20 caracteres númericos';
            } else if (!$this->model->validateUniqueCuenta($num_cuenta)) { // si el valor no existe, retorna true
                $errores['num_cuenta'] = "Número de cuenta existente, fue registrado previamente";
            }

            // Cliente. 
            //-> Campo obligatorio
            //-> Valor numérico
            //-> El registro debe existir en la tabla de clientes
            if (empty($id_cliente)) {
                $errores['id_cliente'] = 'Campo obligatorio, debe seleccionar un cliente';
            } else if (!filter_var($id_cliente, FILTER_VALIDATE_INT)) {
                $errores['id_cliente'] = 'Requerido valor númerico para este campo';
            } else if (!$this->model->validateClient($id_cliente)) { // si el valor no existe, retorna true
                $errores['id_cliente'] = 'El cliente indicado no existe, deje de piratear la web por favor';
            }


            # Comprobamos la validación
            if (!empty($errores)) {
                // Si existen errores de validación, entramos en este bloque
                $_SESSION['cuenta'] = serialize($cuenta);
                $_SESSION['mensaje'] = 'El formulario no fue validado, revise los campos marcados en rojo';
                $_SESSION['errores'] = $errores;

                // Redireccionamos de nuevo al formulario
                header('location:' . URL . 'cuentas/nuevo/index');
            } else {
                # Añadimos el registro a la tabla
                $this->model->create($cuenta);

                // mensaje de feedback al usuario
                $_SESSION['mensaje'] = "La nueva cuenta fue creada con éxito.";

                // Redireccionamos a la vista principal de cuentas
                header("Location:" . URL . "cuentas");
            }
        }
    }

    # Método delete
    # Permite eliminar una cuenta de la tabla
    function delete($param = [])
    {

        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['delete']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "cuentas");

        } else {
            $id = $param[0];
            $this->model->delete($id);
            $_SESSION['mensaje'] = "Cuenta eliminada con éxito";
            header("Location:" . URL . "cuentas");
        }
    }

    # Método editar
    # Muestra los detalles de una cuenta en un formulario de edición
    # Sólo se podrá modificar el titular o cliente de la cuenta
    function editar($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['edit']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {
            # Obtengo el id de la cuenta a editar
            $id = $param[0];

            # Asignamos dicho id a una propiedad de la vista
            $this->view->id = $id;

            # Comprobamos si el formulario viene de una no validación
            if (isset($_SESSION['mensaje'])) {
                // Añadimos a la vista en el mensaje de mensaje
                $this->view->mensaje = $_SESSION['mensaje'];

                // Autorellenamos el formulario
                $this->view->cuenta = unserialize($_SESSION['cuenta']);

                // Recuperamos el array con los errores
                $this->view->errores = $_SESSION['errores'];

                // Una vez usadas las variables de sesión, las liberamos
                unset($_SESSION['mensaje']);
                unset($_SESSION['errores']);
                unset($_SESSION['cuenta']);

            }

            // Añadimos a la propiedad de la vista title un texto
            $this->view->title = "Formulario editar cuenta";

            // Añadimos a la vista las siguientes propiedades:
            $this->view->clientes = $this->model->getClientes();
            $this->view->cuenta = $this->model->getCuenta($id);

            // Cargamos la vista de editar la cuenta
            $this->view->render("cuentas/editar/index");
        }
    }

    # Método update
    # Envía los detalles modificados de una cuenta para su actualización en la tabla
    function update($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['edit']))) {

            $_SESSION['mensaje'] = "EL usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            # Saneamos los datos del formulario
            $num_cuenta = filter_var($_POST['num_cuenta'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_cliente = filter_var($_POST['id_cliente'] ??= '', FILTER_SANITIZE_NUMBER_INT);
            $num_movimientos = filter_var($_POST['num_movtos'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $fechaUltMovimiento = filter_var($_POST['fecha_ul_mov'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $fecha_alta = filter_var($_POST['fecha_alta'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $saldo = filter_var($_POST['saldo'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);

            # Creamos el objeto a partir de los datos saneados
            $cuenta = new classCuenta(
                null,
                $num_cuenta,
                $id_cliente,
                $fecha_alta,
                $fechaUltMovimiento,
                $num_movimientos,
                $saldo,
                null,
                null
            );

            // Cargamos el id de la cuenta a actualizar
            $id = $param[0];

            # Obtenemos el objeto objeto de la clase classCuenta
            $objetOriginal = $this->model->getCuenta($id);


            # Validación
            // Solo si es necesario y en caso de modificación del campo
            $errores = [];

            // Número Cuenta. 
            //-> Campo obligatorio
            //-> Tamaño de 20 dígitos númericos
            //-> Valor único en la BBDD

            // Definimos la expresión regular para el núm de cuenta
            // Validar el numero de cuenta
            if (strcmp($num_cuenta, $objetOriginal->num_cuenta) !== 0) {
                $cuenta_regexp = [
                    'options' => [
                        'regexp' => '/^[0-9]{20}$/'
                    ]
                ];
                if (empty($num_cuenta)) {
                    $errores['num_cuenta'] = 'Campo Obligatorio, añada un número de cuenta';
                } else if (!filter_var($num_cuenta, FILTER_VALIDATE_REGEXP, $cuenta_regexp)) {
                    $errores['num_cuenta'] = 'Formato no valido, deben ser 20 caracteres númericos';
                } else if (!$this->model->validateUniqueCuenta($num_cuenta)) {
                    $errores['num_cuenta'] = "El número de cuenta ya está registrado";
                }
            }

            // Cliente. 
            //-> Campo obligatorio
            //-> Valor numérico
            //-> El registro debe existir en la tabla de clientes
            if (strcmp($id_cliente, $objetOriginal->id_cliente) !== 0) {
                if (empty($id_cliente)) {
                    $errores['id_cliente'] = 'Campo Obligatorio, seleccione un cliente';
                } else if (!filter_var($id_cliente, FILTER_VALIDATE_INT)) {
                    $errores['id_cliente'] = 'Deberá introducir un valor númerico en este campo';
                } else if (!$this->model->validateClient($id_cliente)) {
                    $errores['id_cliente'] = 'No existe el cliente indicado';
                }
            }


            # Comprobamos validación
            if (!empty($errores)) {
                // errores de validación
                $_SESSION['cuenta'] = serialize($cuenta);
                $_SESSION['mensaje'] = 'Formulario no validado';
                $_SESSION['errores'] = $errores;

                // Redireccionamos
                header('location:' . URL . 'cuentas/editar/' . $id);
            } else {
                // Actualizamos el registro de la base de datos
                $this->model->update($cuenta, $id);

                // Creamos el mensaje personalizado
                $_SESSION['mensaje'] = 'Cuenta actualizada con éxito';

                // Redireccionamos a la vista principal de cuentas
                header("Location:" . URL . "cuentas");
            }

        }
    }


    # Método mostrar
    # Muestra los detalles de una cuenta en un formulario no editable
    function mostrar($param = [])
    {

        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['show']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'cuentas');
        } else {
            # id de la cuenta
            $id = $param[0];

            $this->view->title = "Formulario Cuenta Mostar";
            $this->view->cuenta = $this->model->getCuenta($id);
            $this->view->cliente = $this->model->getCliente($this->view->cuenta->id_cliente);


            $this->view->render("cuentas/mostrar/index");
        }
    }

    # Método ordenar
    # Permite ordenar la tabla cuenta a partir de alguna de las columnas de la tabla
    function ordenar($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['order']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'cuentas');
        } else {
            $criterio = $param[0];
            $this->view->title = "Tabla Cuentas";
            $this->view->cuentas = $this->model->order($criterio);
            $this->view->render("cuentas/main/index");

        }
    }

    # Método buscar
    # Permite realizar una búsqueda en la tabla cuentas a partir de una expresión
    function buscar($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Compruebo ususario autentificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "EL usuario debe autenticarse";

            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['filter']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'cuentas');
        } else {
            $expresion = $_GET["expresion"];
            $this->view->title = "Tabla Cuentas";
            $this->view->cuentas = $this->model->filter($expresion);
            $this->view->render("cuentas/main/index");
        }
    }

    /**
     * Funciones para exportar e importar cvs
     */
    // Exportar
    public function exportar()
    {

        // continuamos sesión
        session_start();

        // comprobamos autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            // te vas pa tu casa chaval
            header("location:" . URL . "login");
            // comprobamos si existen trivilegios para la accion
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['exportar']))) {
            $_SESSION['mensaje'] = "El usuario no dispone de privilegios para esta acción";
            header("location:" . URL . "cuentas");
        }

        // cargamos los datos para el cvs (retorna un array asociativo)
        $cuentas = $this->model->getCSV();

        // Preparamos las cabeceras
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="cuentas.csv"');
        // Abrimos el archivo para escribir la exportacion
        $exportFile = fopen('php://output', 'w');
        // Recorremos el fecht de clientes y vamos añadiendo los valores de los compos para cada cliente
        foreach ($cuentas as $cuenta) {

            $cuenta = [

                'num_cuenta' => $cuenta['num_cuenta'],
                'id_cliente' => $cuenta['id_cliente'],
                'fecha_alta' => $cuenta['fecha_alta'],
                'fecha_ul_mov' => $cuenta['fecha_ul_mov'],
                'num_movtos' => $cuenta['num_movtos'],
                'saldo' => $cuenta['saldo'],
                'create_at' => $cuenta['create_at'],
                'update_at' => $cuenta['update_at']

            ];

            // Añadimos un cuenta en cada fila, seleccionamos el separador
            fputcsv($exportFile, $cuenta, ';');
        }

        // cerramos el fichero
        fclose($exportFile);

    }

        // Importar
        public function importar()
        {
            // continuamos sesión
            session_start();
    
            // comprobamos autenticación
            if (!isset($_SESSION['id'])) {
                $_SESSION['mensaje'] = "El usuario debe autenticarse";
                // te vas pa tu casa chaval
                header("location:" . URL . "login");
                // comprobamos si existen trivilegios para la accion
            } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['importar']))) {
                $_SESSION['mensaje'] = "El usuario no dispone de privilegios para esta acción";
                header("location:" . URL . "cuentas");
            }
    
            // Verificamos la solicitud tipo POST, Si el archivo se ha subido sin errores.
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_csv"]) && $_FILES["archivo_csv"]["error"] == UPLOAD_ERR_OK) {
                // Obtenemos la ruta temporal del archivo subido.
                $file = $_FILES["archivo_csv"]["tmp_name"];
    
                // Verificamos la extensión del archivo
                $fileExtension = pathinfo($_FILES["archivo_csv"]["name"], PATHINFO_EXTENSION);
                if ($fileExtension != 'csv') {
                    $_SESSION['error'] = "El archivo debe ser un CSV";
                    header('location:' . URL . 'cuentas');
                    exit();
                }
    
                // Verificamos el formato del archivo CSV
                $handle = fopen($file, "r");
                if ($handle === FALSE) {
                    $_SESSION['error'] = "Error al abrir el archivo CSV";
                    header('location:' . URL . 'cuentas');
                    exit();
                }
    
                // Abrimos el archivo en modo lectura.
                $handle = fopen($file, "r");
    
                // Verificamos que no hay errores.
                if ($handle !== FALSE) {
                    // Recorremos el archivo mientras haya líneas y las asignamos a $data = array donde cada indice es un campo del CSV.
                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                        // Asignamos cada indice a una variable.
                        // sanitizamos los datos antes igualar
                        $num_cuenta = filter_var($data[0], FILTER_SANITIZE_SPECIAL_CHARS);
                        $id_cliente = filter_var($data[1], FILTER_SANITIZE_SPECIAL_CHARS);
                        $fecha_alta = filter_var($data[2], FILTER_SANITIZE_SPECIAL_CHARS);
                        $fecha_ul_mov = filter_var($data[3], FILTER_SANITIZE_SPECIAL_CHARS);
                        $num_movtos = filter_var($data[4], FILTER_SANITIZE_SPECIAL_CHARS);
                        $saldo = filter_var($data[5], FILTER_SANITIZE_SPECIAL_CHARS);
                        $create_at = filter_var($data[5], FILTER_SANITIZE_SPECIAL_CHARS);
                        $update_at = filter_var($data[5], FILTER_SANITIZE_SPECIAL_CHARS);
    
                        // Verificamos que el num de cuenta no existe y que el cliemte exista
                        if ($this->model->validateUniqueCuenta($num_cuenta) && $this->model->validateClient($id_cliente)) {
                            // Instanciamos objeto classCuenta
                            $cuenta = new classCuenta();
                            // Asignamos los valores extraidos del CSV al nuevo objeto
                            $cuenta->num_cuenta =   $num_cuenta;
                            $cuenta->id_cliente =   $id_cliente;
                            $cuenta->fecha_alta =   $fecha_alta;
                            $cuenta->fecha_ul_mov = $fecha_ul_mov;
                            $cuenta->num_movtos =   $num_movtos;
                            $cuenta->saldo =        $saldo;
                            $cuenta->create_at =    $create_at;
                            $cuenta->update_at =    $update_at;
    
                            // Insertamos la nueva cuenta a la tabla.
                            $this->model->create($cuenta);
                        } else {
                            // En caso de que uno de los métodos de validación retorne FALSE.
                            $_SESSION['mensaje'] = "Operación cancelada. La cuenta ya existe";
                        }
                    }
    
                    // Cerramos el archivo.
                    fclose($handle);
        
                    // Feedback de éxito.
                    $_SESSION['mensaje'] = "Importación realizada correctamente";
                    header('location:' . URL . 'cuentas');
                    exit();
                } else {
                    // Establece un mensaje de error en la sesión si el archivo no se pudo abrir.
                    $_SESSION['error'] = "Error con el archivo CSV";
                    // Redirige al usuario a la página de clientes.
                    header('location:' . URL . 'cuentas');
                    exit();
                }
            } else {
                // Establece un mensaje de error en la sesión si no se seleccionó un archivo CSV.
                $_SESSION['error'] = "Seleccione un archivo CSV";
                // Redirige al usuario a la página de clientes.
                header('location:' . URL . 'cuentas');
                exit();
            }
    
        }

        function pdf()
        {
            session_start();
    
            if (!isset($_SESSION['id'])) {
                $_SESSION['mensaje'] = "Usuario no autentificado";
                header("location:" . URL . "login");
                exit();
            } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['pdf']))) {
                $_SESSION['mensaje'] = "Operación sin privilegios";
                header('location:' . URL . 'cuentas');
                exit();
            }
    
            // Obtenemos el fetch de clientes
            $cuentas = $this->model->get();
    
            // Instanciamos objeto de pdfClientes -> extiende de FPDF
            $pdf = new pdfCuentas();
    
            // Invocamos contenido (a su vez invoca al resto de métodos)
            $pdf->contenido($cuentas);
    
            // Cerramos 
            $pdf->Output();
        }

    // Carga en la vista los movimientos de la cuenta
    function movimientos($param = [])
    {
        //Iniciar o continuar sesión
        session_start();

        # id de la cuenta
        $id = $param[0];

        // Comprobar identificación usuario
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['cuentas']['movimientos']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'cuentas');

        } else {

            $this->view->title = "Movimientos de la Cuenta";
            $this->view->movimientos = $this->model->getMovientosCuenta($id);

            $this->view->render("cuentas/movimientos/index");
        }
    }
    
}


