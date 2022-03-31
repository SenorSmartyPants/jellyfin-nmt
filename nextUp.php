<?php
include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$folderType = ItemType::COLLECTIONFOLDER;
$collectionType = CollectionType::TVSHOWS;
$topParentName = $collectiontypeNames[$collectionType];

$pageObj = new ListingsPage('');
$pageObj->title = 'Next Up';

$rewatching = null;

$itemsAndCount = getNextUp($indexStyle->Limit, ($page - 1) * $indexStyle->Limit, $rewatching);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();

?>