<?php
include_once 'IndexStyles.php';
include_once 'config_listings.php';
include_once 'secrets.php';
include_once 'menuItems.php';
include_once 'page.php';

$page = $_GET["page"];
$page = $page ?? 1;

$parentId = $_GET["parentId"];

$folderType = $_GET["FolderType"];
$collectionType = $_GET["CollectionType"];

$name = $_GET["Name"];

$genres = $_GET["Genres"];
$nameStartsWith = $_GET["Title"];
$ratings = $_GET["Ratings"];
$tags = $_GET["Tags"];
$years = $_GET["Years"];

$backdropId = $_GET["backdropId"];
$backdrop = getBackdropIDandTag(null, $backdropId);

$QSBase = "?parentId=" . $parentId . "&FolderType=" . $folderType . "&CollectionType=" . $collectionType . "&Name=" . urlencode($name) . 
    "&Genres=" . urlencode($genres) . "&Title=" . urlencode($nameStartsWith) . 
    "&Ratings=" . $ratings . "&Tags=" . urlencode($tags) .
    "&Years=" . $years . "&backdropId=" . $backdropId . "&page=";

class ListingsPage extends Page
{
    public $items;
    public $menuItems = array();

    public function printJavascript() 
    {
?>
        <script type="text/javascript" src="js/listings.js"></script>
<?
    }

    public function printHead()
    {
        $this->onload = "initpage(" . ((isset($this->indexStyle->popupHeight) || isset($this->indexStyle->popupWidth)) ? 'true' : 'false') . ")";
        parent::printHead();

        //speed dial TVIDs
        $name = "Title";
        $titleLetters = range("A","Z");
        array_unshift($titleLetters,"#");
        $tvids = [1,2,22,222,3,33,333,4,44,444,5,55,555,6,66,666,7,77,777,7777,8,88,888,9,99,999,9999];

        for ($i=0; $i < count($titleLetters); $i++) { 
            $url = categoryBrowseURL($name, $titleLetters[$i]);
?>
            <a href="<?= $url ?>" tvid="<?= $tvids[$i] ?>"></a>
<?
        }

    }

    public function printContentWrapperStart() 
    {
?>
        <table border="0" cellpadding="0" cellspacing="0" align="<?= $this->indexStyle->moviesTableAlign ?>">
        <tr valign="<?= $this->indexStyle->moviesTableVAlign ?>"><td class="posterTableParent">
<? 
    }

    public function printContent()
    {
        $this->printPosterTable($this->items);  
    }

    function printPosterTable($items)
    {
        global $lastRow;
    
        $lastRow = ceil(count($items) / $this->indexStyle->nbThumbnailsPerLine);
        ?>
        <table class="movies" border="0" cellpadding="<?= $this->indexStyle->moviesTableCellpadding ?? 0 ?>" cellspacing="<?= $this->indexStyle->moviesTableCellspacing ?? 0 ?>" align="<?= $this->indexStyle->moviesTableAlign ?>">
            <?
            $i = 0;
            foreach ($items as $item) {
                //first item in row
                if (isStartOfRow($i)) {
                    echo "<tr>";
                }
                $menuItem = getMenuItem($item);
                if ($menuItem) {
                    printPosterTD($menuItem, 0, $i, ceil(($i + 1) / $this->indexStyle->nbThumbnailsPerLine));
                    //add menuItem to menuItems list for later
                    array_push($this->menuItems, $menuItem);
    
                    //last item in row
                    if (isEndOfRow($i)) {
                        echo "</tr>";
                    }
    
                    $i++;
                }
            }
            ?>
        </table>
        <?
    }    

    public function printFooter()
    {
?>
            <div id="popupWrapper">
<?
        //print popups last of all, so they have highest z-index on NMT
        foreach ($this->menuItems as $key => $menuItem) {
            printTitleAndSubtitle($menuItem, 0, $key);
        }
        if (isset($this->indexStyle->popupHeight) || isset($this->indexStyle->popupWidth)) {
            //print popups last of all, so they have highest z-index on NMT
            foreach ($this->menuItems as $key => $menuItem) {
                printPopup($menuItem, 0, $key);
            }
        }
?>
            </div>
<?
        parent::printFooter();
    }
}

$pageObj = new ListingsPage('');
$pageObj->backdrop = $backdrop;


function setNumPagesAndIndexCount($totalRecordCount)
{
    global $page, $numPages, $indexStyle, $pageObj;
    $pageObj->indexStyle = $indexStyle;
    $numPages = ceil($totalRecordCount / $indexStyle->Limit);
    $indexStyle->setIndexCount($page < $numPages ? 
                $indexStyle->Limit : 
                $totalRecordCount - ($indexStyle->Limit * ($page-1)));
}

function isStartOfRow($position)
{
    global $indexStyle;
    return ($position % $indexStyle->nbThumbnailsPerLine == 0);
}

function isLastRow($row)
{
    global $lastRow;
    return ($row == $lastRow);
}

function isEndOfRow($position)
{
    global $indexStyle;
    return ($position % $indexStyle->nbThumbnailsPerLine == $indexStyle->nbThumbnailsPerLine - 1);
}

function printTitleAndSubtitle($menuItem, $gap, $position)
{
    $placement = $position + $gap + 1; //$position is zero based
?>
        <div id="title<?= $placement ?>" class="hidden"><?= $menuItem->Name ?></div>
        <div id="subtitle<?= $placement ?>" class="hidden"><?= $menuItem->Subtitle ?></div>
<?
}

function printPopup($menuItem, $gap, $position)
{
    global $indexStyle;
    $placement = $position + $gap + 1; //$position is zero based

    if ($menuItem->PosterURL) {
?>
        <img id="imgDVD<?= $placement ?>" src="<?= $menuItem->PosterURL ?>" <?= $indexStyle->hoverFrame ? null : 'onclick="openLink(' . $placement . ');"' ?> />
<?php
        if ($indexStyle->hoverFrame) {
?>
        <img id="frmDVD<?= $placement ?>" src="<?= $indexStyle->hoverFrame ?>" onclick="openLink(<?= $placement ?>);" />
<?php            
        }
    }
}

//gap is for skipping rows, in sets on the bottom
function printPosterTD($menuItem, $gap, $position, $row)
{
    global $indexStyle;
    global $page, $numPages;
    $placement = $position + $gap + 1; //$position is zero based
    ?>
    <td align="center" <? 
    if (!$menuItem->PosterURL) { 
        ?>class="defaultCardBackground<?= ($position % 5) + 1 ?>" width="<?= $indexStyle->thumbnailsWidth ?>" height="<?= $indexStyle->thumbnailsHeight ?>"<?
    } ?> >
        <a href="<?= $menuItem->DetailURL ?>" <?= $menuItem->OnDemandTag ?? null ?> onclick="return prompter('TV-14 hardcode')" TVID="<?= $placement ?>" name="<?= $placement ?>" onmouseover="show(<?= $placement ?>)" onfocus="show(<?= $placement ?>)" onblur="hide(<?= $placement ?>)" 
        id="<?= $placement ?>" 
<?php

    //start of row
    if (isStartOfRow($placement - 1)) {
        if ($placement == 1) {
            echo "onkeyleftset=\"pgupload\"";
        } else {
            echo "onkeyleftset=\"" . ($placement - 1) . "\"";
        }
    }

    //end of row
    if (isEndOfRow($placement - 1)) {
        if ($placement != $indexStyle->Limit) {
            if (isLastRow($row)) {
                if ($numPages == 1) {
                    //go to first item
                    echo 'onkeyrightset="1"';
                }
            } else {
                echo "onkeyrightset=\"" . ($placement + 1) . "\"";
            }
        } else {
            echo "onkeyrightset=\"pgdnload\"";
        }
    }


    //last row
    if (isLastRow($row)) {
        if ($numPages == 1) {
            //go to top row
            $topofcolumn = $placement % $indexStyle->nbThumbnailsPerLine;
            $topofcolumn = ($topofcolumn == 0) ? $indexStyle->nbThumbnailsPerLine : $topofcolumn;
            echo " onkeydownset=\"" . $topofcolumn . "\" ";
        } else {
            //down arrow goes to next page
            echo " onkeydownset=\"pgdnload\"";
        }
    }
?>>
<?
    if ($menuItem->PosterURL) {
?>
        <img src="<?= $menuItem->PosterURL ?>" width="<?= $indexStyle->thumbnailsWidth ?>" height="<?= $indexStyle->thumbnailsHeight ?>" onfocussrc="images/wall/transparent.png" />
<?   
    } else {
        echo $menuItem->Name;
    }
?></a>
    </td>
<?php
}
?>