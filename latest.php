<?php

include 'index.php';

$GroupItems = "true";
$Limit = 27;

$items = getLatest($Limit);

if (count($items) > 12) {
    setIndexStyle(IndexStyleEnum::Popup9x3);
} else {
    setIndexStyle(IndexStyleEnum::Popup6x2);
}


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