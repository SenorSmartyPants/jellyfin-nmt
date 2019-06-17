<?php

include 'index.php';

$libraryBrowse = true;
$useSeasonImage = false;


$page = $_GET["page"];
$page = $page ?: 1;

$parentId = $_GET["parentId"];

$collectionType = $_GET["CollectionType"];

$name = $_GET["Name"];

$genres = $_GET["Genres"];
$nameStartsWith = $_GET["Title"];
$ratings = $_GET["Ratings"];
$tags = $_GET["Tags"];
$years = $_GET["Years"];

$QSBase = "?parentId=" . $parentId . "&CollectionType=" . $collectionType . "&Name=" . urlencode($name) . 
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
    default:
        $recursive = true;
        $type = "series,movie";
        break;
}

$Limit = 27;
$itemsAndCount = getItems($parentId, ($page - 1) * $Limit, $Limit, $type, $recursive, 
    $genres, $nameStartsWith, $ratings, $tags, $years);
$items = $itemsAndCount->Items;

$numPages = ceil($itemsAndCount->TotalRecordCount / $Limit);

/* features needed
Series name only for menuitem title


*/

setIndexStyle(IndexStyleEnum::PosterPopupDynamic, count($items));

printHeadEtc();

printNavbarAndPosters($name, $items);

printTitleTable($page, $numPages);

printFooter();

?>