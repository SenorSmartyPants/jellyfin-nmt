<?php

include 'index.php';

$GroupItems = "true";
$Limit = 27;

$items = getLatest($Limit);

setIndexStyle(IndexStyleEnum::PopupPosterDynamic, count($items));

printHeadEtc();

switch ($_GET["type"]) {
    case 'episode':
        $Title = "Latest TV";
        break;

    case 'movie':
        $Title = "Latest Movies";
        break;
    
    default:
        $Title = "Latest";
        break;
}

printNavbarAndPosters($Title, $items);

printTitleTable();

printFooter();

?>