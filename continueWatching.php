<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = false;

$pageObj = new ListingsPage('Continue Watching', false);
$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$itemsAndCount = getResume($indexStyle->Limit, ($page - 1) * $indexStyle->Limit);
$items = $itemsAndCount->Items;

$pageObj->setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
