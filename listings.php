<?php
include_once 'IndexStyles.php';
include_once 'config_listings.php';
include_once 'secrets.php';
include_once 'menuItems.php';
include_once 'page.php';
include_once 'filterMenu.php';
include_once 'utils/javascript.php';

$page = $_GET['page'];
$page = $page ?? 1;

$topParentId = $_GET['topParentId'];

$folderType = $_GET['folderType'];
$collectionType = $_GET['collectionType'];

$name = $_GET['name'];
$topParentName = $_GET['topParentName'];

$backdropId = $_GET['backdropId'];
$backdrop = getBackdropIDandTag(null, $backdropId);

class ListingsPage extends Page
{
    public $items;
    public $menuItems = array();
    public $cbp;
    public $QSBase;

    protected $renderFiltering;

    protected $filters;
    protected $titleLetters;
    protected $singleLetterTVIDs;
    protected $letterToNumber;
    public $dynamicGridPage = false;

    public function __construct($title, $renderFiltering = true)
    {
        global $topParentName, $topParentId, $parentId, $folderType, $collectionType;
        global $name, $backdropId;

        parent::__construct($title);

        $this->renderFiltering = $renderFiltering;
        if ($renderFiltering) {
            $this->titleLetters = range("A", "Z");
            array_unshift($this->titleLetters, "#");

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

            $this->additionalCSS = 'filter.css';
        }

        $this->cbp = new CategoryBrowseParams();

        $this->cbp->topParentName = $topParentName;
        $this->cbp->topParentId = $topParentId;
        $this->cbp->folderType = $folderType;
        $this->cbp->collectionType = $collectionType;

        $this->cbp->name = $name;
        $this->cbp->backdropId = $backdropId;

        $this->cbp->params->setFromQueryString();

        $this->QSBase = http_build_query($this->cbp);

    }

    public function printJavascript()
    {
        global $folderType, $collectionType;
        global $topParentId, $topParentName;
        global $page, $numPages;

?>
        <script type="text/javascript" src="js/listings.js"></script>
        <script type="text/javascript" src="js/uiUpdateUtils.js"></script>
        <script type="text/javascript">
        var iPage = <?= $this->dynamicGridPage ? $page : 1 ?>;
        var iPageSize = <?= $this->indexStyle->Limit ?>;
        var iNumPages = <?= $numPages ?>;
        var iRowSize = <?= $this->indexStyle->nbThumbnailsPerLine ?>;
        var iNumRows = <?= ceil(count($this->menuItems) / $this->indexStyle->nbThumbnailsPerLine) ?>;
        var asMenuTitle = <?= getJSArray(array_map(function ($i) { return $i->Name; }, $this->menuItems), true) ?>;
        var asMenuSubtitle = <?= getJSArray(array_map(function ($i) { return $i->Subtitle; }, $this->menuItems), true) ?>;
        var asMenuURL = <?= getJSArray(array_map(function ($i) { return $i->DetailURL; }, $this->menuItems), true) ?>;
<?
        if ($this->dynamicGridPage) {
?>
        var asMenuImage = <?= getJSArray(array_map(function ($i) { return $i->PosterURL; }, $this->menuItems), true) ?>;
<?
        }
?>
        </script>
<?
        if ($this->renderFiltering) {
            //clear some options that would be reset by filter
            $filterCBP = clone $this->cbp;
            $filterCBP->backdropId = null;
            $filterCBP->name = null;
            $filterCBP->params->ParentID = null;
?>
            <script type="text/javascript">
                var filteringBaseURL = '<?= categoryBrowseURLEx($filterCBP) ?>';
            </script>
            <script type="text/javascript" src="js/filter/filters.js.php?topParentId=<?= $topParentId ?>&topParentName=<?= $topParentName ?>&itemType=<?= mapFolderTypeToSingleItemType($folderType, $collectionType) ?>"></script>
            <script type="text/javascript" src="js/filter/filter.js"></script>
<?
        } else {
            //empty initMenu function to prevent JS error
?>
        <script type="text/javascript">function initMenu() {}</script>
<?
        }
    }

    public function printHead()
    {
        //initialize menuitems
        foreach ($this->items as $item) {
            $menuItem = getMenuItem($item);
            if ($menuItem) {
                array_push($this->menuItems, $menuItem);
            }
        }

        $this->onload .= "initpage(" . ((isset($this->indexStyle->popupHeight) || isset($this->indexStyle->popupWidth)) ? 'true' : 'false') . ")";
        parent::printHead();
    }

    private function toSingleLetterNumberpad($str)
    {
        return $this->singleLetterTVIDs[$str];
    }

    private function printTVIDLink($url, $tvid)
    {
        print("<a href=\"$url\" tvid=\"$tvid\" tabindex=\"-1\" ></a>\n");
    }

    private function printTVIDLinks($categoryName, $items, $getTVID)
    {
        global $folderType, $collectionType, $topParentId, $topParentName;
        $browseType = mapItemTypeToCollectionType(mapFolderTypeToSingleItemType($folderType, $collectionType));

        $this->cbp = new CategoryBrowseParams();
        $this->cbp->topParentName = $topParentName;
        $this->cbp->topParentId = $topParentId;
        $this->cbp->folderType = $folderType;
        $this->cbp->collectionType = $browseType;

        foreach ($items as $item) {
            //filter by the displayed folder/collectiontype, tv, movie, boxset...
            $this->cbp->params->addParam($categoryName, $item);

            $url = categoryBrowseURLEx($this->cbp);
            $this->printTVIDLink($url, call_user_func($getTVID, $item));
        }
    }

    private function printSpeedDial()
    {
        if ($this->renderFiltering) {
            //speed dial TVIDs
            $this->printTVIDLinks('NameStartsWith', $this->titleLetters, 'ListingsPage::toSingleLetterNumberpad');
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

    public function printPosterTable($items, $wrapBottomRowToTop = true)
    {
        global $lastRow;
        global $diplay_menuitems, $offset, $page;

        if ($this->dynamicGridPage) {
            $offset = $this->indexStyle->Limit * ($page - 1);
            $diplay_menuitems = array_slice($this->menuItems, $offset, $this->indexStyle->Limit, true);
        } else {
            $diplay_menuitems = $this->menuItems;
        }


        $lastRow = min(ceil(count($items) / $this->indexStyle->nbThumbnailsPerLine), ceil($this->indexStyle->Limit / $this->indexStyle->nbThumbnailsPerLine));
        ?>
        <table class="movies" border="0" cellpadding="<?= $this->indexStyle->moviesTableCellpadding ?? 0 ?>" cellspacing="<?= $this->indexStyle->moviesTableCellspacing ?? 0 ?>" align="<?= $this->indexStyle->moviesTableAlign ?>">
            <?
            //add empty menuitems so will always print limit
            $max = $this->dynamicGridPage ? min($this->indexStyle->Limit, count($diplay_menuitems)) : count($diplay_menuitems);
            for ($i=0; $i < $max; $i++) {
                $key = $offset + $i;
                $menuItem = $diplay_menuitems[$key];

                //first item in row
                if (ListingsPage::isStartOfRow($i)) {
                    echo "<tr>";
                }
                ListingsPage::printPosterTD($menuItem, $key, ceil(($i + 1) / $this->indexStyle->nbThumbnailsPerLine), $wrapBottomRowToTop);

                //last item in row
                if (ListingsPage::isEndOfRow($i)) {
                    echo "</tr>";
                }
            }
            ?>
        </table>
        <?
    }

    private function printPCMenu()
    {
    ?>
        <div id="popupWrapper"><div id="noNMT">
        <a href="#" onclick="toggleMenu(); toggleMenuLinks(); return false;">menu</a>
        <div id="menuLinks">
            <table id="menuLinkTbl" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="leftTd"><a href="#" onclick="catUp(); return false;">Up</a></td>
                    <td><a href="#" onclick="genUp(); return false;">Up</a></td>
                </tr>
                <tr>
                    <td class="leftTd"><a href="#" onclick="catDown(); return false;">Down</a></td>
                    <td><a href="#" onclick="genDown(); return false;">Down</a></td>
                </tr>
                <tr>
                    <td class="leftTd">&#160;</td>
                    <td class="selectLink"><a href="#" onclick="openLink('genLink5'); return false;">Select</a></td>
                </tr>
            </table>
        </div>

        </div></div>

    <?
    }

    protected function printPaging($currentPage = 1, $numPages = 1)
    {
        global $tvid_page_pgup, $tvid_page_pgdn;

        if ($numPages > 1) {
            if ($this->dynamicGridPage) {
                ?>
                <a href="#" name="pgupload" onfocus="updateSelectedItem(-1)"></a>
                <a href="#" name="pgdnload" onfocus="updateSelectedItem(1)"></a>
                <a href="#" onclick="updateSelectedItem(-1); return false;" TVID="<?= $tvid_page_pgup ?>" id="currentPage"><?= $currentPage ?></a> /
                <a href="#" onclick="updateSelectedItem(1); return false;" TVID="<?= $tvid_page_pgdn ?>"><?= $numPages ?></a>
                <?
            } else {
                parent::printPaging($currentPage, $numPages);
            }
        }
    }

    public function printFooter()
    {
        global $tvid_filter_menu;
        global $diplay_menuitems, $offset;
        if ($this->renderFiltering) {
        ?>
        <div id="popupWrapper">
<?
        FilterMenu::printFooter();
        ?>
        </div>
<?
            if ($this->PCMenu) {
                $this->printPCMenu();
            }
        }
?>
        <div id="popupWrapper">
<?
        //print popups last of all, so they have highest z-index on NMT
        if (isset($this->indexStyle->popupHeight) || isset($this->indexStyle->popupWidth)) {
            //print popups last of all, so they have highest z-index on NMT
            //foreach ($diplay_menuitems as $key => $menuItem) {
            $max = $this->dynamicGridPage ? $this->indexStyle->Limit : count($diplay_menuitems);
            for ($i=0; $i < $max; $i++) {
                $key = $offset + $i;
                $menuItem = $diplay_menuitems[$key];
                ListingsPage::printPopup($menuItem, $key);
            }
        }
?>
        </div>
<?
        if ($this->renderFiltering) {
?>
            <div id="navigationlinks">
                <a TVID="<?= $tvid_filter_menu ?>" name="showMenu"  onfocusset="catLink5" onclick="toggleMenu()" href="#" ></a>
                <a name="catLinkUp"   href="#" onfocusset="catLink5" onfocus="catUp();" onfocusload=""></a>
                <a name="catLinkDown" href="#" onfocusset="catLink5" onfocus="catDown();" onfocusload=""></a>
                <a name="genLinkUp"   href="#" onfocusset="genLink5" onfocus="genUp();" onfocusload=""></a>
                <a name="genLinkDown" href="#" onfocusset="genLink5" onfocus="genDown();" onfocusload=""></a>
            </div>
<?
            $this->printSpeedDial();
        }

        parent::printFooter();
    }

    public static function setNumPagesAndIndexCount($totalRecordCount)
    {
        global $page, $numPages, $indexStyle, $pageObj;
        $pageObj->indexStyle = $indexStyle;
        $numPages = ceil($totalRecordCount / $indexStyle->Limit);
        $indexStyle->setIndexCount($page < $numPages ?
                    $indexStyle->Limit :
                    $totalRecordCount - ($indexStyle->Limit * ($page-1)));
    }

    private static function isStartOfRow($position)
    {
        global $indexStyle;
        return ($position % $indexStyle->nbThumbnailsPerLine == 0);
    }

    private static function isLastRow($row)
    {
        global $lastRow;
        return ($row == $lastRow);
    }

    private static function isEndOfRow($position)
    {
        global $indexStyle;
        return ($position % $indexStyle->nbThumbnailsPerLine == $indexStyle->nbThumbnailsPerLine - 1);
    }

    private function isEndOfMenuItems($position)
    {
        // check if position would be past valid menuitems limit
        return ($position >= count($this->menuItems) - 1);
    }

    private static function printPopup($menuItem, $position)
    {
        global $indexStyle;
        $placement = ($position % $indexStyle->Limit);
        $row = intdiv($position % $indexStyle->Limit, $indexStyle->nbThumbnailsPerLine); // mod position by limit to support client side paging
        $col = $position % $indexStyle->nbThumbnailsPerLine;

        if (!$menuItem || $menuItem->PosterURL) {
    ?>
            <img id="imgDVD<?= $placement ?>" class="menu<?= $placement ?> imgRow<?= $row ?> imgCol<?= $col ?>" src="<?= $menuItem->PosterURL ?>" <?= $indexStyle->hoverFrame ? null : 'onclick="openLinkURL(asMenuURL[iActiveItem]);"' ?> />
    <?php
            if ($indexStyle->hoverFrame) {
    ?>
            <img id="frmDVD<?= $placement ?>" class="menu<?= $placement ?> frmRow<?= $row ?> frmCol<?= $col ?>" src="<?= $indexStyle->hoverFrame ?>" onclick="openLinkURL(asMenuURL[iActiveItem]);" />
    <?php
            }
        }
    }

    private function getOnkeyleftset($placement)
    {
        global $numPages;
        $retval = null;
        //start of row
        if (ListingsPage::isStartOfRow($placement)) {
            if ($placement == 0) {
                if ($numPages == 1) {
                    $retval = 'onkeyleftset="' . (count($this->menuItems) - 1) . '"';
                } else {
                    $retval = 'onkeyleftset="pgupload"';
                }
            } else {
                $retval = "onkeyleftset=\"" . ($placement - 1) . "\"";
            }
        }
        return $retval;
    }

    private function getOnkeyrightset($placement, $row)
    {
        global $numPages;

        $retval = null;

        if ((ListingsPage::isEndOfRow($placement) && ListingsPage::isLastRow($row))
            || $this->isEndOfMenuItems($placement)
        ) {
            if ($numPages == 1) {
                //go to first item
                $retval =  'onkeyrightset="0"';
            } else {
                $retval = 'onkeyrightset="pgdnload"';
            }
        } else {
            $retval = "onkeyrightset=\"" . ($placement + 1) . "\"";
        }

        return $retval;
    }

    private function getOnkeydownset($placement, $row, $wrapBottomRowToTop)
    {
        global $indexStyle, $numPages;

        $retval = null;
        //last row
        if (ListingsPage::isLastRow($row)) {
            if ($numPages == 1) {
                if ($wrapBottomRowToTop) {
                    //go to top row
                    $topofcolumn = $placement % $indexStyle->nbThumbnailsPerLine;
                    $retval = " onkeydownset=\"" . $topofcolumn . "\" ";
                }
            } else {
                //down arrow goes to next page
                $retval = " onkeydownset=\"pgdnload\"";
            }
        } else {
            // go to next row
            $nextdown = min($placement + $indexStyle->nbThumbnailsPerLine, count($this->menuItems) - 1);
            $retval = " onkeydownset=\"" . $nextdown . "\" ";
        }
        return $retval;
    }

    //gap is for skipping rows, in sets on the bottom
    private function printPosterTD($menuItem, $position, $row, $wrapBottomRowToTop)
    {
        global $indexStyle;
        $placement = ($position % $indexStyle->Limit);

        if (!$menuItem) {
            $menuItem = (object) [];
            $menuItem->PosterURL = "images/wall/transparent.png";
        }
        ?>
        <td align="center" <?
        if (!$menuItem->PosterURL) {
            ?>class="defaultCardBackground<?= ($position % 5) + 1 ?>" width="<?= $indexStyle->thumbnailsWidth ?>" height="<?= $indexStyle->thumbnailsHeight ?>"<?
        } ?> >
            <a href="#" onclick="openLinkURL(asMenuURL[iActiveItem]);" <?= $menuItem->OnDemandTag ?? null ?> name="<?= $placement ?>" onmouseover="show(<?= $placement ?>)" onfocus="show(<?= $placement ?>)" onblur="hide(<?= $placement ?>)"
            id="<?= $placement ?>"
    <?php
        echo ListingsPage::getOnkeyleftset($placement);
        echo ListingsPage::getOnkeyrightset($placement, $row);
        echo ListingsPage::getOnkeydownset($placement, $row, $wrapBottomRowToTop);
    ?>>
    <?
        if ($menuItem->PosterURL) {
    ?>
            <img id="menuImg<?= $placement ?>" src="<?= $menuItem->PosterURL ?>" width="<?= $indexStyle->thumbnailsWidth ?>" height="<?= $indexStyle->thumbnailsHeight ?>" onfocussrc="images/wall/transparent.png" />
    <?
        } else {
            echo $menuItem->Name;
        }
    ?></a>
        </td>
    <?php
    }


}
