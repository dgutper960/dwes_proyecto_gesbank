<?php

/**
 * Hemos instalado en nuestro proyecto
 * la librería PHPMailer
 *  - Necesitamos importarlas para trabajar con sus funcionalidades
 *  - Lo hacemos en este controlador para evitar la sobrecarga de recursos inecesarios
 */

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
// Archivo de configuración eliminado del repositorio
require_once 'config/credencialesMail.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Perfil extends Controller
{

    # Muestra los detalles del perfil antes de eliminar
    public function render()
    {

        # Iniciamos o continuamos con la sesión
        session_start();

        # Capa autentificación
        if (!isset($_SESSION['id'])) {
            header("location:" . URL . "login");
        }

        # Capa mensaje
        if (isset($_SESSION['mensaje'])) {
            $this->view->mensaje = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
        }


        # Obtenemos objeto con los detalles del usuario
        $this->view->user = $this->model->getUserId($_SESSION['id']);
        $this->view->title = 'Perfil de Usuario - GesBank - MVC';

        $this->view->render('perfil/main/index');

    }

    # Editar los detalles name y email de usuario
    public function edit()
    {

        # Iniciamos o continuamos sesión
        session_start();

        # Capa de autentificación
        if (!isset($_SESSION['id'])) {

            header('location:' . URL . 'login');

        }

        # Comprobamos si existe mensaje
        if (isset($_SESSION['mensaje'])) {

            $this->view->mensaje = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);

        }

        # Obtenemos objeto User con los detalles del usuario
        $this->view->user = $this->model->getUserId($_SESSION['id']);

        # Capa no validación formulario
        if (isset($_SESSION['error'])) {

            # Mensaje de error
            $this->view->error = $_SESSION['error'];
            unset($_SESSION['error']);

            # Variables de autorrelleno
            $this->view->user = unserialize($_SESSION['user']);
            unset($_SESSION['user']);

            # Tipo de error
            $this->view->errores = $_SESSION['errores'];
            unset($_SESSION['errores']);

        }

        $this->view->title = 'Modificar Perfil Usuario - GesBank - MVC';
        $this->view->render('perfil/edit/index');


    }

    # Valida el formulario de modificación de perfil
    public function valperfil()
    {

        # Iniciamos o continuamos con la sesión
        session_start();

        # Capa autentificación
        if (!isset($_SESSION['id'])) {

            header("location:" . URL . "login");
        }

        # Saneamos el formulario
        $name = filter_var($_POST['name'] ??= null, FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($_POST['email'] ??= null, FILTER_SANITIZE_EMAIL);

        # Obtenemos objeto con los detalles del usuario
        $user = $this->model->getUserId($_SESSION['id']);

        # Validaciones
        $errores = [];

        // name
        if (strcmp($user->name, $name) !== 0) {
            if (empty($name)) {
                $errores['name'] = "Nombre de usuario es obligatorio";
            } else if ((strlen($name) < 5) || (strlen($name) > 50)) {
                $errores['name'] = "Nombre de usuario ha de tener entre 5 y 50 caracteres";
            } else if (!$this->model->validateName($name)) {
                $errores['name'] = "Nombre de usuario ya ha sido registrado";
            }
        }

        // email
        if (strcmp($user->email, $email) !== 0) {
            if (empty($email)) {
                $errores['email'] = "Email es un campo obligatorio";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = "Email no válido";
            } elseif (!$this->model->validateEmail($email)) {
                $errores['email'] = "Email ya ha sido registrado";
            }
        }

        # Crear objeto user
        $user = new classUser(
            $user->id,
            $name,
            $email,
            null
        );


        # Comprobamos si hay errores
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['user'] = serialize($user);
            $_SESSION['error'] = "Formulario con errores de validación";

            header('location:' . URL . 'perfil/edit');

        } else {

            # Actualizamos perfil
            $this->model->update($user);

            $_SESSION['name_user'] = $name;
            $_SESSION['mensaje'] = 'Usuario modificado correctamente';

            // En este momento, mandamos un email al usuario mostrando los cámbios realizados
            try {
                // Instanciamops objeto phpMailer
                $mail = new PHPMailer(true);
                // juego de caracteres
                $mail->CharSet = "UTF-8";
                $mail->Encoding = "quoted-printable";
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = USUARIO;                                      // Cambiar por tu dirección de correo
                $mail->Password = PASS;                                         // Cambiar por tu contraseña

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Introducimos los parametros para el email
                $destinatario = $email;
                $remite = USUARIO;
                $asunto = "Notificación sobre su perfil usuario";
                // Implementamos el mensaje que recibirá el ususario
                $mensaje =
                    "<h1>Se realizaron algunos cámbios en su prefil</h1>"
                    . "<h2>Estos son los valores actuales:</h2>"
                    . "Nombre de usuario: " . $name
                    . "<br>"
                    . "Email: " . $email
                    . "<br><br>"
                    . "<p>Esperamos que siga disfrutando de nuestros servicios</p>";


                $mail->setFrom($remite, $name);
                $mail->addAddress($destinatario);
                $mail->addReplyTo($remite, $name);

                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $mensaje;

                // Enviar correo electrónico
                $mail->send();
            } catch (Exception $e) {
                // Manejar excepciones
                $_SESSION['error'] = 'Error al enviar el mensaje: ' . $e->getMessage();
            }


            header('location:' . URL . 'perfil');

        }

    }

    # Modificación del password
    public function pass()
    {

        # Iniciamos o continuamos sesión
        session_start();

        # Capa de autentificación
        if (!isset($_SESSION['id'])) {

            header('location:' . URL . 'login');

        }

        # Comprobamos si existe mensaje
        if (isset($_SESSION['mensaje'])) {
            $this->view->mensaje = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
        }

        # Capa no validación formulario
        if (isset($_SESSION['error'])) {

            # Mensaje de error
            $this->view->error = $_SESSION['error'];
            unset($_SESSION['error']);

            # Tipo de error
            $this->view->errores = $_SESSION['errores'];
            unset($_SESSION['errores']);

        }

        # título página
        $this->view->title = "Modificar password";
        $this->view->render('perfil/pass/index');


    }

    # Validación cambio password
    public function valpass()
    {

        # Iniciamos o continuamos con la sesión
        session_start();

        # Capa autentificación
        if (!isset($_SESSION['id'])) {

            header("location:" . URL . "login");
        }

        # Saneamos el formulario
        $password_form = filter_var($_POST['password_actual'] ??= null, FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_var($_POST['password'] ??= null, FILTER_SANITIZE_SPECIAL_CHARS);
        $password_confirm = filter_var($_POST['password_confirm'] ??= null, FILTER_SANITIZE_SPECIAL_CHARS);

        # Obtenemos objeto con los detalles del usuario
        $user = $this->model->getUserId($_SESSION['id']);
        $usuario = $this->model->getUserId($_SESSION['id']);

        # Validaciones
        $errores = array();

        # Validar password actual
        if (!password_verify($password_form, $user->password)) {
            $errores['password_actual'] = "Password actual no es correcto";
        }

        # Validar nuevo password
        if (empty($password)) {
            $errores['password'] = "Password no introducido";
        } else if (strcmp($password, $password_confirm) !== 0) {
            $errores['password'] = "Password no coincidentes";
        } else if ((strlen($password) < 5) || (strlen($password) > 60)) {
            $errores['password'] = "Password ha de tener entre 5 y 60 caracteres";
        }


        if (!empty($errores)) {

            $_SESSION['errores'] = $errores;
            $_SESSION['error'] = "Formulario con errores de validación";

            header("location:" . URL . "perfil/pass");

        } else {

            # Crear objeto user
            $user = new classUser(
                $user->id,
                null,
                null,
                $password
            );

            # Actualiza password
            $this->model->updatePass($user);

            $_SESSION['mensaje'] = "La contraseña fue cambiada correctamente";

            // Enviamos correo después de cambiar el password
            try {
                // Configurar PHPMailer
                $mail = new PHPMailer(true);
                $mail->CharSet = "UTF-8";
                $mail->Encoding = "quoted-printable";
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = USUARIO;                                      // Cambiar por tu dirección de correo
                $mail->Password = PASS;                                         // Cambiar por tu contraseña

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configurar destinatario, remitente, asunto y mensaje
                $destinatario = $usuario->email;
                $remitente = USUARIO;
                $asunto = "Nuevo cambio de contraseña de tu Perfil";
                $mensaje =
                    "<h1>La contraseña del perfil fue cambiada con exito</h1>"
                    ."<h2>Su nueva contraseña es:</h2>"
                    . "<h2>". $password ."</h2>";

                $mail->setFrom($remitente, $usuario->name);
                $mail->addAddress($destinatario);
                $mail->addReplyTo($remitente, $usuario->name);

                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $mensaje;

                // Enviar correo electrónico
                $mail->send();
            } catch (Exception $e) {
                // Manejar excepciones
                $_SESSION['error'] = 'Error al enviar el mensaje: ' . $e->getMessage();
            }


            $_SESSION['mensaje'] = "Password modificado correctamente";

            #Vuelve corredores
            header("location:" . URL . "perfil");
        }
    }



    # Elimina definitivamente el perfil
    public function delete()
    {

        # Iniciamos o continuamos con la sesión
        session_start();

        # Capa autentificación
        if (!isset($_SESSION['id'])) {

            header("location:" . URL . "login");

        } else {

            // En este caso mandamos email de despedida
            try {

                $user = $this->model->getUserId($_SESSION['id']);
                $mail = new PHPMailer(true);
                $mail->CharSet = "UTF-8";
                $mail->Encoding = "quoted-printable";
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = USUARIO;                                      // Cambiar por tu dirección de correo
                $mail->Password = PASS;                                         // Cambiar por tu contraseña

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $destinatario = $user->email;
                $remite = USUARIO;
                $asunto = "Su perfil de usuario ha sido eliminado";
                $mensaje =
                    "<h2>Su perfil con los siguientes datos ha sido eliminado con éxito</h2>"
                    . "<b>" . $user->name . "</b><br>"
                    . "<b>" . $user->email . "</b><br>"
                    . "<p>Sentimos que haya decidido dejar de usar nuestros servicios</p>"
                    . "<p>Esperamos verle pronto de vuelta</p>";

                $mail->setFrom($remite, $user->name);
                $mail->addAddress($destinatario);
                $mail->addReplyTo($remite, $user->name);

                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $mensaje;

                // Enviar correo electrónico
                $mail->send();
            } catch (Exception $e) {
                // Manejar excepciones
                $_SESSION['error'] = 'Error al enviar el mensaje: ' . $e->getMessage();
            }

            # Elimino perfil de usuario
            $this->model->delete($_SESSION['id']);

            # Destruyo la sesión
            session_destroy();

            # Salgo de la aplicación
            header('location:' . URL . 'index');
        }

    }



}
