<?php

include 'index.php';

$libraryBrowse = true;
$useSeasonImage = false;


$page = $_GET["page"];
$page = $page ? $page : 1;

$parentId = $_GET["parentId"];

$collectionType = $_GET["CollectionType"];



switch ($collectionType) {
    case "tvshows":
        $recursive = false;
        $type = "series";
        break;
    case "movies":
        $recursive = true;
        $type = "movie";
        break;
}

$Limit = 27;
$items = getItems($parentId, ($page - 1) * $Limit, $Limit, $type, $recursive)->Items;

/* features needed
Series name only for menuitem title


*/

setIndexStyle(IndexStyleEnum::PosterPopupDynamic, count($items));

printHeadEtc();

printNavbarAndPosters($_GET["Name"], $items);

printTitleTable();

printFooter();

?>