<?php
include_once 'IndexStyles.php';
include_once 'config_listings.php';
include_once 'secrets.php';
include_once 'menuItems.php';

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

function printHeadEtc($onloadset = null)
{
    global $theme_css, $indexStyle;

    $onloadset = $onloadset ?? "1";
    ?>
    <html>

    <head>
        <link rel="shortcut icon" href="<?= getFavIconURL() ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Jellyfin NMT</title>

<?
        if (isset($indexStyle) && null !== $indexStyle->cssFile()) {
?>
        <!-- don't add any styles before the following. JS show/hide code depends on this these being first -->
        <link rel="StyleSheet" type="text/css" href="<?= $indexStyle->cssFile() ?>"/>
<?
        }
?>
        <link rel="StyleSheet" type="text/css" href="css/base.css" />
        <link rel="StyleSheet" type="text/css" href="css/themes/<?= $theme_css ?>" />

        <script>
            var title = 1;
            var subtitle = 1;

            function bind() {
                if ( title == 1 ) title = document.getElementById('title');
                if (subtitle == 1) subtitle = document.getElementById('subtitle');
            }
            function show(x) {
                bind();
                title.firstChild.nodeValue = document.getElementById('title'+x).firstChild.nodeValue;
                var subX = document.getElementById('subtitle'+x).firstChild;
                if (subX) {
                    subtitle.firstChild.nodeValue = subX.nodeValue;
                }
<?
                if (isset($indexStyle->popupHeight)) {
?>
                document.styleSheets[0].cssRules[(x - 1) * 2].style.visibility = "visible";
                document.styleSheets[0].cssRules[(x - 1) * 2 + 1].style.visibility = "visible";
<?
                }
?>            
            }
            function hide(x) {
                bind();
                title.firstChild.nodeValue = "\xa0";
                subtitle.firstChild.nodeValue = "\xa0";
<?
                if (isset($indexStyle->popupHeight)) {
?>
                document.styleSheets[0].cssRules[(x - 1) * 2].style.visibility = "hidden";
                document.styleSheets[0].cssRules[(x - 1) * 2 + 1].style.visibility = "hidden";
<?
                }
?>          
            }

            function initpage() {
                return false;
            }
        </script>

    </head>

    <body bgproperties="fixed" onloadset="<?= $onloadset?>" FOCUSTEXT="#FFFFFF" focuscolor="#00a4dc" onload="initpage()"
        <? if ($_GET["backdropId"]) { ?>background="<?= getImageURL($_GET["backdropId"],720,1280,"Backdrop") ?>" <? } ?>>

    <?php
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
        if (isset($indexStyle->popupHeight)) {
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

    </body>

    </html>
<?php
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
        foreach ($items as $key => $item) {
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
        <img id="imgDVD<?= $placement ?>" src="<?= $menuItem->PosterURL ?>" />
<?php
        if ($indexStyle->hoverFrame) {
?>
        <img id="frmDVD<?= $placement ?>" src="<?= $indexStyle->hoverFrame ?>" />
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
        <a href="<?= $menuItem->DetailURL ?>" <?= $menuItem->OnDemandTag ?? null ?> onclick="return prompter('TV-14 hardcode')" TVID="<?= $placement ?>" name="<?= $placement ?>" onmouseover="show(<?= $placement ?>)" onmouseout="hide(<?= $placement ?>)" onfocus="show(<?= $placement ?>)" onblur="hide(<?= $placement ?>)" 
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
            echo "onkeyrightset=\"" . ($placement + 1) . "\"";
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
    ?>
    <table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
        <tr>
            <td width="20%" valign="top"><? if ($include_jellyfin_logo_when_backdrop_present || !$_GET["backdropId"]) { ?><a href="index.php"><img src="<?= getLogoURL() ?>" height="47"/></a><? } ?></td>
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