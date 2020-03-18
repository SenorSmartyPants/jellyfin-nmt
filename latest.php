<?php

include 'listings.php';

$GroupItems = "true";
$Limit = 27;

$items = getLatest($Limit);

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopupDynamic);
setNumPagesAndIndexCount(count($items));

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

printHeadEtc(null, null, $Title);

printNavbarAndPosters($Title, $items);

printTitleTable();

printFooter();

?>