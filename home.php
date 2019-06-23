<?php

include 'listings.php';

$useSeasonNameForMenuItems = true;

//$Limit = 27;

$items = getUsersViews()->Items;

/* features needed
Series name only for menuitem title
*/
setIndexStyle(IndexStyleEnum::PosterPopupDynamic, count($items));

//960x540
//(1093 - (n+1)*4) / n = w
//larger thumbnails need more padding I think

if (count($items) <= 6) {
    //3x3
    $thumbnailsWidth = 360;
    $thumbnailsHeight = 202;
    $popupWidth = 360;
    $popupHeight = 202;
    $nbThumbnailsPerPage = 6;
    $nbThumbnailsPerLine = 3; 
} else {
    //4x4
    $thumbnailsWidth = 268;
    $thumbnailsHeight = 151;
    $popupWidth = 268;
    $popupHeight = 151;
    $nbThumbnailsPerPage = 12;
    $nbThumbnailsPerLine = 4;  
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