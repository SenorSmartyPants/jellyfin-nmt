<?php
include_once 'IndexStyles.php';
include_once 'config_listings.php';
include_once 'secrets.php';
include_once 'menuItems.php';
include_once 'page.php';

$menuItems = array();

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

function setNumPagesAndIndexCount($totalRecordCount)
{
    global $page, $numPages, $indexStyle;
    $numPages = ceil($totalRecordCount / $indexStyle->Limit);
    $indexStyle->setIndexCount($page < $numPages ? 
                $indexStyle->Limit : 
                $totalRecordCount - ($indexStyle->Limit * ($page-1)));
}

function printListingsInitJS()
{
?>
        <script type="text/javascript" src="js/listings.js"></script>
<?
}

function printHeadEtc($onloadset = null, $additionalCSS = null, $title = null)
{
    global $indexStyle;
    $onload = "initpage(" . ((isset($indexStyle->popupHeight) || isset($indexStyle->popupWidth)) ? 'true' : 'false') . ")";
    Page::printHead($onloadset, $additionalCSS, $title, 'printListingsInitJS', $onload);
}

function printFooter()
{
    global $menuItems, $indexStyle;

?>
        <div id="popupWrapper">
<?
        //print popups last of all, so they have highest z-index on NMT
        foreach ($menuItems as $key => $menuItem) {
            printTitleAndSubtitle($menuItem, 0, $key);
        }
        if (isset($indexStyle->popupHeight) || isset($indexStyle->popupWidth)) {
            //print popups last of all, so they have highest z-index on NMT
            foreach ($menuItems as $key => $menuItem) {
                printPopup($menuItem, 0, $key);
            }
        }
?>
        </div>
        <div class="hidden" id="navigationlinks">
            <a href="index.php" TVID="HOME"></a>
            <a href="categories.php" TVID="info"></a><br/>
        </div>
<?php
    Page::printFooter();
}

function printNavbarAndPosters($title, $items)
{
    global $indexStyle;
    ?>
    <table border="0" cellpadding="0" cellspacing="0" align="left"><tr valign="top"><td>
    <?php  
    printNavbar($title);
?>
    </td></tr><tr valign="<?= $indexStyle->moviesTableVAlign ?>"><td class="posterTableParent">
<? 
    printPosterTable($items);
?>
    </td></tr></table>
<?php    
}

function printPosterTable($items)
{
    global $menuItems, $lastRow;

    global $indexStyle;

    $lastRow = ceil(count($items) / $indexStyle->nbThumbnailsPerLine);
    ?>
    <table class="movies" border="0" cellpadding="<?= $indexStyle->moviesTableCellpadding ?? 0 ?>" cellspacing="<?= $indexStyle->moviesTableCellspacing ?? 0 ?>" align="<?= $indexStyle->moviesTableAlign ?>">
        <?php
        $i = 0;
        foreach ($items as $item) {
            //first item in row
            if (isStartOfRow($i)) {
                echo "<tr>";
            }
            $menuItem = getMenuItem($item);
            if ($menuItem) {
                printPosterTD($menuItem, 0, $i, ceil(($i + 1) / $indexStyle->nbThumbnailsPerLine));
                //add menuItem to menuItems list for later
                array_push($menuItems, $menuItem);

                //last item in row
                if (isEndOfRow($i)) {
                    echo "</tr>";
                }

                $i++;
            }
        }
        ?>
    </table>
<?php
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

function printNavbar($title)
{
    global $user_switch_url, $user_ids, $current_users;

    ?>
    <table class="main" border="0" cellpadding="0" cellspacing="0">
        <tr valign="top">
            <td class="indexname" id="indexmenuleft" align="left" valign="top">
                <?= $title ?>
            </td>
            <td id="indexmenuright" align="right">&nbsp;
            <a onkeydownset="1" href="<?= $user_switch_url ?>"><?php
foreach($current_users as $user) {
?><img src="<?=getImageURL($user_ids[$user],45,45,null,null,null,null,null,"Users") ?>" width="45" height="45" /><?php
}
?></a>&nbsp;
            </td>
        </tr>
    </table>
<?php
}

function printTitleTable($currentPage = 1, $numPages = 1)
{
    global $apiCallCount, $QSBase, $include_jellyfin_logo_when_backdrop_present;
    global $backdropId;
    ?>
    <table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
        <tr>
            <td width="20%" valign="top"><? if ($include_jellyfin_logo_when_backdrop_present || !$backdropId) { ?><a href="index.php"><img src="<?= getLogoURL() ?>" height="47"/></a><? } ?></td>
            <td width="60%" align="center" valign="top">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" id="title" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center" id="subtitle" valign="top" class="secondaryText">&nbsp;</td>
                    </tr>
                </table>
            </td>


            <td width="20%" align="right" id="page" valign="top"><? 
            if ($numPages > 1) { 
                //pgup on first page, wraps around to last page
                $page = ($currentPage == 1) ? $numPages : (intval($currentPage) - 1);
                echo "\n" . '               <a name="pgupload" onfocusload="" TVID="PGUP" href="' . $_SERVER['PHP_SELF'] . $QSBase . $page . "\" >" . $currentPage . "</a> / ";
                //pgdn on last page wraps to first page
                $page = ($currentPage == $numPages) ? 1 : (intval($currentPage) + 1);
                echo "\n" . '               <a name="pgdnload" onfocusload="" TVID="PGDN" href="' . $_SERVER['PHP_SELF'] . $QSBase . $page  . "\" >" . $numPages . "</a>";
            }
?>
                <!-- API call count = <?= $apiCallCount ?> -->
            </td>
        </tr>

    </table>
<?php
}

?>