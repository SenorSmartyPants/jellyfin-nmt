<?php

include 'index.php';

$useSeasonNameForMenuItems = false;
$Limit = 27;

$items = getNextUp($Limit)->Items;

if (count($items) > 12) {
    setIndexStyle(IndexStyleEnum::Popup9x3);
} else {
    setIndexStyle(IndexStyleEnum::Popup6x2);
}

printHeadEtc();

printNavbarAndPosters("Next Up", $items);

printTitleTable();

printFooter();

?>