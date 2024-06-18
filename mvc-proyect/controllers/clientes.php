<?php

class Clientes extends Controller
{

    # Método principal. Muestra todos los clientes
    public function render($param = [])
    {
        # Se debe continuar la sesión para mantener los posibles datos almacenados
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['main']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            # Si exsiste la variable de sesión mesage, lo mostramos
            if (isset($_SESSION['mensaje'])) {
                $this->view->mensaje = $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            }
            # Creamos la propiedad title de la vista
            $this->view->title = "Panel de Clientes - GesBank";

            # Añadimos a la propiedad de la vista "clientes" el resultado del método get(),
            // disponible en el modelo
            $this->view->clientes = $this->model->get();


            # Cargamos la vista principal
            $this->view->render("clientes/main/index");
        }
    }

    # Método nuevo. Muestra formulario añadir cliente
    public function nuevo($param = [])
    {
        # Continuamos sesión
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['new']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {
            # Creamos un objeto vacio
            $this->view->cliente = new classCliente();

            # Si existe la variable de sesión 'errores' es que ha habido algún error
            // en caso afirmativo, creamos las siguientes propiedades:
            if (isset($_SESSION['error'])) {
                // Mostramos el mensaje de error en la vista
                $this->view->error = $_SESSION['error'];

                // Autorrellenamos los datos del formulario -> debemos deserializarlos previamente
                $this->view->cliente = unserialize($_SESSION['cliente']);

                // Recuperamos el array con los errores
                $this->view->errores = $_SESSION['errores'];

                // Una vez echo uso de las varables de sesión, se deben eliminar, para evitar bucles y otros errores
                unset($_SESSION['error']);
                unset($_SESSION['cliente']);
                unset($_SESSION['errores']);


            } // fin del bloque if en caso de existe alguna variable de sesion 'error'

            # Añadimos a la vista la propiedad title
            $this->view->title = "Formulario cliente nuevo";

            # Cargamos la vista del formulario para añadir un nuevo cliente
            $this->view->render("clientes/nuevo/index");
        }
    }

    # Método create. 
    # Permite añadir nuevo cliente a partir de los detalles del formuario
    public function create($param = [])
    {

        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['new']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {
            /**
             * Proceso de validación
             */
            # Saneamos los datos del formulario para evitar la inyección de código
            // (??= '')-> operador de asignación de fusión de null
            $nombre = filter_var($_POST["nombre"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS); // special_chars para los string
            $apellidos = filter_var($_POST["apellidos"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $telefono = filter_var($_POST["telefono"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $ciudad = filter_var($_POST['ciudad'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $dni = filter_var($_POST['dni'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ??= '', FILTER_SANITIZE_EMAIL);

            # Instanciamos el objeto con los datos saneados
            $cliente = new classCliente(
                null,
                $apellidos,
                $nombre,
                $telefono,
                $ciudad,
                $dni,
                $email,
                null,
                null
            );

            # Validación
            // Creamos un array vacío para almacenar los posibles errores de validación
            $errores = [];

            // apellidos. 
            //-> Campo obligatorio
            //-> Tamaño maximo de 45
            if (empty($apellidos)) {
                $errores['apellidos'] = "Campo obligatorio";
            } else if (strlen($apellidos) > 45) { // strlen() evalua el número de caracteres
                $errores['apellidos'] = "El campo admite un máximo de 45 caracteres";
            }

            // nombre. 
            //-> Campo obligatorio
            //-> Tamaño maximo de 20
            if (empty($nombre)) {
                $errores['nombre'] = "Campo obligatorio";
            } else if (strlen($nombre) > 20) {
                $errores['nombre'] = "El campo admite un máximo de 20 caracteres";
            }

            // Teléfono. 
            //-> 9 dígitos numéricos
            // Inicializamos variable para almacenra la expresión regular
            $optionsTel = [
                'options' => [
                    'regexp' => '/^[0-9]{9}$/'
                ]
            ];

            if (!empty($telefono) && !filter_var($telefono, FILTER_VALIDATE_REGEXP, $optionsTel)) {
                $errores['telefono'] = "Este campo debe ser numérico con 9 dígitos";
            }

            // Ciudad. 
            //-> Obligatorio
            //-> Tamaño máximo de 20
            if (empty($ciudad)) {
                $errores['ciudad'] = "Campo obligatorio";
            } else if (strlen($ciudad) > 20) {
                $errores['ciudad'] = "El campo admite un máximo de 20 caracteres";
            }

            // dni. 
            //-> Campo obligatorio
            //-> Formato de 8 digitos y 1 mayúscula
            //-> Valor único en la BBDD
            // Creamos un regexp, que permita 8 digitos y 1 letra mayuscula
            $optionsDNI = [
                'options' => [
                    'regexp' => '/^[0-9]{8}[A-Z]$/'
                ]
            ];

            if (empty($dni)) {
                $errores['dni'] = "Campo obligatorio";
            } else if (!filter_var($dni, FILTER_VALIDATE_REGEXP, $optionsDNI)) {
                $errores['dni'] = "El formato requerido para DNI es: '12345678A'";
            } else if (!$this->model->validateUniqueDni($dni)) { // método que retorna false si el DNI existe
                $errores['dni'] = "El DNI ha sido registrado con anterioridad";
            }

            // email. 
            //-> Campo obligatorio
            //-> Formato valido para email
            //-> Valor único en la BBDD 
            if (empty($email)) {
                $errores['email'] = "Campo obligatorio";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = "Formato email no válido";
            } else if (!$this->model->validateUniqueEmail($email)) { // método que retorna false si el DNI existe
                $errores['email'] = "El email ya ha sido registrado con anterioridad";
            }

            # Comprobamos la validación
            // Si el array de errores no está vacío, es que hemos tenido algún error de validación
            if (!empty($errores)) {
                // Almacenamos los errores en variables de sesión
                $_SESSION['cliente'] = serialize($cliente); // Para el autorrellenado del formulario
                $_SESSION['error'] = 'Formulario no validado';
                $_SESSION['errores'] = $errores;

                // Redireccionamos al formulario nuevo
                header('Location:' . URL . 'clientes/nuevo');
            } else {
                // Si no hay errores, añadimos el registro a la tabla
                $this->model->create($cliente);

                // Creamos el mensaje personalizado
                $_SESSION['mensaje'] = 'Cliente añadido con éxito';

                // Redirigimos a la vista principal de clientes
                header("Location:" . URL . "clientes");
            }

        }
    }
    # Método delete. 
    # Permite la eliminación de un cliente
    public function delete($param = [])
    {
        # Continuamos la sesión
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['delete']))) {

            $_SESSION['mensaje'] = "Acción Restringida. Usuario sin privilegios";
            header("location:" . URL . "clientes");

        } else {

            $id = $param[0];

            /*
                Control para el borrado en cascada (la BBDD no lo tiene)
             */
            $this->model->deleteCuentasCliente($id);

            // Una vez borradas las cuentas del cliente, borramos al cliente
            $this->model->delete($id);
            $_SESSION['mensaje'] = "Cliente eliminado con éxito";
            // vamos al main de clientes
            header("Location:" . URL . "clientes");
        }
    }

    # Método editar. 
    # Muestra un formulario que permita editar los detalles de un cliente
    public function editar($param = [])
    {
        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['edit']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            # Obtenemos el id del cliente a editar
            $id = $param[0];
            $this->view->id = $id;

            # Asignamos un valor a la propiedad de la vista title
            $this->view->title = "Formulario editar cliente";

            # Asignamos a la propiedad de la vista cliente el resultado del método getCliente
            $this->view->cliente = $this->model->getCliente($id);


            # Comprobamos si existen errores
            // en caso de errores el formulario viene de una no validación
            if (isset($_SESSION["error"])) {
                // Añadimos a la vista el mensaje de error
                $this->view->error = $_SESSION["error"];

                // Autorellenamos el formulario
                $this->view->cliente = unserialize($_SESSION['cliente']);  // deserializamos e igulamos a cliente

                // Recuperamos el array con los errores
                $this->view->errores = $_SESSION['errores'];

                // Una vez usadas las variables de sesión, las eliminmamos
                unset($_SESSION['error']);
                unset($_SESSION['cliente']);
                unset($_SESSION['errores']);
            }

            # Cargamos la vista edit del cliente
            $this->view->render("clientes/editar/index");
        }
    }
    # Método update.
    # Actualiza los detalles de un cliente a partir de los datos del formulario de edición
    public function update($param = [])
    {

        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['edit']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {
            # Saneamos los datos del formulario
            $nombre = filter_var($_POST["nombre"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $apellidos = filter_var($_POST["apellidos"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $telefono = filter_var($_POST["telefono"] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $ciudad = filter_var($_POST['ciudad'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $dni = filter_var($_POST['dni'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ??= '', FILTER_SANITIZE_EMAIL);

            # Creamos objeto con los datos saneados
            $cliente = new classCliente(
                null,
                $apellidos,
                $nombre,
                $telefono,
                $ciudad,
                $dni,
                $email,
                null,
                null
            );

            # Cargamos el id del cliente a actualizar
            $id = $param[0];

            # Obtenemos el objeto original
            $objetOriginal = $this->model->getCliente($id);

            // Creamos array para errores
            $errores = [];

            # Validación. Solo en caso de modificación de campo
            // En cada validación se va a comprar el dato del formulario con el original
            // En caso de ser diferentes se va a proceder a la validación correspomdiente

            // apellidos. 
            //->Campo obligatorio
            //-> Tamaño maximo de 45
            if (strcmp($apellidos, $objetOriginal->apellidos) !== 0) {
                if (empty($apellidos)) {
                    $errores['apellidos'] = "Campo obligatorio";
                } else if (strlen($apellidos) > 45) {
                    $errores['apellidos'] = "El campo admite un máximo de 45 caracteres";
                }
            }

            // nombre. 
            //-> Campo obligatorio
            //-> Tamaño maximo de 20
            if (strcmp($nombre, $objetOriginal->nombre) !== 0) {
                if (empty($nombre)) {
                    $errores['nombre'] = "Campo obligatorio";
                } else if (strlen($nombre) > 20) {
                    $errores['nombre'] = "El campo admite un máximo de 20 caracteres";
                }
            }

            // Teléfono. 
            //-> 9 dígitos numéricos
            // Inicializamos variable para almacenra la expresión regular
            if (strcmp($telefono, $objetOriginal->telefono)) {
                $optionsTel = [
                    'options' => [
                        'regexp' => '/^[0-9]{9}$/'
                    ]
                ];

                if (!empty($telefono) && !filter_var($telefono, FILTER_VALIDATE_REGEXP, $optionsTel)) {
                    $errores['telefono'] = "Debe ser númerico de 9 dígitos";
                }
            }

            // Ciudad. 
            //-> Obligatorio
            //-> Tamaño máximo de 20
            if (strcmp($ciudad, $objetOriginal->ciudad) !== 0) {
                // Ciudad. Obligatorio, tamaño máximo de 20
                if (empty($ciudad)) {
                    $errores['ciudad'] = "Campo obligatorio";
                } else if (strlen($ciudad) > 20) {
                    $errores['ciudad'] = "Superaste el limite de caracteres";
                }
            }

            // dni. 
            //-> Campo obligatorio
            //-> Formato de 8 digitos y 1 mayúscula
            //-> Valor único en la BBDD
            // Creamos un regexp, que permita 8 digitos y 1 letra mayuscula
            if (strcmp($dni, $objetOriginal->dni) !== 0) {
                $dniRegexp = [
                    'options' => [
                        'regexp' => '/^[0-9]{8}[A-Z]$/'
                    ]
                ];

                if (empty($dni)) {
                    $errores['dni'] = "Campo obligatorio";
                } else if (!filter_var($dni, FILTER_VALIDATE_REGEXP, $dniRegexp)) {
                    $errores['dni'] = "Formato DNI incorrecto";
                } else if (!$this->model->validateUniqueDni($dni)) {
                    $errores['dni'] = "El DNI introducido ya ha sido registrado";
                }
            }

            // email. 
            //-> Campo obligatorio
            //-> Formato valido para email
            //-> Valor único en la BBDD 
            if (strcmp($email, $objetOriginal->email) !== 0) {
                if (empty($email)) {
                    $errores['email'] = "Campo obligatorio";
                } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores['email'] = "Formato Email no válido";
                } else if (!$this->model->validateUniqueEmail($email)) {
                    $errores['email'] = "El correo electrónico introducido ya está registrado";
                }
            }

            # Comprobamos la validación
            // Si el array de errores no está vacío, es que hemos tenido algún error de validación
            if (!empty($errores)) {
                // Almacenamos los errores en variables de sesión
                $_SESSION['cliente'] = serialize($cliente);
                $_SESSION['error'] = 'Formulario no validado';
                $_SESSION['errores'] = $errores;

                // Redireccionamos
                header('location:' . URL . 'clientes/editar/' . $id);
            } else {
                // Actualizamos el registro
                $this->model->update($cliente, $id);

                // Añadimos a la variable de sesión un mensaje
                $_SESSION['mensaje'] = 'Cliente actualizado correctamente';

                // Redireccionamos al main de clientes
                header("Location:" . URL . "clientes");
            }
        }
    }

    # Método mostrar
    # Muestra en un formulario de solo lectura los detalles de un cliente
    public function mostrar($param = [])
    {
        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['show']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            $id = $param[0];
            $this->view->title = "Formulario Cliente Mostar";
            $this->view->cliente = $this->model->getCliente($id);
            $this->view->render("clientes/mostrar/index");
        }
    }

    # Método ordenar
    # Permite ordenar la tabla de clientes por cualquiera de las columnas de la tabla
    public function ordenar($param = [])
    {

        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['order']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {


            $criterio = $param[0];
            $this->view->title = "Tabla Clientes";
            $this->view->clientes = $this->model->order($criterio);
            $this->view->render("clientes/main/index");

        }
    }

    # Método buscar
    # Permite buscar los registros de clientes que cumplan con el patrón especificado en la expresión
    # de búsqueda
    public function buscar($param = [])
    {

        # Continuamos la sesion
        session_start();

        # Comprobamos si el usuario está autenticado
        // si no existe la variable de sesión id, no lo está
        if (!isset($_SESSION['id'])) {
            // mostramos el mensaje y redirigimos
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location: " . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['filter']))) {

            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "index");

        } else {

            $expresion = $_GET["expresion"];
            $this->view->title = "Tabla Clientes";
            $this->view->clientes = $this->model->filter($expresion);
            $this->view->render("clientes/main/index");
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
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['exportar']))) {
            $_SESSION['mensaje'] = "El usuario no dispone de privilegios para esta acción";
            header("location:" . URL . "clientes");
        }

        // cargamos los datos para el cvs (retorna un array asociativo)
        $clientes = $this->model->getCSV();

        // Preparamos las cabeceras
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="clientes.csv"');
        // Abrimos el archivo para escribir la exportacion
        $exportFile = fopen('php://output', 'w');
        // Recorremos el fecht de clientes y vamos añadiendo los valores de los compos para cada cliente
        foreach ($clientes as $cliente) {

            $cliente = [

                'apellidos' => $cliente['apellidos'],
                'nombre' => $cliente['nombre'],
                'email' => $cliente['email'],
                'telefono' => $cliente['telefono'],
                'ciudad' => $cliente['ciudad'],
                'dni' => $cliente['dni'],
                'create_at' => $cliente['create_at'],
                'update_at' => $cliente['update_at']

            ];

            // Añadimos un cliente en cada fila, seleccionamos el separador
            fputcsv($exportFile, $cliente, ';');
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
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['importar']))) {
            $_SESSION['mensaje'] = "El usuario no dispone de privilegios para esta acción";
            header("location:" . URL . "clientes");
        }

        // Verificamos la solicitud tipo POST, Si el archivo se ha subido sin errores.
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_csv"]) && $_FILES["archivo_csv"]["error"] == UPLOAD_ERR_OK) {
            // Obtenemos la ruta temporal del archivo subido.
            $file = $_FILES["archivo_csv"]["tmp_name"];

            // Verificamos la extensión del archivo
            $fileExtension = pathinfo($_FILES["archivo_csv"]["name"], PATHINFO_EXTENSION);
            if ($fileExtension != 'csv') {
                $_SESSION['error'] = "El archivo debe ser un CSV";
                header('location:' . URL . 'clientes');
                exit();
            }

            // Verificamos el formato del archivo CSV
            $handle = fopen($file, "r");
            if ($handle === FALSE) {
                $_SESSION['error'] = "Error al abrir el archivo CSV";
                header('location:' . URL . 'clientes');
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
                    $apellidos = filter_var($data[0], FILTER_SANITIZE_SPECIAL_CHARS);
                    $nombre = filter_var($data[1], FILTER_SANITIZE_SPECIAL_CHARS);
                    $email = filter_var($data[2], FILTER_SANITIZE_SPECIAL_CHARS);
                    $telefono = filter_var($data[3], FILTER_SANITIZE_SPECIAL_CHARS);
                    $ciudad = filter_var($data[4], FILTER_SANITIZE_SPECIAL_CHARS);
                    $dni = filter_var($data[5], FILTER_SANITIZE_SPECIAL_CHARS);

                    // Verificamos que el email y dni no existan previamente en la tabla.
                    if ($this->model->validateUniqueEmail($email) && $this->model->validateUniqueDni($dni)) {
                        // Instanciamos objeto classCliente
                        $cliente = new classCliente();
                        // Asignamos los valores extraidos del CSV al nuevo objeto
                        $cliente->apellidos = $apellidos;
                        $cliente->nombre = $nombre;
                        $cliente->email = $email;
                        $cliente->telefono = $telefono;
                        $cliente->ciudad = $ciudad;
                        $cliente->dni = $dni;

                        // Insertamos el nuevo cliente a la tabla.
                        $this->model->create($cliente);
                    } else {
                        // En caso de que uno de los métodos de validación retorne FALSE.
                        $_SESSION['mensaje'] = "Operación cancelada. El cliente ya existe";
                    }
                }

                // Cerramos el archivo.
                fclose($handle);
    
                // Feedback de éxito.
                $_SESSION['mensaje'] = "Importación realizada correctamente";
                header('location:' . URL . 'clientes');
                exit();
            } else {
                // Establece un mensaje de error en la sesión si el archivo no se pudo abrir.
                $_SESSION['error'] = "Error con el archivo CSV";
                // Redirige al usuario a la página de clientes.
                header('location:' . URL . 'clientes');
                exit();
            }
        } else {
            // Establece un mensaje de error en la sesión si no se seleccionó un archivo CSV.
            $_SESSION['error'] = "Seleccione un archivo CSV";
            // Redirige al usuario a la página de clientes.
            header('location:' . URL . 'clientes');
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
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['clientes']['pdf']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'clientes');
            exit();
        }

        // Obtenemos el fetch de clientes
        $clientes = $this->model->get();

        // Instanciamos objeto de pdfClientes -> extiende de FPDF
        $pdf = new pdfClientes();

        // Invocamos contenido (a su vez invoca al resto de métodos)
        $pdf->contenido($clientes);

        // Cerramos 
        $pdf->Output();
    }

}