<?php
include_once '../../categories.php';

$itemType = $_GET["itemType"];
$topParentId = $_GET["topParentId"];
$topParentName = $_GET['topParentName'];

if (empty($itemType) && empty($topParentId))
{
    $pageObj = new CategoriesJSPage();
} else {
    if (!empty($itemType)) {
        $itemTypes = array(htmlspecialchars($itemType));
    }
    $pageObj = new CategoriesJSPage($itemTypes, $topParentId, $topParentName);
}

$pageObj->render();
?>