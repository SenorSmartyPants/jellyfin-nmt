<?php

include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup6x2);

$itemsAndCount = getNextUp($indexStyle->Limit, ($page - 1) * $indexStyle->Limit);
$items = $itemsAndCount->Items;

setNumPagesAndIndexCount($itemsAndCount->TotalRecordCount);

$pageObj->title = 'Next Up';

printHeadEtc();

printNavbarAndPosters($items);

$pageObj->printTitleTable($page, $numPages);

printFooter();

?>