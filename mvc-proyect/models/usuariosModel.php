<?php

class usuariosModel extends Model
{
    // Extrae todos los usuarios
    public function getAllUsers()
    {
        try {
            $sql = "SELECT 
                        users.id,
                        users.name,
                        users.email,
                        users.password,
                        roles_users.role_id,
                        roles.name AS role_name
                    FROM
                        roles_users
                            INNER JOIN
                        users ON roles_users.user_id = users.id
                            INNER JOIN
                        roles ON roles_users.role_id = roles.id";

            $conexion = $this->db->connect();
            $pdoSt = $conexion->prepare($sql);
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();
            return $pdoSt;
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Extrae todos los roles
    public function getAllRoles()
    {
        try {
            $sql = "SELECT * from roles";

            $conexion = $this->db->connect();
            $pdoSt = $conexion->prepare($sql);
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();
            return $pdoSt;
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Inserta un registro en la tabla usuarios
    // Asigna un rol al registro insertado
    public function create(classUser $user, int $id_rol)
    {
        try {

            // PASSWORD_DEFAULT -> Selecciona el cifrado más fuerte disponible
            $password_hashed = password_hash($user->password, PASSWORD_DEFAULT);

            // Inserción del usuario
            $sql = "INSERT INTO users 
                    VALUES (
                            null,
                            :nombre,
                            :email,
                            :pass,
                            default,
                            default
                            )";

            $pdo = $this->db->connect();
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':nombre', $user->name, PDO::PARAM_STR, 50);
            $stmt->bindParam(':email', $user->email, PDO::PARAM_STR, 50);
            $stmt->bindParam(':pass', $password_hashed, PDO::PARAM_STR, 60);

            $stmt->execute();

            // Registro del id del último registro insertado
            $id_usuario = $pdo->lastInsertId();

            // Asociamos el rol al usuario
            $sql = "INSERT INTO roles_users 
                    VALUES (
                            null,
                            :user_id,
                            :role_id,
                            default,
                            default
                            )";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $id_rol, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Valida que un email no exista en la tabla
    // Retorna 0 si el email de entrada no exste en la tabla
    public function validateUniqueEmail($email)
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $conexion = $this->db->connect();
            $pdoSt = $conexion->prepare($sql);
            $pdoSt->bindParam(":email", $email, PDO::PARAM_STR);
            $pdoSt->execute();
            $count = $pdoSt->fetchColumn();

            // Si el email no existe, retorna 0
            return $count == 0;
        } catch (PDOException $e) {
            include_once ('template/partials/errorDB.php');
            exit();
        }
    }

    // Busca y retorna un usuario por id
    public function getUser(int $id)
    {
        try {
            $sql = "SELECT 
                        users.id,
                        users.name,
                        users.email,
                        users.password,
                        roles_users.role_id,
                        roles.name AS role_name
                    FROM
                        roles_users
                            INNER JOIN
                        users ON roles_users.user_id = users.id
                            INNER JOIN
                        roles ON roles_users.role_id = roles.id
                    WHERE
                        users.id = :id";

            $pdo = $this->db->connect();
            $pdoSt = $pdo->prepare($sql);
            $pdoSt->bindParam(':id', $id, PDO::PARAM_INT);
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();
            return $pdoSt->fetch();
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Retorna un objeto con las propiedades id y rol, correspondientes a un usuario
    // Argumento: id del usuario a obtebner el rol
    public function getRolUsuario(int $id)
    {
        try {
            $sql = "SELECT 
                        roles.id, roles.name
                    FROM
                        roles
                            INNER JOIN
                        roles_users ON roles.id = roles_users.role_id
                            INNER JOIN
                        users ON roles_users.user_id = users.id
                    WHERE
                        users.id = :id";

            $pdo = $this->db->connect();
            $pdoSt = $pdo->prepare($sql);
            $pdoSt->bindParam(':id', $id, PDO::PARAM_INT);
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();
            return $pdoSt->fetch();
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Elimina un registro de la tabla usuarios
    // Arguimeto: id del usuario a eliminar
    public function delete(int $id)
    {
        try {
            $sql = "DELETE FROM users WHERE id=:id";

            $conexion = $this->db->connect();
            $pdoSt = $conexion->prepare($sql);
            $pdoSt->bindParam(":id", $id, PDO::PARAM_INT);
            $pdoSt->execute();
            return $pdoSt;
        } catch (PDOException $error) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Ordena los resultados según un criterio preseleccionado
    public function order(int $criterio)
    {
        try {
            $sql = "SELECT 
                        users.id,
                        users.name,
                        users.email,
                        users.password,
                        roles_users.role_id,
                        roles.name AS role_name
                    FROM
                        roles_users
                            INNER JOIN
                        users ON roles_users.user_id = users.id
                            INNER JOIN
                        roles ON roles_users.role_id = roles.id
                    ORDER BY :criterio";

            $conexion = $this->db->connect();
            $pdoSt = $conexion->prepare($sql);
            $pdoSt->bindParam(':criterio', $criterio, PDO::PARAM_INT);
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();
            return $pdoSt;
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Filtra los resultados según una expresión dada
    public function filter($expresion)
    {
        try {

            $sql = "SELECT 
            users.id,
            users.name,
            users.email,
            roles_users.role_id,
            roles.name AS role_name
        FROM
            roles_users
                INNER JOIN
            users ON roles_users.user_id = users.id
                INNER JOIN
            roles ON roles_users.role_id = roles.id
        WHERE
            users.id LIKE :expresion_id
            OR users.name LIKE :expresion_name
            OR users.email LIKE :expresion_email
            OR roles_users.role_id LIKE :expresion_role_id
            OR roles.name LIKE :expresion_role_name";

            $conexion = $this->db->connect();

            $expresionValor = "%" . $expresion . "%";
            $pdoSt = $conexion->prepare($sql);

            $pdoSt->bindValue(':expresion_id', $expresionValor, PDO::PARAM_STR);
            $pdoSt->bindValue(':expresion_name', $expresionValor, PDO::PARAM_STR);
            $pdoSt->bindValue(':expresion_email', $expresionValor, PDO::PARAM_STR);
            $pdoSt->bindValue(':expresion_role_id', $expresionValor, PDO::PARAM_STR);
            $pdoSt->bindValue(':expresion_role_name', $expresionValor, PDO::PARAM_STR);
            
            $pdoSt->setFetchMode(PDO::FETCH_OBJ);
            $pdoSt->execute();

            return $pdoSt;
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }

    // Actualiza los detalles de un usuario en la tabla
    // Actualiza el rol del usuario en la tabla roles_users
    public function update(classUser $usuario, $id_rol)
    {
        try {
            // Obtener la conexión a la base de datos
            $conexion = $this->db->connect();

            // Actualizamos los detalles del usuario en la tabla users
            $sql = "UPDATE 
                        users 
                    SET 
                        name = :name,
                        email = :email,
                        password = :password,
                        update_at = NOW()
                    WHERE
                        id = :id";

            $pdoSt = $conexion->prepare($sql);
            // Vinculamos los parámetros
            $pdoSt->bindParam(":id", $usuario->id, PDO::PARAM_INT);
            $pdoSt->bindParam(":name", $usuario->name, PDO::PARAM_STR, 50);
            $pdoSt->bindParam(":email", $usuario->email, PDO::PARAM_STR, 50);
            $pdoSt->bindParam(":password", $usuario->password, PDO::PARAM_STR, 60);

            $pdoSt->execute();

            // Actualizamos el rol del usuario en la tabla roles_users
            $sql = "UPDATE roles_users SET
                    role_id = :role_id,
                    update_at = NOW()
                WHERE
                    user_id = :user_id";
            $pdoSt = $conexion->prepare($sql);
            // Vinculamos los parámetros
            $pdoSt->bindParam(":role_id", $id_rol, PDO::PARAM_INT);
            $pdoSt->bindParam(":user_id", $usuario->id, PDO::PARAM_INT);
            $pdoSt->execute();
        } catch (PDOException $e) {
            require_once ("template/partials/errorDB.php");
            exit();
        }
    }


}
