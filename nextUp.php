<?php

include 'index.php';

$useSeasonNameForMenuItems = false;
$Limit = 27;

$items = getNextUp($Limit)->Items;

setIndexStyle(IndexStyleEnum::PosterPopupDynamic, count($items));

printHeadEtc();

printNavbarAndPosters("Next Up", $items);

printTitleTable();

printFooter();

?>