<?php

include 'secrets.php';
include 'utils.php';

$GroupItems = "true";
$Limit = 21;

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

    <body bgproperties="fixed" onloadset="1" FOCUSTEXT="#FFFFFF" focuscolor="transparent" onload="initpage()"
    background="<?= $jukebox_url ?>/pictures/wall/background1.jpg">
 
    <?php
}

function printFooter()
{
    ?>
    </body>

    </html>
<?php
}

function printPosterTable()
{
    global $api_url, $user_id, $api_key, $GroupItems, $Limit;
    ;
    global $nbThumbnailsPerLine;
    //set table is centered
    $align = "left";
    ?>
    <table class="movies" border="0" cellpadding="0" cellspacing="4" align="<?= $align ?>" >
<?php
/*
    <xsl:for-each select="library/movies/movie[position() mod $nbCols = 1]"> //selects first item in row
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
    $url = $api_url . "/Users/" . $user_id .
    "/Items/Latest?&GroupItems=" . $GroupItems .
    "&Limit=" . $Limit .
    "&api_key=" . $api_key;

    echo "<a href=\"" . $url . "\">url</a><br/>";


    $contents = file_get_contents($url);
    $latest = json_decode($contents);

    //gap is ?
    foreach ($latest as $key => $item) {
        if ($key % $nbThumbnailsPerLine == 0) 
        {
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
        if ($key % $nbThumbnailsPerLine == $nbThumbnailsPerLine - 1) 
        {
            echo "</tr>";
        }
    }
    ?>
    </table>
<?php 
}

//gap is for skipping rows, in sets on the bottom
function printPostTD($menuItem, $gap, $position)
{
    global $api_url, $jukebox_url, $popupWidth, $popupHeight;
    global $thumbnailsWidth,$thumbnailsHeight;
    $placement = $position + $gap;
    ?>
    <td align="center">
        <a href="<?= $jukebox_url . $menuItem->DetailBaseURL ?>" onclick="return prompter('TV-14 hardcode')" 
        TVID="<?= $placement ?>" name="<?= $placement ?>" 
        onmouseover="show(<?= $placement ?>, 'do I even use this?')" 
        onmouseout="hide(<?= $placement ?>, 'or this?')" 
        onfocus="show(<?= $placement ?>, 'do I even use this?')" 
        onblur="hide(<?= $placement ?>, 'or this?')" 
        >
        <?php
                                                                                                                                                                                                                            
        /*   
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




//TODO: this manages keyset navigation 
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

                <xsl:if test="position()+$gap = 1">
                    <xsl:attribute name="onkeyleftset">pgupload</xsl:attribute>
                </xsl:if>

                <xsl:if test="position()+$gap = 9">
                    <xsl:attribute name="onkeyrightset">10</xsl:attribute>
                </xsl:if>
                <xsl:if test="position()+$gap = 10">
                    <xsl:attribute name="onkeyleftset">9</xsl:attribute>
                </xsl:if>
                <xsl:if test="position()+$gap = 18">
                    <xsl:attribute name="onkeyrightset">19</xsl:attribute>
                </xsl:if>
                <xsl:if test="position()+$gap = 19">
                    <xsl:attribute name="onkeyleftset">18</xsl:attribute>
                </xsl:if>
                <xsl:if test="position()+$gap = 27">
                    <xsl:attribute name="onkeyrightset">pgdnload</xsl:attribute>
                </xsl:if>

                <xsl:if test="$gap=$lastGap and $currentIndex != $lastIndex">
                    <xsl:attribute name="onkeydownset">pgdnload</xsl:attribute>
                </xsl:if>
            </xsl:if>                
        */
        ?> 
        <img src="<?= $api_url ?>/Items/<?= $menuItem->PosterID ?>/Images/Primary?UnplayedCount=<?= $menuItem->UnplayedCount ?>&maxHeight=<?= $popupHeight ?>&maxWidth=<?= $popupWidth ?>" width="<?= $thumbnailsWidth ?>" height="<?= $thumbnailsHeight ?>" onfocussrc="pictures/wall/transparent.png"/></a>
    </td>
<?php
}



printHeadEtc();

printPosterTable();

printFooter();
?>