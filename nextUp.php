<?php

include 'index.php';

$useSeasonNameForMenuItems = false;
$Limit = 27;

//skin options
$nbThumbnailsPerPage = 27;
$nbThumbnailsPerLine = 9;


printHeadEtc();

printNavbar("Next Up");

printPosterTable(getNextUp($Limit)->Items);

printTitleTable();

printFooter();

?>