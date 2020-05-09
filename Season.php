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
include_once 'basepage.php';
include_once 'templates.php';

const TITLETRUNCATELONG = 56;
const TITLETRUNCATE = 40;
const PLOTTRUNCATE = 470;
const EPISODESPERPAGE = 15;

$ShowAudioCodec = true;
$ShowContainer = true;
$ShowVideoOutput = true;
$star_rating = true;
$tvNumberRating = false;

$id = $_GET["id"];
$selectedEpisodeIndexNumber = $_GET["episode"];

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
$episodeCount = $episodesAndCount->TotalRecordCount;

$epPages = 1 + intdiv(($episodeCount - 1), EPISODESPERPAGE);

$i=0;
do {
    $episode = $episodes[$i++];
} while ($id != $episode->SeasonId && $i < $episodeCount);
//$episode == first episode from this season, not from specials

$selectedEpisode = $episodes[0];
foreach($episodes as $key => $episode) {
    if ($selectedEpisodeIndexNumber == $episode->IndexNumber && $id == $episode->SeasonId) {
        $selectedEpisode = $episode;
        $selectedEpisodeArrayIndex = $key + 1;
        break;
    }
}


$selectedPage = 1 + intdiv(($selectedEpisodeArrayIndex - 1), EPISODESPERPAGE);

$streams = getStreams($selectedEpisode);

printBaseHeadEtc('episode' . (($selectedEpisodeArrayIndex - 1) % EPISODESPERPAGE + 1), "Season.css", $season->Name . ' - ' . $season->SeriesName, 'printInitJS', 'init()', 'transparent');
printTopBar();
printSpacerTable();
printLowerTable();
printSeasonFooter();

function titleCSS($length)
{
    if ($length <= 35) {
        $cssClass = "tveptitle";
    }
    else if ($length <= 38) {
        $cssClass = "tveptitle24";
    }
    else if ($length <= 43) {
        $cssClass = "tveptitle22";
    }
    else if ($length <= 46) {
        $cssClass = "tveptitle20";
    }
    else if ($length <= 53) {
        $cssClass = "tveptitle18";
    }
    else {
        $cssClass = "tveptitle16";
    }
    return $cssClass;
}

function truncateTitle($title)
{
    if (strlen($title) > TITLETRUNCATELONG) {
        $title = substr($title, 0, TITLETRUNCATELONG) . '...';
    }
    return $title;
}

function truncatePlot($Plot, $JSescape = false)
{
    if (strlen($Plot) > PLOTTRUNCATE) {
        $Plot = substr($Plot, 0, PLOTTRUNCATE) . '...';
    }
    if ($JSescape) {
        $Plot = addslashes(str_replace(array("\n", "\r"), '', $Plot));
    }
    return $Plot;
}

function renderEpisodeJS($episode)
{
    $Plot = truncatePlot($episode->Overview, true);
?>
    <script type="text/javascript">
        asEpisodeTitle.push("<?= truncateTitle($episode->Name) ?>");
        asEpisodeTitleCSS.push("<?= titleCSS(strlen($episode->Name)) ?>");
        asEpisodeTitleShort.push("<?= substr($episode->Name, 0, TITLETRUNCATE) ?>");
        asEpisodePlot.push("<?= $Plot ?>");
        asEpisodeUrl.push("<?= translatePathToNMT($episode->Path) ?>");
        asEpisodeVod.push("vod");
        asSeasonNo.push("<?= $episode->ParentIndexNumber ?>");
        asEpisodeNo.push("<?= $episode->IndexNumber ?>");
        asEpisodeTVDBID.push("<?= $episode->ProviderIds->Tvdb ?>");
        asEpisodeWatched.push("<?= $episode->UserData->Played ?>");
        asEpisodeImage.push("<?= $episode->ImageTags->Primary ? getImageURL($episode->Id, null, 278, "Primary", null, null, $episode->ImageTags->Primary) : "images/wall/transparent.png" ?>");
    </script>
<?
}

function renderEpisodeHTML($episode, $indexInList, $episodeIndex)
{
    global $season;
    if ($episode) {
        if ($episode->ParentIndexNumber == 0 && $season->IndexNumber != 0) {
            //Special episode, not displaying special season, then list episode as SX. Title
            $titleLine = 'S' . $episode->IndexNumber;
        } else {
            $titleLine = sprintf('%02d', $episode->IndexNumber);
        }
        $titleLine .= '. ' . ($episode->UserData->Played ? '* ' : '') . htmlspecialchars(substr($episode->Name, 0, TITLETRUNCATE), ENT_NOQUOTES);
    }

    #region videoPlayLink setup
    $attrs = array(
        "class" => "TvLink secondaryText",
        "id" => "a_e_" . $indexInList,
        "onkeydownset" => "todown",
        "onkeyrightset" => "toright",
        "onkeyupset" => "toup",
        "onkeyleftset" => "toleft",
        "onmouseover" => "showEpisode(" . $episodeIndex . ")"
    );
    $linkHTML = '<span class="tabTvShow" id="s_e_' . $indexInList . '">' . $titleLine . '&nbsp;</span>';
    $linkName = "episode" . $indexInList;

    if (CHECKIN) {
        $callbackJS = "checkin();";
        $callbackName = "playepisode" . $indexInList;
        $callbackAdditionalAttributes = array('id' => 'a2_e_' . $indexInList);
    }
    #endregion

?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <?= videoPlayLink($episode, $linkHTML, $linkName, $attrs, $callbackJS, $callbackName, $callbackAdditionalAttributes) ?> 
            </td>
        </tr>
    </table><a href="#" class="tabTvShow" TVID="<?= $episode->IndexNumber ?>" onclick="setFocus(<?= $indexInList ?>); return false;" id="t_e_<?= $indexInList ?>" ></a>
<?
}

function printInitJS()
{
    global $series, $season, $episodes, $selectedPage, $epPages, $episodeCount, $selectedEpisodeArrayIndex;
?>
    <script type="text/javascript">
        iMainSeason = <?= $season->IndexNumber ?>;

        var iPage = <?= $selectedPage ?> //selected page
        var iEpPages = <?= $epPages ?>;
        var iEpisodeId = <?= $selectedEpisodeArrayIndex ?? 1 ?>;
        
        var iEpisodesPerPage = <?= EPISODESPERPAGE ?>;

        var fmorePages = <?= $epPages > 1 ? 'true':'false' ?>;

        asEpisodeTitle = new Array('0');
        asEpisodeTitleCSS = new Array('0');
        asEpisodePlot = new Array('0');
        asEpisodeUrl = new Array('0');
        asSeasonNo = new Array('0');
        asEpisodeNo = new Array('0');
        asEpisodeTVDBID = new Array('0');
        asEpisodeImage = new Array('0');
        //the following are only used for episode paging
        asEpisodeTitleShort = new Array('0');
        asEpisodeVod = new Array('0');
        asEpisodeWatched = new Array('0');
    </script>

<?

foreach ($episodes as $episode) {
    renderEpisodeJS($episode);
}

?>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/season/season.js"></script>
<?
if ($episodeCount > EPISODESPERPAGE) {
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
<? if (CHECKIN) { ?>
    <script type="text/javascript" src="js/empty.js" id="checkinjs"></script>
    <script type="text/javascript">
        function checkin() {
            var url = "<?= CHECKIN_URL ?>?tvdb_id=<?= $series->ProviderIds->Tvdb ?>&title=<?= rawurlencode($series->Name) ?>&year=<?= $series->ProductionYear ?>&season=" + 
                asSeasonNo[iEpisodeId] + "&episode=" + asEpisodeNo[iEpisodeId] + "&episode_id=" + asEpisodeTVDBID[iEpisodeId];
             
            document.getElementById("checkinjs").setAttribute('src', url + "&JS=true");
        }

        function callback(id, inlineMsg) {
            document.getElementById("checkinjs").setAttribute('src', "js/empty.js");
        }
    </script>  

<?
    }

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
    global $series, $season, $streams;
    global $ShowAudioCodec, $ShowContainer, $ShowVideoOutput, $star_rating, $tvNumberRating;
    global $bannerId;
?>
<table height="656" width="1102" border="0" cellspacing="0" cellpadding="0" background="images/season/background.png">
<tr>
    <td valign="top">
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
            <?= $ShowAudioCodec ? '<td>' . audioCodec($streams->Audio) . '</td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowContainer ? '<td>' . container($streams->Container) . '</td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowVideoOutput ? '<td>' . videoOutput($streams->Video) . "</td>\n"  : null ?>
            <td width="<?= TopBarSpacerWidth($season->IndexNumber) ?>"></td>
            <td align="right" class="rating"><? 
                if ($series->CommunityRating) 
                {
                    if ($star_rating) 
                    { ?>
                        <img hspace="10" vspace="10" src="images/detail/rating_<?= round($series->CommunityRating)*10?>.png" />
                    <? }
                    if ($tvNumberRating) 
                    {
                        echo "&nbsp;(" . $series->CommunityRating . "/10)"; 
                    }
                } 
            ?></td>			
        </tr>
    </table>
    </td>
</tr>
<? 
}

function printSpacerTable()
{
?>
        <tr>
            <td height="278"></td>
        </tr>
<?
}

function printLowerTable()
{
    global $selectedEpisode, $episodeCount, $epPages, $selectedPage;
?>
<tr>
    <td>

<table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="30" height="314"></td>
        <td width="690" valign="top">
            <table width="100%" height="314" border="0" cellspacing="0" cellpadding="0" id="episodenInfos">
                <tr>
                    <td colspan="2" width="100%" height="50" id="episodeName" class="<?= titleCSS(strlen($selectedEpisode->Name)) ?>" valign="middle" align="left"><?= truncateTitle($selectedEpisode->Name) ?></td>
                </tr>
                <tr>
                    <td colspan="2" height="24" ></td>
                </tr>
                <tr>
                    <td width="320" height="240"></td>
                    <td width="410" id="episodeId" class="tvplot" align="left" valign="top"><?= truncatePlot($selectedEpisode->Overview) ?? "&#160; " ?></td>
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
                    <a href="" id="a_e_page" name="epispageCount" onmouseover="toggleRight()" class="TvLink secondaryText" >
                    <span class="tabTvShow" id="pageCount"><? if ($epPages > 1) { echo $selectedPage . ' / ' . $epPages . ' (' . $episodeCount . ')'; } ?></span>
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
    $episodeOffset = ($selectedPage - 1) * EPISODESPERPAGE;
    for ($i=0; $i < EPISODESPERPAGE && $i < $episodeCount ; $i++) { 
        $episodeIndex = $episodeOffset + $i;
        renderEpisodeHTML($episodes[$episodeIndex], $i + 1, $episodeIndex + 1);
    }
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

    </td>
</tr>
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
    global $series, $season, $selectedEpisode;
?>
        </table>  	
    <a TVID="INFO" name="gt_tvshow" href="#" onclick="showSeasonInfo()"></a>
    <a id="openEpisode" TVID="Play" <?= videoAttributes($selectedEpisode) ?> ></a>
    <a href="#" onclick="return  toggleEpisodeDetails();" tvid=""></a>
    <div id="popupWrapper">
        <div id="divEpisodeImgBackSabish" class="abs"><img src="images/season/epi_back.png" width="308" id="episodeImgBack"/></div>
        <div id="divEpisodeImgSabish" class="abs"><img src="<?= $selectedEpisode->ImageTags->Primary ? getImageURL($selectedEpisode->Id, null, 278, "Primary", null, null, $selectedEpisode->ImageTags->Primary) : "images/wall/transparent.png" ?>" width="278" height="164" id="episodeImg"/></div>
        <div id="divEpisodeCertification" class="abs"><?= officialRating($series->OfficialRating) ?></div>
    </div>
<?
    if (PCMENU) {
        printPCMenu();
    }
?>
    <a TVID="HOME" href="index.php"></a>
    <a TVID="PGDN" ONFOCUSLOAD="" name="pgdn" href=""></a>
    <a TVID="PGUP" ONFOCUSLOAD="" name="pgup" href=""></a>

    <a TVID="RED" href="<?= itemDetailsLink($season->Id) ?>"></a>
    </body>

    </html>
<?
}
?>