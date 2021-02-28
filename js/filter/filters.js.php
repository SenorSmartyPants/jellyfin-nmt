<?php
include_once '../../categories.php';

if (empty($_GET["itemType"]))
{
    $pageObj = new CategoriesJSPage();
} else {
    $itemTypes = array(htmlspecialchars($_GET["itemType"]));
    $pageObj = new CategoriesJSPage($itemTypes);    
}

$pageObj->render();
?>