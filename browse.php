<?php

include 'index.php';

$libraryBrowse = true;
$useSeasonImage = false;


$page = $_GET["page"];
$page = $page ?: 1;

$parentId = $_GET["parentId"];

$collectionType = $_GET["CollectionType"];

$name = $_GET["Name"];

$QSBase = "?parentId=" . $parentId . "&CollectionType=" . $collectionType . "&Name=" . $name . "&page=";

switch ($collectionType) {
    case "tvshows":
        $recursive = true;
        $type = "series";
        break;
    case "movies":
        $recursive = true;
        $type = "movie";
        break;
}

$Limit = 27;
$itemsAndCount = getItems($parentId, ($page - 1) * $Limit, $Limit, $type, $recursive);
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