<?php
class classUser
{

    public $id;
    public $name;
    public $email;
    public $password;
    public $password_confirm;
    // Nuevas propiedades para el CRUD
    public $role_id;
    public $role_name;

    public function __construct(
        $id = null,
        $name = null,
        $email = null,
        $password = null,
        $password_confirm = null,
        // Nuevas propiedades para el CRUD
        $role_id = null,
        $role_name = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->password_confirm = $password_confirm;
        // Nuevas propiedades para el CRUD
        $this->role_id = $role_id;
        $this->role_name = $role_name;
    }


}


