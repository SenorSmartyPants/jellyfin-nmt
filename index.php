<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = true;

$items = getUsersViews()->Items;

/* features needed
Series name only for menuitem title
*/
$indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup);

//960x540
//(1096 - (n+1)*cellspacing) / n = w
//or 
//(1096 - (2n)*cellpadding) / n = w
//larger thumbnails need more padding I think

//thumbnail/.8 = popup dimensions
//can be whatever I want!
if (count($items) <= 6) {
    //3x2
    $indexStyle->thumbnailsWidth = 341;
    $indexStyle->thumbnailsHeight = 191;
    $indexStyle->popupWidth = 426;
    $indexStyle->popupHeight = 238;
    $indexStyle->Limit = 6;
    $indexStyle->nbThumbnailsPerLine = 3;
} else {
    //4x3
    $indexStyle->thumbnailsWidth = 254;
    $indexStyle->thumbnailsHeight = 143;
    $indexStyle->popupWidth = 318;
    $indexStyle->popupHeight = 179;
    $indexStyle->Limit = 12;
    $indexStyle->nbThumbnailsPerLine = 4; 
}
$indexStyle->moviesTableCellspacing = 16;
$indexStyle->offsetY = 156;
$indexStyle->ImageType = ImageType::PRIMARY;

setNumPagesAndIndexCount(count($items));

class IndexPage extends ListingsPage
{
    private $continueWatching;
    //private $rewatching;

    public function __construct($title)
    {
        parent::__construct($title, false);
        //check if there are continueWatching items
        $this->continueWatching = (getResume(1)->TotalRecordCount) > 0;
        if ($this->continueWatching) {
            $this->onloadset = 'continue';
        } else {
            $this->onloadset = 'nextup';
        }
        //check if there are rewatching items
        //$this->rewatching = (getNextUp(1,0,true)->TotalRecordCount) > 0;
    }

    public function printContent()
    {
        if ($this->continueWatching) {
            echo '<a href="continueWatching.php" name="continue">Continue Watching ></a>&nbsp;';
        } 
    ?>
        <a href="nextUp.php" name="nextup">Next Up ></a>
    <?
/*         if ($this->rewatching) {
            echo '<a href="nextUp.php?rewatching" name="rewatching">Rewatching ></a>&nbsp;';
        }  */
    ?>
        <br clear="all"/>
        <a href="latest.php?type=<?= ItemType::EPISODE ?>">Latest TV Shows ></a>
        <br clear="all"/>
        <a href="latest.php?type=<?= ItemType::MOVIE ?>">Latest Movies ></a>
        <br clear="all"/>
        <a href="categoriesHTML.php">Categories ></a>
        <br clear="all"/>
    <?
        $this->printPosterTable($this->items);
    }
}

$pageObj = new IndexPage('Home');
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>