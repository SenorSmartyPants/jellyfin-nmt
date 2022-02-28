<?php
include 'listings.php';

$libraryBrowse = true;
$useSeasonImage = false;

//common options
$recursive = false;
$type = null;

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
            $recursive = true;
        } elseif ($collectionType == CollectionType::MOVIES) {
            $type = ItemType::MOVIE;
            $recursive = true;
        }
}


//if filtering parameters are set, then search recursively
//or no parentID set
if ($collectionType === 'search' || !empty($pageObj->cbp->searchTerm) || empty($parentId)) {
    //exclude season and episodes to match JF behavior
    $excludeItemTypes = ItemType::SEASON . ',' . ItemType::EPISODE;
    $recursive = true;
}

$pageObj = new ListingsPage('');
$pageObj->backdrop = $backdrop;

//paging with dynamic style causes issues
//$indexStyle = new IndexStyle($folder_collection_listing_style[$folderType .'/'. $collectionType]);
overrideIndexStyle($folderType, $collectionType);

$params = new UserItemsParams();
$params->ParentID = $parentId;
$params->StartIndex = ($page - 1) * $indexStyle->Limit;
$params->Limit = $indexStyle->Limit;
$params->IncludeItemTypes = $type;
$params->Recursive = $recursive;
$params->SortBy = $sortBy;
$params->SortOrder = $sortOrder;
$params->collapseBoxSetItems = $collapseBoxSetItems;
$params->ExcludeItemTypes = $excludeItemTypes;
$categoryName = $pageObj->cbp->categoryName;
$params->$categoryName = $pageObj->cbp->searchTerm;

$itemsAndCount = getItems($params);

$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);


$prettySortBy = [
    "SortName" => "Name",
    "CommunityRating" => "Community Rating",
    "CriticRating" => "Critic Rating",
    "DateCreated" => "Date Added",
    "DatePlayed" => "Date Played",
    "OfficialRating" => "Parental Rating",
    "PlayCount" => "Play Count",
    "PremiereDate" => "Release Date",
    "Runtime" => "Runtime",
];

if (!empty($topParentName) && $topParentName != $name) {
    $pageObj->title = $topParentName;
    if (!empty($name)) {
        $pageObj->title .= ' - ';
    }
}
$pageObj->title .= $name;
if ((!empty($sortBy) && $sortBy != SORTNAME) || $sortOrder == DESC) {
    $pageObj->title .= ', by ' . $prettySortBy[$sortBy];
    if ($sortOrder == DESC) {
        $pageObj->title .= ' ' . $sortOrder;
    }
}

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>