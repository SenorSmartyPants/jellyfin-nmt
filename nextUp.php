<?php

include 'listings.php';

$useSeasonNameForMenuItems = false;

$indexStyle = new IndexStyle();

$items = getNextUp($indexStyle->Limit)->Items;

setIndexStyle(null, count($items));

printHeadEtc();

printNavbarAndPosters("Next Up", $items);

printTitleTable();

printFooter();

?>