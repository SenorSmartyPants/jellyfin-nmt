<?php
include 'listings.php';

$GroupItems = 'true';
$Limit = 27;

$collectionTypeToItemType = array(CollectionType::TVSHOWS => ItemType::EPISODE, 
    CollectionType::MOVIES => ItemType::MOVIE, CollectionType::BOXSETS => ItemType::BOXSET,
    CollectionType::PLAYLISTS => ItemType::PLAYLIST, CollectionType::MUSICVIDEOS => ItemType::MUSICVIDEO);

$collectionType = htmlspecialchars($_GET['collectionType']);
$folderType = ItemType::COLLECTIONFOLDER;

$items = getLatest($collectionTypeToItemType[$collectionType], $Limit, $topParentId);

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopupDynamic);
setNumPagesAndIndexCount(count($items));

$pageObj->title = $name . ' ' . $topParentName;
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>