<?php

include 'listings.php';

$useSeasonNameForMenuItems = false;
$Limit = 27;

$items = getNextUp($Limit)->Items;

setIndexStyle(null, count($items));

printHeadEtc();

printNavbarAndPosters("Next Up", $items);

printTitleTable();

printFooter();

?>