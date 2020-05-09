<?php
const PCMENU = false;

function printBaseHeadEtc($onloadset = null, $additionalCSS = null, $title = null, $InitJSFunction = null, 
    $onload = null, $focuscolor="#00a4dc")
{
    global $theme_css, $indexStyle;
    global $backdrop;

    $onloadset = $onloadset ?? "1";
    ?>
    <html>

    <head>
        <link rel="shortcut icon" href="<?= getFavIconURL() ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?= $title ? $title . ' - ' : null ?>Jellyfin NMT</title>

<?
        if (isset($indexStyle) && null !== $indexStyle->cssFile()) {
            //don't add any styles before the following. JS show/hide code depends on this these being first
?>
        <link rel="StyleSheet" type="text/css" href="<?= $indexStyle->cssFile() ?>"/>
<?
        }
?>
        <link rel="StyleSheet" type="text/css" href="css/base.css" />
        <link rel="StyleSheet" type="text/css" href="css/themes/<?= $theme_css ?>" />
<?
        if ($additionalCSS) {
?>        <link rel="StyleSheet" type="text/css" href="css/<?= $additionalCSS ?>"/>
<?
        }
        
        if (PCMENU) {
            echo '        <link rel="StyleSheet" type="text/css" href="/New/Jukebox/no_nmt.css" media="screen" />' . "\n";
        }
        
        if ($InitJSFunction) {
            call_user_func($InitJSFunction);
        }
?>      
    </head>

    <body bgproperties="fixed" onloadset="<?= $onloadset ?>" 
    FOCUSTEXT="#dddddd" focuscolor="<?= $focuscolor ?>" 
    bgcolor="#000000"
    <? if ($onload) 
    { 
        ?> onload="<?= $onload ?>" <?
    }
    ?>
    <? if ($backdrop->Id) 
    { 
        ?> background="<?= getImageURL($backdrop->Id, 720, 1280, "Backdrop", null, null, $backdrop->Tag) ?>"<?   
    }
    ?>>
<?
}


?>