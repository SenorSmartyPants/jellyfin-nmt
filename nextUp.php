<?php
include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$folderType = ItemType::COLLECTIONFOLDER;
$collectionType = CollectionType::TVSHOWS;
$topParentName = $collectiontypeNames[$collectionType];

$pageObj = new ListingsPage('');
if (isset($_GET['rewatching'])) {
    $rewatching = true;
    $pageObj->title = 'Rewatching';
} else {
    $rewatching = null;
    $pageObj->title = 'Next Up';
}

$itemsAndCount = getNextUp($indexStyle->Limit, ($page - 1) * $indexStyle->Limit, $rewatching);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();

?>