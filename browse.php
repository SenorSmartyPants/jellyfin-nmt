<?php
include 'listings.php';

$libraryBrowse = true;
$useSeasonImage = false;

//common options
$recursive = false;
$type = null;
$sortBy = 'SortName';

switch ($folderType) {
    case ItemType::PLAYLIST:
        $recursive = true;
        $sortBy = null;
        break;

    case ItemType::BOXSET:
        $sortBy = null;
        break;
    
    default:
        if ($collectionType == CollectionType::TVSHOWS) {
            $type = ItemType::SERIES;
        } elseif ($collectionType == CollectionType::MOVIES) {
            $type = ItemType::MOVIE;
        }
}


//if filtering parameters are set, then search recursively
if ($collectionType === 'search' || !empty($Genres) || !empty($Title) || !empty($Ratings) || !empty($Tags) || !empty($Years)) {
    //exclude season and episodes to match JF behavior
    $excludeItemTypes = ItemType::SEASON . ',' . ItemType::EPISODE;
    $recursive = true;
}

//paging with dynamic style causes issues
//$indexStyle = new IndexStyle($folder_collection_listing_style[$folderType .'/'. $collectionType]);
overrideIndexStyle($folderType, $collectionType);

$params = new UserItemsParams();
$params->ParentID = $parentId;
$params->StartIndex = ($page - 1) * $indexStyle->Limit;
$params->Limit = $indexStyle->Limit;
$params->IncludeItemTypes = $type;
$params->Recursive = $recursive;
$params->Genres = $Genres;
$params->NameStartsWith = $Title;
$params->OfficialRatings = $Ratings;
$params->Tags = $Tags;
$params->Years = $Years;
$params->SortBy = $sortBy;
$params->ExcludeItemTypes = $excludeItemTypes;

$itemsAndCount = getItems($params);

$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

if (!empty($topParentName) && $topParentName != $name) {
    $pageObj->title = $topParentName . ' - ';
}
$pageObj->title .= $name;
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>