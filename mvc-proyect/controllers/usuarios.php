<?php

class Usuarios extends Controller
{

    // Carga el CRUD de usuarios en la vista main 
    function render($param = [])
    {

        # Inicioar o continuar la sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");

        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['main']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {
            # Si hay mensajes en la sesión los cargamos en la vista
            if (isset($_SESSION['mensaje'])) {
                $this->view->mensaje = $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            }

            # Cargamos la propiedad del título
            $this->view->title = "Lista usuarios - GesBank";

            // Cargamos los datos para el CRUD en la vista
            $this->view->usuarios = $this->model->getAllUsers();
            $this->view->roles = $this->model->getAllRoles();
            $this->view->render("usuarios/main/index");
        }
    }

    // Control de acciones para el formulario de nuevo usuario
    function nuevo($param = [])
    {
        # Iniciamos o continuamos la sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['nuevo']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {

            // Instancia de usuario
            $this->view->usuario = new classUser();

            # Si existen errores -> venimos de formulario no válidado
            if (isset($_SESSION['error'])) {
                // Cargamos en la vista el mensaje de error
                $this->view->error = $_SESSION['error'];

                // Rellenamos el formulario con los datos de la sesión
                $this->view->usuario = unserialize($_SESSION['usuario']);
                // Datos para la lista desplegable
                $this->view->roles = $this->model->getAllRoles();

                // Cargamos los errores en la vista
                $this->view->errores = $_SESSION['errores'];

                // Recuperamos el valor del rol seleccionado
                $this->view->rol_select = isset($_SESSION['roles']) ? $_SESSION['roles'] : null;

                // Limpiamos las variables de sesión -> Evita bucle infinito
                unset($_SESSION['error']);
                unset($_SESSION['errores']);
                unset($_SESSION['usuario']);
            }

            # Si no hay errores -> Llegamos al formulario por priemra vez
            // Cargamos las propiedades necesarias para la vista
            $this->view->title = "Panel Usuarios - GesBank";
            // Propiedad para la lista desplegable de roles
            $this->view->roles = $this->model->getAllRoles();
            // Cargamos la vista
            $this->view->render("usuarios/nuevo/index");
        }
    }

    // Inserta los datos del formulario, nuevo usuario, en el método create
    function create($param = [])
    {
        // Iniciar o continuar sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['nuevo']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {

            // Saneamos los datos del formulario
            $nombre = filter_var($_POST['nombre'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_var($_POST['password'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $password_confirm = filter_var($_POST['password_confirm'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            $rol = filter_var($_POST['rol'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Creamos el usuario con los datos saneados
            $usuario = new classUser(
                null,
                $nombre,
                $email,
                $password,
                $password_confirm
            );

            // Validación
            $errores = [];

            //Nombre
            //-> Obligatorio
            if (empty($nombre)) {
                $errores['nombre'] = 'El campo es obligatorio';
            }

            // Email
            //-> Obligatorio
            //-> debe ser un email válido 
            //-> Valor único en la tabla	
            if (empty($email)) {
                $errores['email'] = 'El campo es obligatorio';
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'El formato del email no es correcto';
            } else if (!$this->model->validateUniqueEmail($email)) {
                $errores['email'] = 'Email en uso por otra cuenta';
            }

            // Contraseña
            //-> Obligatorio
            if (empty($password)) {
                $errores['password'] = 'Debe registrar una contraseña para el usuario';
            }

            // Confirmar contraseña
            //-> Obligatorio
            //-> Debe coincidir con el campo password
            if (empty($password_confirm)) {
                $errores['password_confirm'] = 'Vuelva a introducir la contraseña';
            } else if ($password != $password_confirm) {
                $errores['password_confirm'] = 'Las contraseñas no coinciden, deben coincidir';
            }

            // Rol
            //-> Obligatorio
            //-> Debe estar en uso
            if (empty($rol)) {
                $errores['roles'] = 'El campo es obligatorio';
            } else if (!in_array($rol, $GLOBALS['usuarios']['roles'])) {
                $errores['roles'] = 'El rol no está en uso. Deje de piratear el sitio web';
            }

            // Comprobar validación
            if (!empty($errores)) {
                // Si hay errores en la validación
                $_SESSION['usuario'] = serialize($usuario);
                $_SESSION['error'] = 'Formulario no válido, rebise los campos';
                $_SESSION['errores'] = $errores;
                $_SESSION['roles'] = $rol;

                //Redireccionamos de nuevo al formulario
                header('location:' . URL . 'usuarios/nuevo/index');
            } else {
                # Añadimos el registro a la tabla
                $this->model->create($usuario, $rol);

                //Crearemos un mensaje, indicando que se ha realizado dicha acción
                $_SESSION['mensaje'] = "Usuario creado correctamente.";

                // Redireccionamos a la vista principal de usuarios
                header("Location:" . URL . "usuarios");
            }
        }
    }

    // Control para el borrado de un usuario
    function delete($param = [])
    {

        # Inicioar o continuar la sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['delete']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {

            //Obteneemos id del objeto
            $id = $param[0];

            //Eliminamos el objeto
            $this->model->delete($id);

            //Generar mensasje
            $_SESSION['mensaje'] = 'Usuario borrado correctamente';

            header("Location:" . URL . "usuarios");
        }
    }

    // Muestra los detalles de un usuario 
    function mostrar($param = [])
    {

        // Iniciar o continuar sesión
        session_start();

        // Tomamos el id de la entrada (enviado por GET desde la vista)
        $id = $param[0];

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['mostrar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {

            $this->view->title = "Panel Usuarios - GesBank";
            $this->view->usuario = $this->model->getUser($id);
            $this->view->rol = $this->model->getRolUsuario($id);

            $this->view->render("usuarios/mostrar/index");
        }
    }




    // Controla las operaciones para el formulario de editar usuario
    public function editar($param = [])
    {
        // Iniciar o continuar sesión
        session_start();

        // Comprobar si el usuario está identificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");
            exit();
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['editar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuario');
            exit();
        } else {

            // Cargamos el id del usuario en  edición
            $id = $param[0];

            // Cargamos en la vista el array de roles
            $this->view->roles = $this->model->getAllRoles();

            // Cargamos el título
            $this->view->title = "Panel Usuarios - GesBank";

            // Cargamos en la vista el usuario en edición 
            $this->view->usuario = $this->model->getUser($id);

            // Cargamos el rol del usuario en edición
            $this->view->rol_user = $this->model->getRolUsuario($id);

            // Si hay errores -> venimos de una no validación
            if (isset($_SESSION['error'])) {
                // Cargamos error
                $this->view->error = $_SESSION['error'];
                // Autorrellenamos
                $this->view->usuario = $this->model->getUser($id);

                // Cargamos errores
                $this->view->errores = $_SESSION['errores'];
                // Limpiamos variables de sesión
                unset($_SESSION['error']);
                unset($_SESSION['errores']);
                unset($_SESSION['usuario']);
            }

            // Cargamos la vista
            $this->view->render('usuarios/editar/index');
        }
    }


    // 
    public function update($param = [])
    {
        // Iniciar sesión
        session_start();

        // Comprobar si el usuario está identificado
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";
            header("location:" . URL . "login");
            exit();
        }

        // Verificar permisos de usuario para editar
        if (!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['editar'])) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
            exit();
        }

        // Validación

        // Obtener el ID del usuario a editar
        $id = $param[0];

        // Instanciamos usuario original, validación del email
        $original_user = $this->model->getUser($id);

        // Obtener los datos del formulario y sanitizarlos
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
        $pasword_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_SPECIAL_CHARS);
        // Obtenemos el ID del rol seleccionado del formulario -> necesario al final
        $id_rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_NUMBER_INT);

        // Validar los datos
        $errores = [];

        //Nombre
        //-> Obligatorio
        if (empty($nombre)) {
            $errores['nombre'] = 'El campo es obligatorio.';
        }

        // Email
        //-> Obligatorio
        //-> debe ser un email válido 
        //-> Valor único en la tabla
        if (empty($email)) {
            $errores['email'] = 'El campo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del email no es correcto';
        } elseif ($email !== $original_user->email && !$this->model->validateUniqueEmail($email)) {
            $errores['email'] = 'El email ya está en uso';
        }

        // Confirmar contraseña
        //-> Obligatorio
        //-> Debe coincidir con el campo password
        if (!empty($password) || !empty($pasword_confirm)) {
            if (empty($password)) {
                $errores['password'] = 'El campo es obligatorio';
            } elseif ($password !== $pasword_confirm) {
                $errores['password_confirm'] = 'Las contraseñas no coinciden';
            }
        }

        // Comprobar si hay errores de validación
        if (!empty($errores)) {
            // Errores de validación
            $_SESSION['error'] = 'Formulario no validado';
            $_SESSION['errores'] = $errores;
            // El id del usuario debe ser pasado en la URL
            header('Location:' . URL . 'usuarios/editar/' . $id);
            exit();
        }

        // Si la password no está vacía, cifrarla
        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT); //-> PHP obtiene el cifrado más fuerte diponible
        }

        // Crear un objeto de usuario con los datos actualizados
        $usuario = new classUser(
            $id,
            $nombre,
            $email,
            $password_hashed
        );

        // Actualizar el usuario y el rol en la base de datos
        $this->model->update($usuario, $id_rol);

        // Mensaje de éxito
        $_SESSION['mensaje'] = "Usuario editado correctamente";

        // Redirigir al listado de usuarios
        header('location:' . URL . 'usuarios');
        exit();
    }

    // Ordena los usuarios a partir de un criterio elegido
    function ordenar($param = [])
    {
        //Inicio o continuo sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['ordenar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {

            $criterio = $param[0];
            $this->view->title = "Panel Usuarios - GesBank";
            $this->view->usuarios = $this->model->order($criterio);
            $this->view->model = $this->model;
            $this->view->render("usuarios/main/index");
        }
    }

    // Filtra los resultados en tabla usuarios a partir una expresión dada
    function buscar($param = [])
    {
        //Inicio o continuo sesión
        session_start();

        // Comprobar autenticación
        if (!isset($_SESSION['id'])) {
            $_SESSION['mensaje'] = "El usuario debe autenticarse";

            header("location:" . URL . "login");
        } else if ((!in_array($_SESSION['id_rol'], $GLOBALS['usuarios']['buscar']))) {
            $_SESSION['mensaje'] = "Operación sin privilegios";
            header('location:' . URL . 'usuarios');
        } else {


            $expresion = $_GET["expresion"];
            $this->view->title = "Panel Usuarios - GesBank";
            $this->view->usuarios = $this->model->filter($expresion);
            $this->view->model = $this->model;
            $this->view->render("usuarios/main/index");
        }
    }


}

