<?php
include_once 'page.php';

class LoginPage extends Page
{
    public $users;

    public function __construct()
    {
        parent::__construct('Login');  
        $this->users = getUsersPublic();   
    }

    public function printContent()
    {
        echo "Login page.\n";
    }
}

$page = new LoginPage();
$page->render();
?>