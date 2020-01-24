<?
/*
no tv rating in JF episode data?
TODO: support season banner. But most in JF don't have
    Banner(doesn't inherit):

        <br />
    Season
    Overview and OfficialRating - missing, pull separately?
    <br />
    episodes support ParentalRating, but not populated in JF
    <br />
    Support watched. Check skin to see <?= $episode->UserData->Played ?>


*/
include_once 'data.php';
include_once 'utils.php';
include_once 'templates.php';

$titleTruncate = 34;
$ShowAudioCodec = true;
$ShowContainer = true;
$ShowVideoOutput = true;
$star_rating = true;
$tvNumberRating = false;

$id = $_GET["id"];

//Banners don't inherit from parents
//have to load season to find out if it has a banner
$season = getItem($id);
$series = getItem($season->SeriesId);
$bannerId = $season->ImageTags->Banner ? $season->Id : $season->SeriesId;
$backdrop = getBackdropIDandTag($season);

$episodesAndCount = getUsersItems(null, "Path,Overview,Height,Width,MediaSources,ProviderIds", null, $id);
$episodes = $episodesAndCount->Items;
$i=0;
do {
    $episode = $episodes[$i++];
} while ($id >= $episode->SeasonId && $i < count($episodes));


$firstSource = $episode->MediaSources[0];
if ($firstSource) {
    foreach ($firstSource->MediaStreams as $mediastream) {
        if ($mediastream->Type == 'Video') {
            $videoStream = $mediastream;
        }
    }
    $audioStream = $firstSource->MediaStreams[$firstSource->DefaultAudioStreamIndex];
    //can have subs without a default
    $subtitleStream = $firstSource->MediaStreams[$firstSource->DefaultSubtitleStreamIndex];
}

printSeasonHeadEtc();
printTopBar();
printSpacerTable();
printLowerTable();
printSeasonFooter();

function formatCast($cast)
{
    $links = array();
    foreach ($cast as $person) {
        $links[] = '<a href="itemDetails.php?id=' . $person->Id . '">' . $person->Name . '</a>';
    }
    return implode(' / ', $links);
}

function renderEpisodeJS($episode)
{
    global $titleTruncate;
?>
    <script type="text/javascript">
        <!--
        asEpisodeTitle.push("<?= $episode->Name ?>");
        asEpisodeTitleShort.push("<?= substr($episode->Name, 0, $titleTruncate) ?>");
        asEpisodePlot.push("<?= $episode->Overview ?>");
        asEpisodeUrl.push("<?= translatePathToNMT(implode("/", array_map("rawurlencode", explode("/", $episode->Path)))) ?>");
        asEpisodeVod.push("vod");
        asSeasonNo.push("<?= $episode->ParentIndexNumber ?>");
        asEpisodeNo.push("<?= $episode->IndexNumber ?>");
        asEpisodeTVDBID.push("<?= $episode->ProviderIds->Tvdb ?>");
        asEpisodeWatched.push("<?= $episode->UserData->Played ?>");
        asEpisodeImage.push("<?= getImageURL($episode->Id, 164, null, "Primary", null, null, $episode->ImageTags->Primary) ?>");
        -->
    </script>
<?
}

function renderEpisodeHTML($episode, $indexInList)
{
    global $season, $titleTruncate;

    if ($episode->ParentIndexNumber = 0 || $season->IndexNumber != 0) {
        //Special episode, not displaying special season, then list episode as SX. Title
        $titleLine = 'S' . $episode->IndexNumber;
    } else {

        $titleLine = sprintf('%02d', $episode->IndexNumber);
    }
    $titleLine .= '. ' . substr($episode->Name, 0, $titleTruncate);
    
?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <a class="TvLink" id="a_e_<?= $indexInList ?>" name="episode<?= $indexInList ?>" 
                    onkeydownset="todown" onkeyrightset="toright" onkeyupset="toup" onkeyleftset="toleft" 
                    onclick="return clicked(this);" onfocus="resetGetter();"
                    onmouseover="showEpisode(<?= $indexInList ?>)" href="#playepisode<?= $indexInList ?>" season="<?= $episode->ParentIndexNumber ?>" episode="<?= $episode->IndexNumber ?>" tvdbid="<?= $episode->ProviderIds->Tvdb ?>">
                    <span class="tabTvShow" id="s_e_<?= $indexInList ?>"><?= $titleLine ?></span>
                </a>
                <a style="display:none;visibility:hidden" width="0" height="0" onfocusload="" 
                href="<?= translatePathToNMT(implode("/", array_map("rawurlencode", explode("/", $episode->Path)))) ?>" 
                vod="" 
                id="a2_e_<?= $indexInList ?>" name="playepisode<?= $indexInList ?>" onfocusset="episode<?= $indexInList ?>" />
            </td>
        </tr>
    </table><a href="#" class="tabTvShow" TVID="<?= $episode->IndexNumber ?>" onclick="setFocusNew(<?= $indexInList ?>); return false;" id="t_e_<?= $indexInList ?>" />
<?
}

function printSeasonHeadEtc($onloadset = null)
{
    global $backdrop, $season;

    global $theme_css, $indexStyle;
    $onloadset = $onloadset ?? "1";
    ?>
    <html>

    <head>
        <link rel="shortcut icon" href="<?= getFavIconURL() ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?= $season->Name . ' - ' . $season->SeriesName ?> - Jellyfin NMT</title>

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

        <style>
/*Sabish TVMod V2 - override*/
.test {color: c6cace; font-weight: normal; font-size: 32pt;!important}
#divEpisodeImgBackSabish {top: 405px; left: 20px;!important}
#divEpisodeImgSabish {top: 417px; left: 31px;!important}
#divEpisodeCertification {top: 597px; left: 161px;!important}
.tvseason {color: c6cace; font-weight: normal; font-size: 32pt;!important}
.tvyear {color: 868686; font-weight: normal; font-size: 32pt;!important}
.tveptitle {font-size:26pt; font-weight: bold; color:#FFFFFF}

.tveptitle24 {font-size:24pt; font-weight: bold; color:#FFFFFF}
.tveptitle22 {font-size:22pt; font-weight: bold; color:#FFFFFF}
.tveptitle20 {font-size:20pt; font-weight: bold; color:#FFFFFF}
.tveptitle18 {font-size:18pt; font-weight: bold; color:#FFFFFF}
.tveptitle16 {font-size:16pt; font-weight: bold; color:#FFFFFF}
.tveptitle14 {font-size:14pt; font-weight: bold; color:#FFFFFF}

.tvplot {color: ececec; font-weight: normal; font-size: 13pt;!important}
.TvLink {color: 868686; font-size: 12pt; font-weight: bold;!important}
.TvLink11 {color: 868686; font-size: 11pt; font-weight: bold;!important}
.rating {color: ffffff; font-weight: 400; font-size: 18pt;!important}

</style>

        <script>

        </script>

    </head>

    <body bgproperties="fixed" onloadset="episode1" onload="initNew()" bgcolor="#000000" focustext="#FFFFFF" FOCUSCOLOR="transparent" 
    <? if ($backdrop->Tag) 
    { 
        ?> background="<?= getImageURL($backdrop->Id, 720, 1280, "Backdrop", null, null, $backdrop->Tag) ?>"> <?   
    }
    ?>

    <table height="656" width="1102" border="0" cellspacing="0" cellpadding="0" background="/New/Jukebox/pictures/sabishmod/tvbg-v2.png">
        <tr>
            <td valign="top">
<?
}

function printTopBar()
{
    global $episode, $season, $videoStream, $audioStream, $firstSource;
    global $ShowAudioCodec, $ShowContainer, $ShowVideoOutput, $star_rating, $tvNumberRating;
    global $bannerId;
?>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr height="50" valign="bottom">
            <td width="18"></td>
            <td width="250">
                <img width="244" height="45" src="<?= getImageURL($bannerId, 45, 244, "Banner") ?>" />
            </td>
            <td width="30"></td>
            <td align="center" valign="center" class="tvseason"><?= ($season->IndexNumber > 0) ? "S" . $season->IndexNumber : "Sp" ?></td>
            <td width="20"></td>

            <td align="center" valign="center" class="tvyear"><?= $season->ProductionYear ?></td>

            <td width="50"></td>
            <td valign="center" style="font-size: medium"><?= $ShowAudioCodec ? audioCodec($audioStream) : null ?><?= $ShowContainer ? container($firstSource->Container) : null ?><?= $ShowVideoOutput ? videoOutput($videoStream) : null ?></td>

            <?= $season->IndexNumber < 10 ? '<td width="90"></td>' : null ?> 
            <?= ($season->IndexNumber > 9 and $season->IndexNumber < 100) ? '<td width="70"></td>' : null ?>
            <?= ($season->IndexNumber > 99 and $season->IndexNumber < 1000) ? '<td width="50"></td>' : null ?>
            <?= ($season->IndexNumber > 999) ? '<td width="30"></td>' : null ?>
            
            <td align="right" valign="center" class="rating">						
                <? 
                if ($episode->CommunityRating) 
                {
                    if ($star_rating) 
                    { ?>
                        <img hspace="10" vspace="10" src="/New/Jukebox/pictures/detail/rating_<?= round($episode->CommunityRating)*10?>.png" >
                        </img>
                    <? }
                    if ($tvNumberRating) 
                    {
                        echo "&nbsp;(" . $episode->CommunityRating . "/10)"; 
                    }
                } ?>
		    </td>			
        </tr>
    </table>
<? 
}

function printSpacerTable()
{
?>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td height="290">
            </td>
        </tr>
    </table> 
<?
}

function printLowerTable()
{
?>
<table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="30" height="314"></td>
        <td width="690" valign="top">
            <table width="100%" height="314" border="0" cellspacing="0" cellpadding="0" id="episodenInfos" class="XXhidden">
                <tr>
                    <td colspan="2" width="100%" height="74" id="episodeName" class="tveptitle" valign="top" align="left">&#160; </td>
                </tr>
                <tr>
                    <td width="320" height="240"></td>
                    <td width="410" id="episodeId" class="tvplot" align="left" valign="top">&#160; </td>
                </tr>
	        </table>
        </td>
        <td width="22" valign="top"></td>
        <td width="360" valign="top">
<?
/*
<!--list episodes-->
<xsl:variable name="iEpisodesPerPage" select="38" />

<xsl:variable name="tooManySeries">
	<xsl:choose>
		<xsl:when test="count(/details/movie/files/file/filePlot) &gt; $iEpisodesPerPage">true</xsl:when>
		<xsl:otherwise>false</xsl:otherwise>
	</xsl:choose>
</xsl:variable>
<xsl:if test="position()=20">
    <xsl:attribute name="onkeydownset">21</xsl:attribute>
</xsl:if>
*/
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" >
<tr>
<tr>
<td height="10">
<a class="TvLink" name="Play" onkeydownset="episode1" onkeyupset="episode1" >
<!--
    <xsl:if test="count(files/file/filePlot) != '1'">

        <xsl:if test="$tv-random-episode='false'"><xsl:attribute name="onkeyrightset">episode1</xsl:attribute></xsl:if>
        <xsl:if test="$tv-random-episode='true'"><xsl:attribute name="onkeyrightset">randomplay</xsl:attribute></xsl:if>
        
        <xsl:if test="$tv-extras='false'"><xsl:attribute name="onkeyleftset">episode1</xsl:attribute></xsl:if>
        <xsl:if test="$tv-extras='true'"><xsl:attribute name="onkeyleftset">refresh</xsl:attribute></xsl:if>


        <xsl:attribute name="href">
            <xsl:value-of select="concat(concat(/details/movie/baseFilename,'.playlist'),'.jsp')" />
        </xsl:attribute>   
        
        <xsl:attribute name="vod">playlist</xsl:attribute>
        <xsl:attribute name="id">gtPlay</xsl:attribute>
    </xsl:if>

    <xsl:if test="count(files/file/filePlot) = '1'">

        <xsl:if test="$tv-random-episode='false'"><xsl:attribute name="onkeyrightset">episode1</xsl:attribute></xsl:if>
        <xsl:if test="$tv-random-episode='true'"><xsl:attribute name="onkeyrightset">randomplay</xsl:attribute></xsl:if>

        <xsl:if test="$tv-extras='false'"><xsl:attribute name="onkeyleftset">episode1</xsl:attribute></xsl:if>
        <xsl:if test="$tv-extras='true'"><xsl:attribute name="onkeyleftset">refresh</xsl:attribute></xsl:if>


        <xsl:attribute name="href">
            <xsl:value-of select="/details/movie/files/file/fileURL"/>
        </xsl:attribute>

        <xsl:attribute name="vod"/>
        <xsl:if test="//movie/container = 'ISO' or substring(//files/file/fileURL,string-length(//files/file/fileURL)-3,4) = '.ISO' or substring(//files/file/fileURL,string-length(//files/file/fileURL)-3,4) = '.iso'"><xsl:attribute name="zcd">2</xsl:attribute></xsl:if>
        <xsl:if test="//movie/container = 'IMG' or substring(//files/file/fileURL,string-length(//files/file/fileURL)-3,4) = '.IMG' or substring(//files/file/fileURL,string-length(//files/file/fileURL)-3,4) = '.img'"><xsl:attribute name="zcd">2</xsl:attribute></xsl:if>
        <xsl:if test="substring(//files/file/fileURL,string-length(//files/file/fileURL)-7,8) = 'VIDEO_TS'"><xsl:attribute name="zcd">2</xsl:attribute></xsl:if>
    </xsl:if>
        -->
Play all
</a>
</td>
		<td height="10"><!--random play episode link goes here --></td>
		<td align="right" width="70">
        <!-- episode paging indicator -->
			<table border="0" cellpadding="0" cellspacing="0">
                <tr><td align="right">
                    <a href="" vod="" id="a_e_page" name="epispageCount" onfocus="" onmouseover="toggleRight()" class="TvLink" >
                    <span class="tabTvShow" id="pageCountNew">&#160;</span>
                    </a>
                </td></tr>
            </table>
		</td>
</tr>
<td width="100%" colspan="3">
<!-- episode list, write out the first 15 -->
<a id="a_e_dummy" name="episode-dummy" href="#" ></a>
<? 
    global $episodes;
    for ($i=0; $i < 15 && $i < count($episodes) ; $i++) { 
        # code...
        renderEpisodeHTML($episodes[$i],$i+1);
    }
    traktGetterIMG();
?>			
</td>
         
         <tr>
		 <td valign="top" width="80" colspan="3">
         
                <a href="#" class="tabTvShow" TVID="" onclick="" id="t_e_21"/>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr><td>
                        <a class="TvLink" href="#" vod="" id="a_e_22" name="toleft" onfocus="toggleLeft()" onmouseover="toggleLeft()">
                        <span class="tabTvShow" id="s_e_22"><img src="/New/Jukebox/pictures/1x1.png"/></span>
                        </a>
                    </td></tr>
                </table>
                <a href="#" class="tabTvShow" TVID="" onclick="" id="t_e_22"/>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr><td>
                        <a class="TvLink" href="#" vod="" id="a_e_23" name="toright" onfocus="toggleRight()" onmouseover="toggleRight()">
                        <span class="tabTvShow" id="s_e_23"><img src="/New/Jukebox/pictures/1x1.png"/></span>
                        </a>
                    </td></tr>
                </table>
                <a href="#" class="tabTvShow" TVID="" onclick="" id="t_e_23"/>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr><td>
                        <a class="TvLink" href="#" vod="" id="a_e_24" name="toup" onfocus="clickUpNew()" onmouseover="clickUpNew()">
                        <span class="tabTvShow" id="s_e_24"><img src="/New/Jukebox/pictures/1x1.png"/></span>
                        </a>
                    </td></tr>
                </table>
                <a href="#" class="tabTvShow" TVID="" onclick="" id="t_e_24"/>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr><td>
                        <a class="TvLink" href="#" vod="" id="a_e_25" name="todown" onfocus="clickDownNew()" onmouseover="clickDownNew()">
                            <span class="tabTvShow" id="s_e_25"><img src="/New/Jukebox/pictures/1x1.png"/></span>
                        </a>
                    </td></tr>
                </table>
                <a href="#" class="tabTvShow" TVID="" onclick="" id="t_e_25"/>
            
		 </td>
		 </tr>
         </tr>
		 
         </table>  		
           
  		 </td>
         </tr>
         </table>
<?
}

function printSeasonFooter()
{
?>
                </td>
            </tr>
        </table>  	
    </body>

    </html>
<?
}
?>