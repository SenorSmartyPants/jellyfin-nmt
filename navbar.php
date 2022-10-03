<?php

class Navbar
{
    const USERIMAGESIZE = 45;

    public static function getHeight()
    {
        return 56;
    }

    public static function printNavbar($title, $auth)
    {
        ?>
        <table class="main" border="0" cellpadding="0" cellspacing="0">
            <tr valign="top">
                <td class="indexname" id="indexmenuleft" align="left" valign="top"><?= $title ?></td>
                <td id="indexmenuright" align="right">&nbsp;
                <a onkeydownset="1" href="login.php"><?php
        if ($auth->IsAuthenticated()) {
            foreach ($auth->userIDs as $userID) {
                ?><img src="<?=getImageURL($userID, new ImageParams(self::USERIMAGESIZE, self::USERIMAGESIZE), null, 'Users') ?>" width="<?= self::USERIMAGESIZE ?>" height="<?= self::USERIMAGESIZE ?>" /><?php
            }
        }
    ?></a>&nbsp;
                </td>
            </tr>
        </table>
<?php
    }

}
