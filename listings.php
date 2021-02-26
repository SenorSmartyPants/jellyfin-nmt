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

    protected $filters;
    protected $titleLetters;
    protected $singleLetterTVIDs;
    protected $letterToNumber;

    public function __construct($title)
    {
        parent::__construct($title);  
        
        $this->titleLetters = range("A","Z");
        array_unshift($this->titleLetters,"#");

        $this->singleLetterTVIDs = array("#"=>"1", 
            "A"=>"2", "B"=>"22", "C"=>"222", 
            "D"=>"3", "E"=>"33", "F"=>"333",
            "G"=>"4", "H"=>"44", "I"=>"444",
            "J"=>"5", "K"=>"55", "L"=>"555",
            "M"=>"6", "N"=>"66", "O"=>"666",
            "P"=>"7", "Q"=>"77", "R"=>"777", "S"=>"7777",
            "T"=>"8", "U"=>"88", "V"=>"888",
            "W"=>"9", "X"=>"99", "Y"=>"999", "Z"=>"9999"
        );
    }

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
    }

    private function toSingleLetterNumberpad($str)
    {
        return $this->singleLetterTVIDs[$str];
    }

    private function toNumberpad($str, $length = 3)
    {
        //remove spaces
        $str = str_replace(' ', '', $str);
        return substr(str_ireplace($this->titleLetters, $this->letterToNumber, $str), 0, $length);
    }

    private function printTVIDLink($url, $tvid)
    {
        print("<a href=\"$url\" tvid=\"$tvid\"></a>\n");
    }

    private function printTVIDLinks($name, $items, $getTVID)
    {
        global $collectionType;
        foreach ($items as $item) {
            $this->printTVIDLink(categoryBrowseURL($name, $item, $collectionType), call_user_func($getTVID, $item)); 
        }
    }

    private function printSpeedDial()
    {
        //TODO: check for TVID collision
        //speed dial TVIDs
        $this->printTVIDLinks("Title", $this->titleLetters, 'ListingsPage::toSingleLetterNumberpad');
    }

    public function printNavbar()
    {
        parent::printNavbar();
        $this->printSpeedDial();
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