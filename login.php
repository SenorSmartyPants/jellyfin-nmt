<?php
include_once 'page.php';

class LoginPage extends Page
{
    public $users;

    public function __construct()
    {
        $this->authRequired = false;
        parent::__construct('Login');  
        $this->users = getUsersPublic();
        $this->handleRequest();
    }

    private function handleRequest()
    {
        if (isset($_GET['id'])) 
        {
            $this->auth->login2(explode(",", $_GET['id']));
            header('Location: index.php');
            die();            
        }
    }

    public function printContent()
    {

        if ($this->auth->IsAuthenticated())
        {    
            ?>
            <p>User is currently set to:</p>
            <?    
            foreach($this->auth->userIDs as $userID) {
                ?><img src="<?=getImageURL($userID,100,100,null,null,null,null,null,"Users") ?>" width="100" height="100" /><?php
            }
        }
        ?>
        <p>Click on name to set user currently watching.</p>
        <p><a name='1' href="login.php?id=<?= $this->users[1]->Id ?>,<?= $this->users[0]->Id ?>"><?= $this->users[1]->Name ?>,<?= $this->users[0]->Name ?></a></p>
        <?
        foreach ($this->users as $user) {
            ?>
            <p><a href="login.php?id=<?= $user->Id ?>"><?= $user->Name ?></a></p>
            <?
        }
    }
}

$page = new LoginPage();
$page->render();
?>