<?php
include_once 'page.php';
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

$pageObj = new Page($Title);
$pageObj->indexStyle = $indexStyle;

printHeadEtc();

printNavbarAndPosters($items);

$pageObj->printTitleTable();

printFooter();

?>