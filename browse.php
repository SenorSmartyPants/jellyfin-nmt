<?php
include 'listings.php';

$libraryBrowse = true;
$useSeasonImage = false;

$pageObj = new ListingsPage('');
$pageObj->backdrop = $backdrop;

overrideIndexStyle($folderType, $collectionType);

$params = new UserItemsParams();
$params->StartIndex = ($page - 1) * $indexStyle->Limit;
$params->Limit = $indexStyle->Limit;

//common options
$recursive = false;
$type = null;

switch ($folderType) {
    case ItemType::PLAYLIST:
        $recursive = true;
        $params->setSortByDefault(null);
        break;

    case ItemType::BOXSET:
        $params->setSortByDefault(null);
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


if ($collectionType === 'search' || empty($params->ParentID)) {
    //exclude season and episodes to match JF behavior
    $excludeItemTypes = ItemType::SEASON . ',' . ItemType::EPISODE;
    $recursive = true;
}

$params->IncludeItemTypes = $type;
$params->ExcludeItemTypes = $excludeItemTypes;
$params->Recursive = $recursive;

$params->setFromQueryString();

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
    "Runtime" => "Runtime"
];

$prettyFilter = [
    "IsFavorite" => "Favorites",
    "IsUnplayed" => "Unplayed",
    "IsPlayed" => "Played",
    "hasSpecialFeature" => "Extras",
    "hasSubtitles" => "Subtitles",
    "hasTrailer" => "Trailers",
    "hasThemeSong" => "Theme Song",
    "hasThemeVideo" => "Theme Video"
];

if (empty($name)) {
    //build name from parameters
    foreach ($filterCategories as $cat) {
        if (!empty($params->$cat)) {
            !empty($name) && $name .= ", ";
            if (strpos($cat, 'has') === 0) {
                $key = $cat;
            } else {
                $key = $params->$cat;
            }
            $name .= array_key_exists($key, $prettyFilter) ? $prettyFilter[$key] : $params->$cat;
        }
    }    
    if ((!empty($params->SortBy) && $params->SortBy != UserItemsParams::SORTNAME) || $params->SortOrder == UserItemsParams::DESC) {
        $name .= ', by ' . $prettySortBy[$params->SortBy];
        if ($params->SortOrder == UserItemsParams::DESC) {
            $name .= ' ' . $params->SortOrder;
        }
    }
}

if (!empty($topParentName) && $topParentName != $name) {
    $pageObj->title = $topParentName;
    if (!empty($name)) {
        $pageObj->title .= ' - ';
    }
}
$pageObj->title .= $name;

$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>