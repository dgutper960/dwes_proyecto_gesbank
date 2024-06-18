<?php

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
// Archivo de configuración eliminado del repositorio
require_once 'config/credencialesMail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Register extends Controller
{

    public function render()
    {

        # iniciamos o continuar sessión
        session_start();

        # Si existe algún mensaje 
        if (isset($_SESSION['mensaje'])) {

            $this->view->mensaje = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
        }

        # Inicializamos los campos del formulario
        $this->view->name = null;
        $this->view->email = null;
        $this->view->password = null;

        if (isset($_SESSION['error'])) {

            # Mensaje de error
            $this->view->error = $_SESSION['error'];
            unset($_SESSION['error']);

            # Variables de autorrelleno
            $this->view->name = $_SESSION['name'];
            $this->view->email = $_SESSION['email'];
            $this->view->password = $_SESSION['password'];
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            unset($_SESSION['password']);

            # Tipo de error
            $this->view->errores = $_SESSION['errores'];
            unset($_SESSION['errores']);
        }

        $this->view->render('register/index');
    }


    public function validate()
    {

        # Iniciamos o continuamos con la sesión
        session_start();

        # Saneamos el formulario
        $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = filter_var($_POST['password'], FILTER_SANITIZE_SPECIAL_CHARS);
        $password_confirm = filter_var($_POST['password-confirm'], FILTER_SANITIZE_SPECIAL_CHARS);

        # Validaciones

        $errores = array();

        # Validar name
        if (empty($name)) {
            $errores['name'] = "Campo obligatorio";
        } else if (!$this->model->validarName($name)) {
            $errores['name'] = "Nombre de usuario no permitido";
        }

        # Validar Email
        if (empty($email)) {
            $errores['email'] = "Campo obligatorio";
        } else if (empty($email)) {
            $errores['email'] = "Campo obligatorio";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = "Email: Email no válido";
        } elseif (!$this->model->validaEmailUnique($email)) {
            $errores['email'] = "Email existente, ya está registrado";
        }

        # Validar password
        if (empty($password)) {
            $errores['password'] = "No se ha introducido password";
        } else if (strcmp($password, $password_confirm) !== 0) {
            $errores['password'] = "Password no coincidentes";
        } elseif (!$this->model->validarPass($password)) {
            $errores['password'] = "Password: No permitido";
        }

        if (!empty($errores)) {

            $_SESSION['errores'] = $errores;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
            $_SESSION['error'] = "Fallo en la validación del formulario";

            header("location:" . URL . "register");
            
        } else {

            # Añade nuevo usuario

            $this->model->create($name, $email, $password);

            $_SESSION['mensaje'] = "Usuario registrado correctamente";
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;

                       // Enviamos un correo de bienvenida
                       try {

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
        
                        $destinatario = $email;
                        $remite = USUARIO;
                        $asunto = "Le damos la bienvenida como nuevo usuario";
                        $mensaje =
                            "<h3>Estas son sus credenciales:</h3>"
                            . "<b>Nombre: " . $name . "</b><br>"
                            . "<b>Email: " . $email . "</b><br>"
                            . "<b>Contraseña: " . $password . "</b><br>"
                            . "<p>Por seguridad deberá cambiar la contraseña</p>"
                            . "<p>Esperamos que disfrute de nuestros servicios</p>";
        
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

            # Vuelve a login
            header("location:" . URL . "login");
        }
    }
}
