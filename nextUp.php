<?php

include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle();

$items = getNextUp($indexStyle->Limit)->Items;

$indexStyle->setIndexCount(count($items));

printHeadEtc();

printNavbarAndPosters("Next Up", $items);

printTitleTable();

printFooter();

?>