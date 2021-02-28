<?php
include 'listings.php';

$GroupItems = "true";
$Limit = 27;

$items = getLatest(htmlspecialchars($_GET["type"]), $Limit);

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopupDynamic);
setNumPagesAndIndexCount(count($items));

switch ($_GET["type"]) {
    case ItemType::EPISODE:
        $Title = "Latest TV";
        $folderType = ItemType::COLLECTIONFOLDER;
        $collectionType = CollectionType::TVSHOWS;
        break;

    case ItemType::MOVIE:
        $Title = "Latest Movies";
        $folderType = ItemType::COLLECTIONFOLDER;
        $collectionType = CollectionType::MOVIES;
        break;
    
    default:
        $Title = "Latest";
        break;
}

$pageObj->title = $Title;
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>