<?php

include 'listings.php';

$GroupItems = "true";
$Limit = 27;

$items = getLatest($Limit);

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopupDynamic);
setNumPagesAndIndexCount(count($items));

switch ($_GET["type"]) {
    case ItemType::EPISODE:
        $Title = "Latest TV";
        break;

    case ItemType::MOVIE:
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