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
            #imgDVD1 { visibility: hidden; position: absolute; top: 12px; left: 11px; }
            #frmDVD1 { visibility: hidden; position: absolute; top: 1px; left: 0px; }
            #title1 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD2 { visibility: hidden; position: absolute; top: 12px; left: 103px; }
            #frmDVD2 { visibility: hidden; position: absolute; top: 1px; left: 93px; }
            #title2 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD3 { visibility: hidden; position: absolute; top: 12px; left: 224px; }
            #frmDVD3 { visibility: hidden; position: absolute; top: 1px; left: 214px; }
            #title3 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD4 { visibility: hidden; position: absolute; top: 12px; left: 345px; }
            #frmDVD4 { visibility: hidden; position: absolute; top: 1px; left: 335px; }
            #title4 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD5 { visibility: hidden; position: absolute; top: 12px; left: 466px; }
            #frmDVD5 { visibility: hidden; position: absolute; top: 1px; left: 456px; }
            #title5 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD6 { visibility: hidden; position: absolute; top: 12px; left: 587px; }
            #frmDVD6 { visibility: hidden; position: absolute; top: 1px; left: 577px; }
            #title6 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD7 { visibility: hidden; position: absolute; top: 12px; left: 708px; }
            #frmDVD7 { visibility: hidden; position: absolute; top: 1px; left: 698px; }
            #title7 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD8 { visibility: hidden; position: absolute; top: 12px; left: 829px; }
            #frmDVD8 { visibility: hidden; position: absolute; top: 1px; left: 819px; }
            #title8 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD9 { visibility: hidden; position: absolute; top: 12px; left: 924px; }
            #frmDVD9 { visibility: hidden; position: absolute; top: 1px; left: 914px; }
            #title9 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD10 { visibility: hidden; position: absolute; top: 190px; left: 11px; }
            #frmDVD10 { visibility: hidden; position: absolute; top: 179px; left: 0px; }
            #title10 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD11 { visibility: hidden; position: absolute; top: 190px; left: 103px; }
            #frmDVD11 { visibility: hidden; position: absolute; top: 179px; left: 93px; }
            #title11 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD12 { visibility: hidden; position: absolute; top: 190px; left: 224px; }
            #frmDVD12 { visibility: hidden; position: absolute; top: 179px; left: 214px; }
            #title12 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD13 { visibility: hidden; position: absolute; top: 190px; left: 345px; }
            #frmDVD13 { visibility: hidden; position: absolute; top: 179px; left: 335px; }
            #title13 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD14 { visibility: hidden; position: absolute; top: 190px; left: 466px; }
            #frmDVD14 { visibility: hidden; position: absolute; top: 179px; left: 456px; }
            #title14 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD15 { visibility: hidden; position: absolute; top: 190px; left: 587px; }
            #frmDVD15 { visibility: hidden; position: absolute; top: 179px; left: 577px; }
            #title15 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD16 { visibility: hidden; position: absolute; top: 190px; left: 708px; }
            #frmDVD16 { visibility: hidden; position: absolute; top: 179px; left: 698px; }
            #title16 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD17 { visibility: hidden; position: absolute; top: 190px; left: 829px; }
            #frmDVD17 { visibility: hidden; position: absolute; top: 179px; left: 819px; }
            #title17 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD18 { visibility: hidden; position: absolute; top: 190px; left: 924px; }
            #frmDVD18 { visibility: hidden; position: absolute; top: 179px; left: 914px; }
            #title18 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD19 { visibility: hidden; position: absolute; top: 364px; left: 11px; }
            #frmDVD19 { visibility: hidden; position: absolute; top: 353px; left: 0px; }
            #title19 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD20 { visibility: hidden; position: absolute; top: 364px; left: 103px; }
            #frmDVD20 { visibility: hidden; position: absolute; top: 353px; left: 93px; }
            #title20 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD21 { visibility: hidden; position: absolute; top: 364px; left: 224px; }
            #frmDVD21 { visibility: hidden; position: absolute; top: 353px; left: 214px; }
            #title21 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD22 { visibility: hidden; position: absolute; top: 364px; left: 345px; }
            #frmDVD22 { visibility: hidden; position: absolute; top: 353px; left: 335px; }
            #title22 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD23 { visibility: hidden; position: absolute; top: 364px; left: 466px; }
            #frmDVD23 { visibility: hidden; position: absolute; top: 353px; left: 456px; }
            #title23 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD24 { visibility: hidden; position: absolute; top: 364px; left: 587px; }
            #frmDVD24 { visibility: hidden; position: absolute; top: 353px; left: 577px; }
            #title24 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD25 { visibility: hidden; position: absolute; top: 364px; left: 708px; }
            #frmDVD25 { visibility: hidden; position: absolute; top: 353px; left: 698px; }
            #title25 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD26 { visibility: hidden; position: absolute; top: 364px; left: 829px; }
            #frmDVD26 { visibility: hidden; position: absolute; top: 353px; left: 819px; }
            #title26 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            #imgDVD27 { visibility: hidden; position: absolute; top: 364px; left: 924px; }
            #frmDVD27 { visibility: hidden; position: absolute; top: 353px; left: 914px; }
            #title27 { visibility: hidden; position: absolute; top: 600px; left: 1px; font-size: 18pt; color: #868686; }
            
            td#title { font-size: 18pt; color: #868686; }
            #frmlistDVD { visibility: visible; position: absolute; top: 52px; left: 730px; }
            td#page { font-size: 18pt; color: #868686; }
            td#year { font-size: 18pt; color: #868686; }
            div.title{ visibility:hidden; }
            div.counter {font-size: 15pt; color: #AAAAAA; }

            body { font-size: 10pt; color: #AAAAAA; text-decoration: none; background-position-x: -85px; }
            a { font-size: 18pt; color: #AAAAAA; text-decoration: none; }
            img { border: 0; }
            table.main { width:1090; height:83; }
            table.categories { width:180; }
            td { text-decoration: none;}
            td.movies { padding-right: 10px;}


            .hidden { visibility: hidden; display: none;}

            .indexname {
                font-size: 23pt;
                font-weight: normal;
                color: #8e8e8e;
            }
        </style>

        <script>
            var title = 1;
            function bind() {
                if ( title == 1 ) title = document.getElementById('title');
            }
            function show(x) {
                bind();
                title.firstChild.nodeValue = document.getElementById('title'+x).firstChild.nodeValue;
                document.styleSheets[0].cssRules[(x - 1) * 3].style.visibility = "visible";
                document.styleSheets[0].cssRules[(x - 1) * 3 + 1].style.visibility = "visible";
            }
            function hide(x) {
                bind();
                title.firstChild.nodeValue = "\xa0";
                document.styleSheets[0].cssRules[(x - 1) * 3].style.visibility = "hidden";
                document.styleSheets[0].cssRules[(x - 1) * 3 + 1].style.visibility = "hidden";
            }

            function initpage() {
                return false;
            }
        </script>

    </head>

    <body bgproperties="fixed" onloadset="1" FOCUSTEXT="#FFFFFF" focuscolor="transparent" onload="initpage()" background="<?= $jukebox_url ?>pictures/wall/background1.jpg">

    <?php
}

function printFooter()
{
    global $menuItems;
    //print popups last of all, so they have highest z-index on NMT
    foreach ($menuItems as $key => $menuItem) {
        printPopup($menuItem, 0, $key);
    }
    ?>
    </body>

    </html>
<?php
}

function printPosterTable($items)
{
    global $menuItems;
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
            printPosterTD($menuItem, 0, $key);
            //add menuItem to menuItems list for later
            array_push($menuItems, $menuItem);

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

function printPopup($menuItem, $gap, $position)
{
    global $api_url, $jukebox_url;
    $placement = $position + $gap + 1; //$position is zero based
    ?>
    <div id="title<?= $placement ?>"><?= $menuItem->Name ?></div>
    <img id="imgDVD<?= $placement ?>" src="<?= $api_url .$menuItem->PosterBaseURL ?>" />
    <img id="frmDVD<?= $placement ?>" src="<?= $jukebox_url ?>pictures/wall/hover-frame.png" />
    <?php
}

//gap is for skipping rows, in sets on the bottom
function printPosterTD($menuItem, $gap, $position)
{
    global $api_url, $jukebox_url;
    global $thumbnailsWidth, $thumbnailsHeight;
    global $nbThumbnailsPerPage;
    $placement = $position + $gap + 1; //$position is zero based
    ?>
    <td align="center">
        <a href="<?= $menuItem->DetailURL ?>" onclick="return prompter('TV-14 hardcode')" TVID="<?= $placement ?>" name="<?= $placement ?>" onmouseover="show(<?= $placement ?>)" onmouseout="hide(<?= $placement ?>)" onfocus="show(<?= $placement ?>)" onblur="hide(<?= $placement ?>)" 
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
            <img src="<?= $api_url . $menuItem->PosterBaseURL ?>" width="<?= $thumbnailsWidth ?>" height="<?= $thumbnailsHeight ?>" onfocussrc="<?= $jukebox_url ?>pictures/wall/transparent.png" /></a>
    </td>
<?php
}

function printNavbar()
{
    global $jukebox_url;

    //83 final height for navbar in original
    $imagePadHeight = 56; //56 in original layout
    ?>
    <table class="main" border="0" cellpadding="0" cellspacing="0">
        <tr valign="top">
            <td align="left" valign="top" height="<?= $imagePadHeight ?>" width="1"><img src="<?= $jukebox_url ?>pictures/detail/1x688.png" height="<?= $imagePadHeight ?>" /></td>
            <td class="indexname" id="indexmenuleft" align="left" valign="top" height="<?= $imagePadHeight ?>" width="265">
                Latest
            </td>
            <td id="indexmenuright" align="right">&nbsp;</td>
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

//echo "<div class=\"indexname\">API call count = $apiCallCount</div>";
?>