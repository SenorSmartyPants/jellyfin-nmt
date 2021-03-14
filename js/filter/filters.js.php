<?php
include_once '../../categories.php';

$itemType = $_GET["itemType"];
$topParentId = $_GET["topParentId"];

if (empty($itemType) && empty($topParentId))
{
    $pageObj = new CategoriesJSPage();
} else {
    if (!empty($itemType)) {
        $itemTypes = array(htmlspecialchars($itemType));
    }
    $pageObj = new CategoriesJSPage($itemTypes, $topParentId);
}

$pageObj->render();
?>