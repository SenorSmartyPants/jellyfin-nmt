<?php

include 'secrets.php';
include 'utils.php';

$GroupItems = "true";
$Limit = 27;

//latest tv ==
//&IncludeItemTypes=episode,series

//skin options
$nbThumbnailsPerPage = 27;
$nbThumbnailsPerLine = 9;
$thumbnailsWidth = 117;
$thumbnailsHeight = 174;
$popupWidth = 160;
$popupHeight = 237;

/*
skin-user.properties
thumbnails.overlay=true

# 3 ROWS POPUP - works *only* for 'awesome' for "roundedcorners" in skin-options.xsl
mjb.nbThumbnailsPerPage=27
mjb.nbThumbnailsPerLine=9
thumbnails.width=160
thumbnails.height=237


mjb.nbSetThumbnailsPerPage=9
mjb.nbSetThumbnailsPerLine=9

roundedcorners=awesome
tv-banners=false
*/

function printMenuItem($menuItem)
{
    global $api_url, $jukebox_url, $popupWidth, $popupHeight;
    ?>
    <a href="<?= $jukebox_url . $menuItem->DetailBaseURL ?>">
        <img src="<?= $api_url ?>/Items/<?= $menuItem->PosterID ?>/Images/Primary?UnplayedCount=<?= $menuItem->UnplayedCount ?>&maxHeight=<?= $popupHeight ?>&maxWidth=<?= $popupWidth ?>" /></a><br />
    <b><?= $menuItem->Name ?></b><br />
<?php
}

function printHeadEtc()
{
    global $jukebox_url;
    //TODO:background can be set to fanart... 
    ?>
    <html>

    <head>
        <link rel="shortcut icon" href="/favicon.ico" />
        <!--<link rel="StyleSheet" type="text/css" href="exportindex_item_pch.css"></link>-->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Moviejukebox</title>

        <style>
            .indexname {
                font-size: 23pt;
                font-weight: normal;
                color: #8e8e8e;
            }
        </style>

    </head>

    <body bgproperties="fixed" onloadset="1" FOCUSTEXT="#FFFFFF" focuscolor="transparent" onload="initpage()" background="<?= $jukebox_url ?>pictures/wall/background1.jpg">

    <?php
}

function printFooter()
{
    ?>
    </body>

    </html>
<?php
}

function printPosterTable($items)
{
    global $nbThumbnailsPerLine;
    //set table is centered
    $align = "left";
    ?>
    <table class="movies" border="0" cellpadding="0" cellspacing="4" align="<?= $align ?>">
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

        foreach ($items as $key => $item) {
            //first item in row
            if (isStartOfRow($key)) {
                echo "<tr>";
            }
            switch ($item->Type) {
                case "Episode":
                    $menuItem = parseEpisode($item);
                    break;
                case "Series":
                    $menuItem = parseSeries($item);
                    break;
                case "Movie":
                    $menuItem = parseMovie($item);
                    break;
            }
            //printMenuItem($menuItem);
            printPostTD($menuItem, 0, $key);

            //last item in row
            if (isEndOfRow($key)) {
                echo "</tr>";
            }
        }
        ?>
    </table>
<?php
}

function isStartOfRow($position)
{
    global $nbThumbnailsPerLine;
    return ($position % $nbThumbnailsPerLine == 0);
}

function isEndOfRow($position)
{
    global $nbThumbnailsPerLine;
    return ($position % $nbThumbnailsPerLine == $nbThumbnailsPerLine - 1);
}

//gap is for skipping rows, in sets on the bottom
function printPostTD($menuItem, $gap, $position)
{
    global $api_url, $jukebox_url, $popupWidth, $popupHeight;
    global $thumbnailsWidth, $thumbnailsHeight;
    global $nbThumbnailsPerPage, $nbThumbnailsPerLine;
    $placement = $position + $gap + 1; //$position is zero based
    ?>
    <td align="center">
        <a href="<?= $jukebox_url . $menuItem->DetailBaseURL ?>" onclick="return prompter('TV-14 hardcode')" TVID="<?= $placement ?>" name="<?= $placement ?>" onmouseover="show(<?= $placement ?>, 'do I even use this?')" onmouseout="hide(<?= $placement ?>, 'or this?')" onfocus="show(<?= $placement ?>, 'do I even use this?')" onblur="hide(<?= $placement ?>, 'or this?')" 
<?php

    //start of row
    if (isStartOfRow($placement - 1)) {
        if ($placement == 1) {
            echo "onkeyrightset=\"pgupload\"";
        } else {
            echo "onkeyleftset=\"" . ($placement - 1) . "\"";
        }
    }

    //end of row
    if (isEndOfRow($placement - 1)) {
        if ($placement != $nbThumbnailsPerPage) {
            echo "onkeyrightset=\"" . ($placement + 1) . "\"";
        } else {
            echo "onkeyrightset=\"pgdnload\"";
        }
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
            <img src="<?= $api_url ?>/Items/<?= $menuItem->PosterID ?>/Images/Primary?UnplayedCount=<?= $menuItem->UnplayedCount ?>&maxHeight=<?= $popupHeight ?>&maxWidth=<?= $popupWidth ?>" width="<?= $thumbnailsWidth ?>" height="<?= $thumbnailsHeight ?>" onfocussrc="pictures/wall/transparent.png" /></a>
    </td>
<?php
}

function printNavbar()
{
    global $jukebox_url;

    //83 final height for navbar in original
    $imagePadHeight = 83; //56 in original layout
    ?>
    <table class="main" border="0" cellpadding="0" cellspacing="0">
        <tr valign="top">
            <td align="left" valign="top" height="<?= $imagePadHeight ?>" width="1"><img src="<?= $jukebox_url ?>pictures/detail/1x688.png" height="<?= $imagePadHeight ?>" /></td>
            <td class="indexname" id="indexmenuleft" align="left" valign="top" height="<?= $imagePadHeight ?>" width="265">
                Latest
            </td>
            <td id="indexmenuright" align="right">place holding</td>
        </tr>
    </table>
<?php
}

function printTitleTable()
{
    ?>
    <table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
        <!--<xsl:if test="$index-titlebackground = 'true'"><xsl:attribute name="background">pictures/dim/custom_tvtitle_dim.png</xsl:attribute></xsl:if>-->
        <tr>
            <td width="25%"></td>
            <td width="50%" align="center" id="title" valign="top">&nbsp;</td>


            <td width="25%" align="right" id="page" valign="top">
                <!--<xsl:value-of select="$Page"/>&#160;
	
	        <xsl:if test="$ForComputer='false'"><xsl:value-of select="$currentIndex"/></xsl:if>
	        <xsl:if test="$ForComputer='true'">
		        <a>
			        <xsl:attribute name="href">
			        <xsl:value-of select="library/category[@current='true']/index[@current='true']/@previous" />.html</xsl:attribute>
			        <xsl:value-of select="$currentIndex"/>
		        </a>
	        </xsl:if>
	
	        &#160;<xsl:value-of select="$OutOf"/>&#160;<a onfocusload=""><xsl:attribute name="href"><xsl:value-of select="library/category[@current='true']/index[@current='true']/@next" />.html</xsl:attribute><xsl:value-of select="$lastIndex" /></a>-->
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="right" id="year" valign="top">&nbsp;</td>
        </tr>
    </table>
<?php
}



printHeadEtc();

printNavbar();

printPosterTable(getLatest($Limit));

printTitleTable();

printFooter();

echo "<div class=\"indexname\">API call count = $apiCallCount</div>";
?>