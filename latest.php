<?php

include 'index.php';

$GroupItems = "true";
$Limit = 27;

//skin options
$nbThumbnailsPerPage = 27;
$nbThumbnailsPerLine = 9;


printHeadEtc();

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

printNavbar($Title);

printPosterTable(getLatest($Limit));

printTitleTable();

printFooter();

?>