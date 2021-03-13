<?php
include 'listings.php';

$libraryBrowse = true;
$useSeasonImage = false;

//common options
$recursive = true;
$type = null;

switch ($folderType) {
    case ItemType::PLAYLIST:
        $sortBy = null;
        break;

    case ItemType::BOXSET:
        $recursive = false;
        $sortBy = null;
        break;        
    
    default:
        $sortBy = 'SortName';
        switch ($collectionType) {
            case CollectionType::TVSHOWS:
                $type = ItemType::SERIES;
                break;
            case CollectionType::MOVIES:
                $type = ItemType::MOVIE;
                break;
            case CollectionType::BOXSETS:
                $type = ItemType::BOXSET;
                break;           
            case "search": //searching from categories page
                //exclude season and episodes to match JF behavior
                $type = "movie,series,boxset";
                break;
            default: //browsing. music,games,books,musicvideos,homevideos,livetv,channels
                $recursive = false;
                break;
        }
}

//paging with dynamic style causes issues
//$indexStyle = new IndexStyle($folder_collection_listing_style[$folderType .'/'. $collectionType]);
overrideIndexStyle($folderType, $collectionType);

$itemsAndCount = getItems($parentId, ($page - 1) * $indexStyle->Limit, $indexStyle->Limit, $type, $recursive, 
    $Genres, $Title, $Ratings, $Tags, $Years, null, null, $sortBy);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->title = $name;
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>