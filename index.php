<?php

include 'listings.php';

$useSeasonNameForMenuItems = true;

$items = getUsersViews()->Items;

/* features needed
Series name only for menuitem title
*/
setIndexStyle(null, count($items));

//960x540
//(1096 - (n+1)*cellspacing) / n = w
//or 
//(1096 - (2n)*cellpadding) / n = w
//larger thumbnails need more padding I think

if (count($items) <= 6) {
    //3x3
    $thumbnailsWidth = 341;
    $thumbnailsHeight = 191;
    $popupWidth = 341;
    $popupHeight = 191;
    $nbThumbnailsPerPage = 6;
    $nbThumbnailsPerLine = 3;
    $moviesTableCellpadding = 0;
    $moviesTableCellspacing = 16;
} else {
    //4x4
    $thumbnailsWidth = 254;
    $thumbnailsHeight = 143;
    $popupWidth = 254;
    $popupHeight = 143;
    $nbThumbnailsPerPage = 12;
    $nbThumbnailsPerLine = 4; 
    $moviesTableCellpadding = 0;
    $moviesTableCellspacing = 16;
}

printHeadEtc("nextup");

printNavbarAndPostersHome("Home", $items);

printTitleTable();

printFooter();


function printNavbarAndPostersHome($title, $items)
{
    ?>
    
    <table border="0" cellpadding="0" cellspacing="0" align="left"><tr valign="top"><td height="598">
    <?php  
    printNavbar($title);
    ?>
    <a href="nextUp.php" name="nextup">Next Up ></a>
    <br clear="all"/>
    <a href="latest.php?type=episode">Latest TV Shows ></a>
    <br clear="all"/>
    <a href="latest.php?type=movie">Latest Movies ></a>
    <br clear="all"/>
    <a href="categories.php">Categories ></a>
    <br clear="all"/>
<?php 
    printPosterTable($items);
?>
    </td></tr>
    </table>

    
<?php    
}

?>