<?php
include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$itemsAndCount = getResume($indexStyle->Limit, ($page - 1) * $indexStyle->Limit);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj = new ListingsPage('Continue Watching', false);
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>