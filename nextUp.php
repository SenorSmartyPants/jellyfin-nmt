<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$folderType = ItemType::COLLECTIONFOLDER;
$collectionType = CollectionType::TVSHOWS;
$topParentName = $collectiontypeNames[$collectionType];

$pageObj = new ListingsPage('');
$pageObj->title = 'Next Up';

$rewatching = null;


$pageObj->dynamicGridPage = $dynamicGridPage;
if (!$pageObj->dynamicGridPage) {
    $StartIndex = ($page - 1) * $indexStyle->Limit;
    $Limit = $indexStyle->Limit;
}

$itemsAndCount = getNextUp($Limit, $StartIndex, $rewatching);
$items = $itemsAndCount->Items;

$pageObj->setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();



