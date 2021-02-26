<?php
include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$itemsAndCount = getNextUp($indexStyle->Limit, ($page - 1) * $indexStyle->Limit);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$collectionType = 'tvshows';
$pageObj->title = 'Next Up';
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();

?>