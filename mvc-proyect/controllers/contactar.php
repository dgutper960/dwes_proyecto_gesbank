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

// Creamos nnuestro controlador
class Contactar extends Controller
{

    // Constructor como siempre
    function __construct()
    {
        parent::__construct();
    }

    // Function render
    //-> Para comprobar si venimos de un formulario vacío 
    //    se ha implementado la clase classContactar
    function render()
    {
        session_start();

        // Creamos la propiedad en la vista (isntancia classContactar)
        $this->view->contactar = new classContactar();

        # Comprobar si vuelvo de un registro no validado
        if (isset($_SESSION['error'])) {
            # Mensaje de error
            $this->view->error = $_SESSION['error'];

            # Recupero array de errores específicos
            $this->view->errores = $_SESSION['errores'];

            //deserializamos si existe la variable de sesión
            if (isset($_SESSION['contactar'])) {
                $this->view->contactar = unserialize($_SESSION['contactar']);
            }
            // Autorellenamos los campos de la vista
            $this->view->contactar = unserialize($_SESSION['contactar']);

            // borramos las variables de sesion 
            unset($_SESSION['error']);
            unset($_SESSION['errores']);
            unset($_SESSION['contactar']);

        } else {
            // en este bloque el usuario no viene de un registro no valido
            // Creamos la propiedad en la vista (isntancia classContactar)
            $this->view->contactar = new classContactar();

            # Si exsiste la variable de sesión mesage, lo mostramos
            if (isset($_SESSION['mensaje'])) {
                $this->view->mensaje = $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            }

            // Mandamos a la vista contactar
            $this->view->render('contactar/index');
        }
    }

    public function validar()
    {
        session_start();

        // instanciamos objeto
        $this->view->contactar = new classContactar();

        // Si volvemos de formulario no válido
        if (isset($_SESSION['error'])) {
            // Cragamos el mensaje de error
            $this->view->error = $_SESSION['error'];

            // Autorrellenamos el formulario
            $this->view->contactar = unserialize($_SESSION['contactar']);

            // Recuperamos errores de la sesion
            $this->view->errores = $_SESSION['errores'];

            // Borramos variables, no queremos bucles infinitos
            unset($_SESSION['error']);
            unset($_SESSION['errores']);
            unset($_SESSION['contactar']);
        }

        // Saneamos los datos del formulario
        $nombre = filter_var($_POST['nombre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $asunto = filter_var($_POST['asunto'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $mensaje = filter_var($_POST['mensajeMail'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        // Instanciamos con los datos saneados
        $contactar = new classContactar(
            $nombre,
            $email,
            $asunto,
            $mensaje
        );

        // Validación:
        $errores = [];
        // Nombre
        //-> obligatiorio
        if (empty($nombre)) {
            $errores['nombre'] = 'El campo es obligatorio';
        }
        // Email
        //-> formato email
        if (empty($email)) {
            $errores['email'] = 'El campo es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del email es incorrecto.';
        }
        // Asunto
        //-> obligatiorio
        if (empty($asunto)) {
            $errores['asunto'] = 'El campo es obligatorio';
        }
        // Mensaje
        //-> obligatiorio
        if (empty($mensaje)) {
            $errores['mensajeMail'] = 'El campo es obligatorio';
        }

        // Comprobamos validación
        if (!empty($errores)) {
            // Si hay errores, almacenarlos en la sesión
            $_SESSION['contactar'] = serialize($contactar);
            $_SESSION['error'] = "Formulario no validado";
            $_SESSION['errores'] = $errores;
            // Redirigimos al formulario de contactar
            header('Location:' . URL . 'contactar');

        } else {
            try {
                // Configurar PHPMailer
                $mail = new PHPMailer(true);
                $mail->CharSet = "UTF-8";
                $mail->Encoding = "quoted-printable";
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                // Constantes definidas en archivo aparte (privacidad)
                // config/credencialesMail.php
                $mail->Username = USUARIO;
                $mail->Password = PASS;

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuramos las propiedades del email
                $destinatario = $email;
                $remitente = USUARIO;
                $asuntoMail = $asunto;
                $mensajeMail = $mensaje;

                $mail->setFrom($remitente, $nombre);
                $mail->addAddress($destinatario);
                $mail->addReplyTo($remitente, $nombre);

                $mail->isHTML(true);
                $mail->Subject = $asuntoMail;
                $mail->Body = $mensajeMail;

                // Enviar el correo al destinatario
                $mail->send();

                // Acciones de feedback al usuario
                $_SESSION['mensaje'] = 'Mensaje enviado correctamente.';
                header('Location:' . URL . 'contactar');

            } catch (Exception $e) {
                $_SESSION['error'] = 'Error al enviar el mensaje: ' . $e->getMessage(); // mostramos mensaje de error
                header('Location:' . URL . 'contactar'); // Usuario al contactar

            }
        }
    }

}