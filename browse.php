<?php

include 'listings.php';

$libraryBrowse = true;
$useSeasonImage = false;


$page = $_GET["page"];
$page = $page ?? 1;

$parentId = $_GET["parentId"];

$folderType = $_GET["FolderType"];
$collectionType = $_GET["CollectionType"];

$name = $_GET["Name"];

$genres = $_GET["Genres"];
$nameStartsWith = $_GET["Title"];
$ratings = $_GET["Ratings"];
$tags = $_GET["Tags"];
$years = $_GET["Years"];

$QSBase = "?parentId=" . $parentId . "&FolderType=" . $folderType . "&CollectionType=" . $collectionType . "&Name=" . urlencode($name) . 
    "&Genres=" . urlencode($genres) . "&Title=" . urlencode($nameStartsWith) . 
    "&Ratings=" . $ratings . "&Tags=" . urlencode($tags) .
    "&Years=" . $years . "&page=";

switch ($collectionType) {
    case "tvshows":
        $recursive = true;
        $type = "series";
        break;
    case "movies":
        $recursive = true;
        $type = "movie";
        break;
    case "search": //searching from categories page
        $recursive = true;
        //exclude season and episodes to match JF behavior
        $type = "movie,series,boxset";
        break;
    default: //browsing. boxsets,music,games,books,musicvideos,homevideos,livetv,channels
        $recursive = false;
        $type = null;
        break;
}

//paging with dynamic style causes issues
setDataLimits($folder_collection_listing_style[$folderType .'/'. $collectionType]);

$itemsAndCount = getItems($parentId, ($page - 1) * $Limit, $Limit, $type, $recursive, 
    $genres, $nameStartsWith, $ratings, $tags, $years);
$items = $itemsAndCount->Items;

$numPages = ceil($itemsAndCount->TotalRecordCount / $Limit);

//items on page count, not total
getIndexStyle($folderType, $collectionType,
    $page < $numPages ? $Limit : $itemsAndCount->TotalRecordCount % $Limit);

printHeadEtc();

printNavbarAndPosters($name, $items);

printTitleTable($page, $numPages);

printFooter();

?>