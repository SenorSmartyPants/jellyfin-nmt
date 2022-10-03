<?php
include_once '../../categories.php';

$itemType = htmlspecialchars($_GET["itemType"]);
$topParentId = htmlspecialchars($_GET["topParentId"]);
$topParentName = htmlspecialchars($_GET['topParentName']);

if (empty($itemType) && empty($topParentId)) {
    $pageObj = new CategoriesJSPage();
} else {
    if (!empty($itemType)) {
        $itemTypes = array($itemType);
    }
    $pageObj = new CategoriesJSPage($itemTypes, $topParentId, $topParentName);
}

$pageObj->render();
