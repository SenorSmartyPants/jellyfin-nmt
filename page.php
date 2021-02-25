<?php
include_once 'config.php';
include_once 'data.php';
include_once 'auth.php';
include_once 'navbar.php';
const PCMENU = true;

class Page 
{
    private $theme_css;
    protected $includeNavbar = true;
    protected $includeTitleTable = true;
    protected $authRequired = true;


    public $title;
    public $additionalCSS;
    public $InitJSFunction;
    public $onloadset = '1';
    public $onload;
    public $focuscolor = '#00a4dc';

    public $indexStyle;
    public $backdrop;

    public $auth;

    public function __construct($title)
    {
        global $theme_css;
        $this->theme_css = $theme_css;
        $this->title = $title;
        $this->auth = new Authentication();
        if ($this->authRequired && !$this->auth->IsAuthenticated())
        {
            //no accessToken
            //redirect to login page
            header('Location: login.php');
            die();
        }
    }

    public function printContentWrapperStart()
    {
?>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" align="left"><tr valign="top"><td height="<?= $this->getAvailableHeight() ?>">
<?
    }

    public function printContent() 
    {
        echo 'Override printContent to display something useful with render.';
    }

    public function printContentWrapperEnd()
    {
?>
        </td></tr></table>
<?
    }

    public function render()
    {
        global $page, $numPages;
        $this->printHead();
        $this->printNavbar();
        $this->printContentWrapperStart();
        $this->printContent();
        $this->printContentWrapperEnd();
        if ($this->includeTitleTable)
        {
            $this->printTitleTable($page, $numPages);
        }
        $this->printFooter();
    }

    public function getAvailableHeight()
    {
        // determine height available for main content
        return 671 - ($this->includeNavbar ? navbar::getHeight() : 0) - ($this->includeTitleTable ? 73 : 0);
    }

    public function printJavascript() 
    {
        if ($this->InitJSFunction) {
            call_user_func($this->InitJSFunction);
        }
    }

    public function printHead()
    {
        ?>
<html>
    <head>
        <link rel="shortcut icon" href="<?= getFavIconURL() ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?= $this->title ? $this->title . ' - ' : null ?>Jellyfin NMT</title>
<?
            if (isset($this->indexStyle) && null !== $this->indexStyle->cssFile()) {
                //don't add any styles before the following. JS show/hide code depends on this these being first
?>
        <link rel="StyleSheet" type="text/css" href="<?= $this->indexStyle->cssFile() ?>"/>
<?
            }
?>
        <link rel="StyleSheet" type="text/css" href="css/base.css" />
        <link rel="StyleSheet" type="text/css" href="css/themes/<?= $this->theme_css ?>" />
<?
            if ($this->additionalCSS) {
?>        <link rel="StyleSheet" type="text/css" href="css/<?= $this->additionalCSS ?>"/>
<?
            }
            
            if (PCMENU) {
                echo '        <link rel="StyleSheet" type="text/css" href="css/no_nmt.css" media="screen" />' . "\n";
            }
            
            $this->printJavascript();
?>
    </head>

    <body id="body" bgproperties="fixed" onloadset="<?= $this->onloadset ?>" FOCUSTEXT="#dddddd" focuscolor="<?= $this->focuscolor ?>" bgcolor="#000000" <?
        if ($this->onload) 
        { 
            ?>onload="<?= $this->onload ?>" <?
        }
?>
<?      if ($this->backdrop->Id) 
        { 
            ?> background="<?= getImageURL($this->backdrop->Id, 720, 1280, "Backdrop", null, null, $this->backdrop->Tag) ?>"<?   
        }
        ?>>
<?
    }

    public function printNavbar()
    {
        if ($this->includeNavbar)
        {
            navbar::printNavbar($this->title, $this->auth);
        }
    }

    public function printTitleTable($currentPage = 1, $numPages = 1)
    {
        global $QSBase, $include_jellyfin_logo_when_backdrop_present;
        global $backdropId;
?>
    <table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
        <tr>
            <td width="20%" valign="top"><? if ($include_jellyfin_logo_when_backdrop_present || !$backdropId) { ?><a href="index.php"><img src="<?= getLogoURL() ?>" height="47"/></a><? } ?></td>
            <td width="60%" align="center" valign="top">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" id="title" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center" id="subtitle" valign="top" class="secondaryText">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td width="20%" align="right" id="page" valign="top"><? 
        if ($numPages > 1) { 
            //pgup on first page, wraps around to last page
            $page = ($currentPage == 1) ? $numPages : (intval($currentPage) - 1);
            $url = filter_var($_SERVER['PHP_SELF'], FILTER_SANITIZE_STRING);
            echo "\n" . '               <a name="pgupload" onfocusload="" TVID="PGUP" href="' . $url . $QSBase . $page . "\" >" . $currentPage . "</a> / ";
            //pgdn on last page wraps to first page
            $page = ($currentPage == $numPages) ? 1 : (intval($currentPage) + 1);
            echo "\n" . '               <a name="pgdnload" onfocusload="" TVID="PGDN" href="' . $url . $QSBase . $page  . "\" >" . $numPages . "</a>";
        }
?>
            </td>
        </tr>
    </table>
<?php
    }

    public function printFooter()
    {
?>
        <div class="hidden" id="navigationlinks">
            <a href="index.php" TVID="HOME"></a>
            <a href="categories.php" TVID="info"></a>
        </div>
    </body>
</html>
<?php
    }
}
?>