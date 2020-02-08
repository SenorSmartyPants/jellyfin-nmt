<?
/*
no tv rating in JF episode data?
TODO:
    Season
    Overview and OfficialRating - missing, pull separately?
    <br />
    episodes support ParentalRating, but not populated in JF
    <br />
    Support watched. Check skin to see <?= $episode->UserData->Played ?>


*/
require_once 'config.php';
include_once 'data.php';
include_once 'utils.php';
include_once 'templates.php';

const TITLETRUNCATE = 34;
const PLOTTRUNCATE = 470;
const EPISODESPERPAGE = 15;
const PCMENU = true;

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
$bannerId = null;
if ($season->ImageTags->Banner) {
    $bannerId = $season->Id;
} elseif ($series->ImageTags->Banner) {
    $bannerId = $season->SeriesId;
}
$backdrop = getBackdropIDandTag($season);

$episodesAndCount = getUsersItems(null, "Path,Overview,Height,Width,MediaSources,ProviderIds", null, $id);
$episodes = $episodesAndCount->Items;
$i=0;
do {
    $episode = $episodes[$i++];
} while ($id != $episode->SeasonId && $i < count($episodes));
//$episode == first episode from this season, not from specials


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
    $Plot = $episode->Overview;
    if (strlen($episode->Overview) > PLOTTRUNCATE) {
        $Plot = substr($episode->Overview, 0, PLOTTRUNCATE) . '...';
    }
    $Plot = addslashes(str_replace(array("\n", "\r"), '', $Plot))
?>
    <script type="text/javascript">
        asEpisodeTitle.push("<?= $episode->Name ?>");
        asEpisodeTitleShort.push("<?= substr($episode->Name, 0, TITLETRUNCATE) ?>");
        asEpisodePlot.push("<?= $Plot ?>");
        asEpisodeUrl.push("<?= translatePathToNMT(implode("/", array_map("rawurlencode", explode("/", $episode->Path)))) ?>");
        asEpisodeVod.push("vod");
        asSeasonNo.push("<?= $episode->ParentIndexNumber ?>");
        asEpisodeNo.push("<?= $episode->IndexNumber ?>");
        asEpisodeTVDBID.push("<?= $episode->ProviderIds->Tvdb ?>");
        asEpisodeWatched.push("<?= $episode->UserData->Played ?>");
        asEpisodeImage.push("<?= $episode->ImageTags->Primary ? getImageURL($episode->Id, null, 278, "Primary", null, null, $episode->ImageTags->Primary) : "/New/Jukebox/pictures/wall/transparent.png" ?>");
    </script>
<?
}

function renderEpisodeHTML($episode, $indexInList)
{
    global $season;
    if ($episode->ParentIndexNumber == 0 && $season->IndexNumber != 0) {
        //Special episode, not displaying special season, then list episode as SX. Title
        $titleLine = 'S' . $episode->IndexNumber;
    } else {
        $titleLine = sprintf('%02d', $episode->IndexNumber);
    }
    $titleLine .= '. ' . ($episode->UserData->Played ? '* ' : '') . substr($episode->Name, 0, TITLETRUNCATE);
    
?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <a class="TvLink secondaryText" id="a_e_<?= $indexInList ?>" name="episode<?= $indexInList ?>" 
                    onkeydownset="todown" onkeyrightset="toright" onkeyupset="toup" onkeyleftset="toleft" 
                    onclick="return clicked(this);" onfocus="resetGetter();"
                    onmouseover="showEpisode(<?= $indexInList ?>)" href="#playepisode<?= $indexInList ?>" season="<?= $episode->ParentIndexNumber ?>" episode="<?= $episode->IndexNumber ?>" tvdbid="<?= $episode->ProviderIds->Tvdb ?>">
                    <span class="tabTvShow" id="s_e_<?= $indexInList ?>"><?= $titleLine ?></span>
                </a>
                <a onfocusload="" 
                href="<?= translatePathToNMT(implode("/", array_map("rawurlencode", explode("/", $episode->Path)))) ?>" 
                vod="" 
                id="a2_e_<?= $indexInList ?>" name="playepisode<?= $indexInList ?>" onfocusset="episode<?= $indexInList ?>"></a>
            </td>
        </tr>
    </table><a href="#" class="tabTvShow" TVID="<?= $episode->IndexNumber ?>" onclick="setFocus(<?= $indexInList ?>); return false;" id="t_e_<?= $indexInList ?>" ></a>
<?
}

function printInitJS()
{
    global $series, $season, $episodes;
?>
    <script type="text/javascript">
        iMainSeason = <?= $season->IndexNumber ?>;

        var QS = location.search.substring(1);
        var params = QS.split('&');

        focusEpisodeNo = 1;

        for (var i = 0; i < params.length; i++) {
            var pair = params[i].split('=');
            if (decodeURIComponent(pair[0]) == 'episode') {
                focusEpisodeNo = pair[1];
            }
        }

        asEpisodeTitle = new Array('0');
        asEpisodeTitleShort = new Array('0');
        asEpisodePlot = new Array('0');
        asEpisodeUrl = new Array('0');
        asEpisodeVod = new Array('0');
        asSeasonNo = new Array('0');
        asEpisodeNo = new Array('0');
        asEpisodeTVDBID = new Array('0');
        asEpisodeWatched = new Array('0');
        asEpisodeImage = new Array('0');
    </script>

<?

foreach ($episodes as $episode) {
    renderEpisodeJS($episode);
}

?>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/season.js"></script>
<?
if (count($episodes) > EPISODESPERPAGE) {
?>    <script type="text/javascript" src="js/season/episodePaging.js"></script>
<?
}
?>
    <script type="text/javascript">

        var sPlotLong = "<?= str_replace(array("\n", "\r"), '', $series->Overview) ?>";
        var sTitleLong = "<?= $series->Name ?>";
        var fWatch = true;
        var fTVplaylist = false;
    </script>

    <script type="text/javascript">
        function clicked(link) {
            var title = "<?= $series->Name ?>";
            var year = "<?= $season->ProductionYear ?>";
            var tvdb_id = "<?= $series->ProviderIds->Tvdb ?>";

            var season = link.getAttribute('season');
            var episode = link.getAttribute('episode');
            var episode_id = link.getAttribute('tvdbid');

            var url = "http://rockpi:8123/trakt-proxy/checkin.php?tvdb_id=" + tvdb_id + "&season=" + season + "&episode=" + episode + "&episode_id=" + episode_id +
                "&title=" + encodeURIComponent(title) + "&year=" + year;
            var getter = document.getElementById('getter');
            getter.setAttribute('src', url);
            return true;
        }

        function resetGetter() {
            var getter = document.getElementById('getter');
            getter.setAttribute('src', '#');
        };
    </script>  

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
        <link rel="StyleSheet" type="text/css" href="css/Season.css" />
        <link rel="StyleSheet" type="text/css" href="/New/Jukebox/no_nmt.css" media="screen" />
<?
    printInitJS();
?>
    </head>

    <body bgproperties="fixed" onloadset="episode1" onload="init()" bgcolor="#000000" focustext="#FFFFFF" FOCUSCOLOR="transparent" 
    <? if ($backdrop->Tag) 
    { 
        ?> background="<?= getImageURL($backdrop->Id, 720, 1280, "Backdrop", null, null, $backdrop->Tag) ?>"<?   
    }
    ?>>
    <table height="656" width="1102" border="0" cellspacing="0" cellpadding="0" background="/New/Jukebox/pictures/sabishmod/tvbg-v2.png">
        <tr>
            <td valign="top">
<?
}

function TopBarSpacerWidth($seasonIndexNumber)
{
    if ($seasonIndexNumber < 10) 
    {
        $width = 90;
    } 
    elseif ($seasonIndexNumber > 9 && $seasonIndexNumber < 100)
    {
        $width = 70;
    }
    elseif ($seasonIndexNumber > 99 && $seasonIndexNumber < 1000) 
    {
        $width = 50;
    }
    elseif ($seasonIndexNumber > 999) 
    {
        $width = 30;
    }
    return $width;
}

function printTopBar()
{
    global $series, $season, $videoStream, $audioStream, $firstSource;
    global $ShowAudioCodec, $ShowContainer, $ShowVideoOutput, $star_rating, $tvNumberRating;
    global $bannerId;
?>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr height="62" valign="middle">
            <td width="18"></td>
            <td width="250"><? if ($bannerId) { ?> 
                <img width="244" height="45" src="<?= getImageURL($bannerId, 45, 244, "Banner") ?>" />
            <? } ?></td>
            <td width="30"></td>
            <td align="center" class="tvseason"><?= ($season->IndexNumber > 0) ? "S" . $season->IndexNumber : "Sp" ?></td>
            <td width="20"></td>
            <td align="center" class="tvyear secondaryText"><?= $season->ProductionYear ?></td>
            <td width="50"></td>
            <?= $ShowAudioCodec ? '<td>' . audioCodec($audioStream) . '</td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowContainer ? '<td>' . container($firstSource->Container) . '</td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowVideoOutput ? '<td>' . videoOutput($videoStream) . "</td>\n"  : null ?>
            <td width="<?= TopBarSpacerWidth($season->IndexNumber) ?>"></td>
            <td align="right" class="rating"><? 
                if ($series->CommunityRating) 
                {
                    if ($star_rating) 
                    { ?>
                        <img hspace="10" vspace="10" src="/New/Jukebox/pictures/detail/rating_<?= round($series->CommunityRating)*10?>.png" />
                    <? }
                    if ($tvNumberRating) 
                    {
                        echo "&nbsp;(" . $series->CommunityRating . "/10)"; 
                    }
                } 
            ?></td>			
        </tr>
    </table>
<? 
}

function printSpacerTable()
{
?>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td height="278"></td>
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
            <table width="100%" height="314" border="0" cellspacing="0" cellpadding="0" id="episodenInfos">
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

<table width="100%" border="0" cellspacing="0" cellpadding="0" >
<tr>
<td height="10">
<a class="TvLink secondaryText" id="gtPlay" name="Play" onkeydownset="episode1" onkeyupset="episode1" >
Play all
</a>
</td>
		<td height="10"><!--random play episode link goes here --></td>
		<td align="right" width="70">
        <!-- episode paging indicator -->
			<table border="0" cellpadding="0" cellspacing="0">
                <tr><td align="right">
                    <a href="" vod="" id="a_e_page" name="epispageCount" onfocus="" onmouseover="toggleRight()" class="TvLink secondaryText" >
                    <span class="tabTvShow" id="pageCount">&#160;</span>
                    </a>
                </td></tr>
            </table>
		</td>
</tr>
<tr>
<td width="100%" colspan="3">
<!-- episode list, write out the first X -->
<a id="a_e_dummy" name="episode-dummy" href="#" ></a>
<? 
    global $episodes;
    for ($i=0; $i < EPISODESPERPAGE && $i < count($episodes) ; $i++) { 
        # code...
        renderEpisodeHTML($episodes[$i],$i+1);
    }
    traktGetterIMG();
?>			
</td>
</tr>  
    <tr>
		<td valign="top" width="80" colspan="3">
            <a href="#" name="toleft" onfocus="toggleLeft()"></a>
            <a href="#" name="toright" onfocus="toggleRight()"></a>
            <a href="#" name="toup" onfocus="clickUp()"></a>
            <a href="#" name="todown" onfocus="clickDown()"></a>
		</td>
	</tr>
		 
         </table>  		
           
  		 </td>
         </tr>
         </table>
<?
}

function printPCMenu()
{
?>
    <div id="noNMT">
        <a href="#" class="clickInfos" onclick="showSeasonInfo(); return false;">Season Summary</a>
        <a href="#" class="clickUp" onclick="clickUp(); return false;">Click Up</a>
        <a href="#" class="clickDown" onclick="clickDown(); return false;">Click Down</a><br />
        <a href="#" class="pageUp" onclick="toggleLeft(); return false;">Previous Page</a>
        <a href="#" class="pageDown" onclick="toggleRight(); return false;">Next Page</a><br />
        <a class="clickIndex" href="">Index</a><br />
    </div>
<?
}

function printSeasonFooter()
{
?>
                </td>
            </tr>
        </table>  	
    <a TVID="INFO" name="gt_tvshow" href="#" onclick="showSeasonInfo()"></a>
    <a id="openEpisode" TVID="Play" href="#" vod=""></a>
    <a href="#" onclick="return  toggleEpisodeDetails();" tvid=""></a>
    <div id="popupWrapper">
        <div id="divEpisodeImgBackSabish" class="abs"><img src="/New/Jukebox/pictures/sabishmod/epi_back.png" width="308" id="episodeImgBack" class="hidden" /></div>
        <div id="divEpisodeImgSabish" class="abs"><img src="/New/Jukebox/pictures/wall/transparent.png" width="278" height="164" id="episodeImg" class="hidden" /></div>
        <div id="divEpisodeCertification" class="abs"><img src="/New/Jukebox/pictures/certificates/tv_ma.png" /></div>
    </div>
<?
    if (PCMENU) {
        printPCMenu();
    }
?>
    <a TVID="HOME" href="index.php"></a>
    <a TVID="PGDN" ONFOCUSLOAD="" name="pgdn" href=""></a>
    <a TVID="PGUP" ONFOCUSLOAD="" name="pgup" href=""></a>

    </body>

    </html>
<?
}
?>