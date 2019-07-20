<?php
include_once 'IndexStyles.php';
include_once 'config.php';
include_once 'secrets.php';
include_once 'menuItems.php';

$menuItems = array();

function printMenuItem($menuItem)
{
    global $api_url;
    ?>
    <a href="<?= $menuItem->DetailURL ?>">
        <img src="<?= $api_url . $menuItem->PosterBaseURL ?>" /></a><br />
    <b><?= $menuItem->Name ?></b><br />
<?php
}

function printHeadEtc($onloadset = null)
{
    global $api_url, $theme_css;
    global $indexStyle;
    //TODO:background can be set to fanart... 
    $onloadset = $onloadset ?? "1";
    ?>
    <html>

    <head>
        <link rel="shortcut icon" href="<?= $api_url ?>/../web/favicon.ico" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Jellyfin NMT</title>

<?
        if (null !== $indexStyle->cssFile()) {
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
        <? if ($_GET["backdropId"]) { ?>background="<?= $api_url . "/Items/" . $_GET["backdropId"] ?>/Images/Backdrop?Height=720&Width=1280" <? } ?>>

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
        /*
    <xsl:for-each select="library/movies/movie[position() mod $nbCols = 1]"> //selects first item in row
    //here position is row number
    //gap is # items already displayed on previous rows 
      <tr>
        <xsl:apply-templates
             select=".|following-sibling::movie[position() &lt; $nbCols]">
          <xsl:with-param name="gap" select="(position() - 1) * $nbCols" />
          <xsl:with-param name="currentIndex" select="$currentIndex" />
          <xsl:with-param name="lastIndex" select="$lastIndex" />
          <xsl:with-param name="lastGap" select="($nbLines - 1) * $nbCols" />
        </xsl:apply-templates>
      </tr>
    </xsl:for-each>
    */
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
    global $api_url, $jukebox_url, $indexStyle;
    $placement = $position + $gap + 1; //$position is zero based

    if ($menuItem->PosterBaseURL) {
?>
        <img id="imgDVD<?= $placement ?>" src="<?= $api_url . $menuItem->PosterBaseURL ?>" />
        <img id="frmDVD<?= $placement ?>" src="<?= $jukebox_url . $indexStyle->hoverFrame ?>" />
<?php
    }
}

//gap is for skipping rows, in sets on the bottom
function printPosterTD($menuItem, $gap, $position, $row)
{
    global $api_url, $jukebox_url;
    global $indexStyle;
    $placement = $position + $gap + 1; //$position is zero based
    ?>
    <td align="center" <? if (!$menuItem->PosterBaseURL) { ?>class="defaultCardBackground<?= ($position % 5) + 1 ?>"<?}?> >
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
        if ($placement != $indexStyle->nbThumbnailsPerPage) {
            echo "onkeyrightset=\"" . ($placement + 1) . "\"";
        } else {
            echo "onkeyrightset=\"pgdnload\"";
        }
    }


    //last row
    if (isLastRow($row)) {
        //go to top row
        $topofcolumn = $placement % $indexStyle->nbThumbnailsPerLine;
        $topofcolumn = ($topofcolumn == 0) ? $indexStyle->nbThumbnailsPerLine : $topofcolumn;
        echo " onkeydownset=\"" . $topofcolumn . "\" ";
    }

/*  TODO: is this anything I want to keep? 
                <xsl:choose>
                    <xsl:when test="count(files/file) > 1">
                        <xsl:call-template name="search-and-replace">
                            <xsl:with-param name="input" select="baseFilename" />
                            <xsl:with-param name="search-string">'</xsl:with-param>
                            <xsl:with-param name="replace-string">\'</xsl:with-param>
                        </xsl:call-template>.playlist.jsp','playlist
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="files/file[1]/fileURL" />','<xsl:choose>
                            <xsl:when test="container='DVD Video' or container='dvd video'">zcd</xsl:when>
                            <xsl:when test="container = 'ISO' or ends-with(files/file[1]/fileURL, '.ISO') or ends-with(files/file[1]/fileURL, '.iso')">zcd</xsl:when>
                            <xsl:when test="container = 'IMG' or ends-with(files/file[1]/fileURL, '.IMG') or ends-with(files/file[1]/fileURL, '.img')">zcd</xsl:when>
                            <xsl:when test="ends-with(files/file[1]/fileURL, 'VIDEO_TS')">zcd</xsl:when>
                            <xsl:otherwise>vod</xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose> 

            <xsl:attribute name="onmouseout">hide(
            <xsl:value-of select="position()+$gap" />, '<xsl:call-template name="search-and-replace">
                <xsl:with-param name="input" select="baseFilename" />
                <xsl:with-param name="search-string">'</xsl:with-param>
                <xsl:with-param name="replace-string">\'</xsl:with-param>
            </xsl:call-template>.playlist.jsp')</xsl:attribute>

            <xsl:attribute name="onfocus">show(
                <xsl:value-of select="position()+$gap" />, '<xsl:choose>
                    <xsl:when test="count(files/file) > 1">
                        <xsl:call-template name="search-and-replace">
                            <xsl:with-param name="input" select="baseFilename" />
                            <xsl:with-param name="search-string">'</xsl:with-param>
                            <xsl:with-param name="replace-string">\'</xsl:with-param>
                        </xsl:call-template>.playlist.jsp','playlist
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="files/file[1]/fileURL" />','<xsl:choose>
                            <xsl:when test="container='DVD Video' or container='dvd video'">zcd</xsl:when>
                            <xsl:when test="container = 'ISO' or ends-with(files/file[1]/fileURL, '.ISO') or ends-with(files/file[1]/fileURL, '.iso')">zcd</xsl:when>
                            <xsl:when test="container = 'IMG' or ends-with(files/file[1]/fileURL, '.IMG') or ends-with(files/file[1]/fileURL, '.img')">zcd</xsl:when>
                            <xsl:when test="ends-with(files/file[1]/fileURL, 'VIDEO_TS')">zcd</xsl:when>
                            <xsl:otherwise>vod</xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>')</xsl:attribute>

                <xsl:attribute name="onblur">hide(
                <xsl:value-of select="position()+$gap" />, '<xsl:call-template name="search-and-replace">
                    <xsl:with-param name="input" select="baseFilename" />
                    <xsl:with-param name="search-string">'</xsl:with-param>
                    <xsl:with-param name="replace-string">\'</xsl:with-param>
                </xsl:call-template>.playlist.jsp')</xsl:attribute>




//TODO: this manages keyset navigation . Top and bottom rows
            <xsl:if test="$lastIndex != 0">
                <xsl:if test="$gap=0 and $currentIndex != 0 and $FilterBar='true'">
                    <xsl:attribute name="onkeyupset">left</xsl:attribute>
                </xsl:if>
                <xsl:if test="$gap=0 and $currentIndex != 0 and $FilterBar='false' and $NavBar='false'">
                    <xsl:attribute name="onkeyupset">pgupload</xsl:attribute>
                </xsl:if>
                <xsl:if test="$gap=0 and $currentIndex != 0 and $FilterBar='false' and $NavBar='true'">
                    <xsl:attribute name="onkeyupset">moviestv</xsl:attribute>
                </xsl:if>

                <xsl:if test="$gap=$lastGap and $currentIndex != $lastIndex">
                    <xsl:attribute name="onkeydownset">pgdnload</xsl:attribute>
                </xsl:if>
            </xsl:if>                
        */
?>>
<?
    if ($menuItem->PosterBaseURL) {
?>
        <img src="<?= $api_url . $menuItem->PosterBaseURL ?>" width="<?= $indexStyle->thumbnailsWidth ?>" height="<?= $indexStyle->thumbnailsHeight ?>" onfocussrc="<?= $jukebox_url ?>pictures/wall/transparent.png" />
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
    global $api_url, $user_switch_url, $user_ids, $current_users;

    ?>
    <table class="main" border="0" cellpadding="0" cellspacing="0">
        <tr valign="top">
            <td class="indexname" id="indexmenuleft" align="left" valign="top">
                <?= $title ?>
            </td>
            <td id="indexmenuright" align="right">&nbsp;
            <a href="<?= $user_switch_url ?>"><?php
foreach($current_users as $user) {
?><img src="<?= $api_url ?>/Users/<?= $user_ids[$user] ?>/Images/Primary" width="45" height="45" /><?php
}
?></a>&nbsp;
            </td>
        </tr>
    </table>
<?php
}

function printTitleTable($currentPage = 1, $numPages = 1)
{
    global $api_url, $apiCallCount;
    global $QSBase, $include_jellyfin_logo_when_backdrop_present;
    ?>
    <table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
        <!--<xsl:if test="$index-titlebackground = 'true'"><xsl:attribute name="background">pictures/dim/custom_tvtitle_dim.png</xsl:attribute></xsl:if>-->
        <tr>
            <td width="20%" valign="top"><? if ($include_jellyfin_logo_when_backdrop_present || !isset($_GET["backdropId"])) { ?><img src="<?= $api_url ?>/../web/components/themes/logowhite.png" height="47"/><? } ?></td>
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