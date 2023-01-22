<?php
/*
no tv rating in JF episode data?
TODO:
    Season
    Overview and OfficialRating - missing, pull separately?
    episodes support ParentalRating, but not populated in JF
*/
require_once 'config.php';
include_once 'data.php';
include_once 'utils.php';
include_once 'page.php';
include_once 'menuItems.php';
include_once 'utils/javascript.php';
include_once 'utils/arrayCallbacks.php';
include_once 'utils/checkinJS.php';
include_once 'utils/templates.php';

const TITLETRUNCATELONG = 56;
const TITLETRUNCATE = 38;
const PLOTTRUNCATE = 470;
const EPISODESPERPAGE = 15;
const EPISODE = 'episode';

$ShowAudioCodec = true;
$ShowContainer = true;
$ShowVideoOutput = true;
$star_rating = true;
$tvNumberRating = false;

$id = htmlspecialchars($_GET["id"]);
$selectedEpisodeIndexNumber = htmlspecialchars($_GET[EPISODE]);
$special = isset($_GET['special']);

$pageObj = new Page('');
$pageObj->additionalCSS = 'Season.css';
$pageObj->InitJSFunction = 'printInitJS';
$pageObj->onload ='init()';
$pageObj->focuscolor ='transparent';

//Banners don't inherit from parents
//have to load season to find out if it has a banner
$season = getItem($id);
$series = getItem($season->SeriesId);
$pageObj->title = $season->Name . ' - ' . $season->SeriesName;
$bannerId = null;
if ($season->ImageTags->Banner) {
    $bannerId = $season->Id;
} elseif ($series->ImageTags->Banner) {
    $bannerId = $season->SeriesId;
}
$pageObj->backdrop = getBackdropIDandTag($season);

//get skip and trim from tags
$skipTrim = new SkipAndTrim($series);

$params = new UserItemsParams();
$params->Fields = 'Path,Overview,Height,Width,MediaSources,ProviderIds';
$params->ParentID = $id;

$episodesAndCount = getUsersItems($params);
$episodes = $episodesAndCount->Items;
$episodeCount = $episodesAndCount->TotalRecordCount;

if ($season->SpecialFeatureCount && $season->SpecialFeatureCount > 0) {
    //Special Features
    $specialfeatures = getItemExtras($id, ExtrasType::SPECIALFEATURES);
    // merge season extras into end of episode list
    $episodes = array_merge($episodes, $specialfeatures);
    $episodeCount += $season->SpecialFeatureCount;
}

$epPages = 1 + intdiv(($episodeCount - 1), EPISODESPERPAGE);

$i=0;
do {
    $episode = $episodes[$i++];
} while ($id != $episode->SeasonId && $i < $episodeCount);
//$episode == first episode from this season, not from specials

$selectedEpisode = $episodes[0];
foreach ($episodes as $key => $episode) {
    if (
        $selectedEpisodeIndexNumber == $episode->IndexNumber &&
        ((!$special && $id == $episode->SeasonId) || ($special && $episode->ParentIndexNumber == 0))
    ) {
        $selectedEpisode = $episode;
        $selectedEpisodeArrayIndex = $key + 1;
        break;
    }
}

$pageObj->onloadset = EPISODE . (($selectedEpisodeArrayIndex - 1) % EPISODESPERPAGE + 1);

$selectedPage = 1 + intdiv(($selectedEpisodeArrayIndex - 1), EPISODESPERPAGE);

$streams = getStreams($selectedEpisode);

$pageObj->printHead();
printTopBar();
printSpacerTable();
printLowerTable();
printSeasonFooter();

function titleCSS($length)
{
    if ($length <= 35) {
        $cssClass = "tveptitle";
    } elseif ($length <= 38) {
        $cssClass = "tveptitle24";
    } elseif ($length <= 43) {
        $cssClass = "tveptitle22";
    } elseif ($length <= 46) {
        $cssClass = "tveptitle20";
    } elseif ($length <= 53) {
        $cssClass = "tveptitle18";
    } else {
        $cssClass = "tveptitle16";
    }
    return $cssClass;
}

function truncateTitle($title)
{
    return truncate($title, TITLETRUNCATELONG);
}

function truncatePlot($Plot, $JSescape = false)
{
    return truncate($Plot, PLOTTRUNCATE, $JSescape);
}

function renderEpisodeHTML($episode, $indexInList, $episodeIndex)
{
    global $season, $skipTrim;
    if ($episode) {
        if ($episode->ExtraType) {
            //Extra/Special featre Ex. Title
            $titleLine = 'Ex';
        } elseif ($episode->ParentIndexNumber == 0 && $season->IndexNumber != 0) {
            //Special episode, not displaying special season, then list episode as Sp. Title
            $titleLine = 'Sp';
        } else {
            $titleLine = sprintf('%02d', $episode->IndexNumber);
        }
        //check for multi-part episode
        if ($episode->IndexNumberEnd) {
            $titleLine .= '-' . $episode->IndexNumberEnd;
        }
        $titleLine .= '. ' . ($episode->UserData->Played ? '* ' : '') . htmlspecialchars(mb_substr($episode->Name, 0, TITLETRUNCATE), ENT_NOQUOTES);
    }

    #region videoPlayLink setup
    $attrs = array(
        "class" => "TvLink secondaryText",
        "id" => "a_e_" . $indexInList,
        "onkeydownset" => "todown",
        "onkeyrightset" => "toright",
        "onkeyupset" => "toup",
        "onkeyleftset" => "toleft",
        "onmouseover" => "show(" . $episodeIndex . ")"
    );
    $linkHTML = '<span id="s_e_' . $indexInList . '">' . $titleLine . '&nbsp;</span>';
    $linkName = EPISODE . $indexInList;

    $callbackJS = CheckinJS::getCallback($skipTrim);
    $callbackName = "playepisode" . $indexInList;
    $callbackAdditionalAttributes = array('id' => 'a2_e_' . $indexInList);
    #endregion

?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <?= videoPlayLink($episode, $linkHTML, $linkName, $attrs, $callbackJS, $callbackName, $callbackAdditionalAttributes) ?>
            </td>
        </tr>
    </table><a href="#" TVID="<?= $episode->IndexNumber ?>" onclick="setFocus(<?= $indexInList ?>); return false;" id="t_e_<?= $indexInList ?>" ></a>
<?
}

function printInitJS()
{
    global $series, $season, $episodes, $selectedPage, $epPages, $episodeCount, $selectedEpisodeArrayIndex;
    global $skipTrim;
?>
    <script type="text/javascript">
        iMainSeason = <?= $season->IndexNumber ?>;

        var iPage = <?= $selectedPage ?> //selected page
        var iEpPages = <?= $epPages ?>;

        var iEpisodesPerPage = <?= EPISODESPERPAGE ?>;

        var fmorePages = <?= $epPages > 1 ? 'true':'false' ?>;

    </script>
<?
    CheckinJS::render($episodes, $selectedEpisodeArrayIndex);

    $asVideoOutput = array_map(function ($i) { return videoOutputImageURL(getStreams($i)->Video); }, $episodes);
    $asContainer = array_map(function ($i) { return containerImageURL($i->MediaSources[0]->Container); }, $episodes);
    $asAudioCodec = array_map(function ($i) { return audioCodecImageURL(getStreams($i)->Audio); }, $episodes);
    $asAudioChannels = array_map(function ($i) { return audioChannelsImageURL(getStreams($i)->Audio); }, $episodes);
    $asAspectRatios = array_map(function ($i) { return getAspectRatioURL(getStreams($i)->Video); }, $episodes);

    global $asVideoOutputUnique, $asContainerUnique, $asAudioCodecUnique, $asAudioChannelsUnique, $asAspectRatiosUnique;
    $asVideoOutputUnique = array_unique($asVideoOutput);
    $asContainerUnique = array_unique($asContainer);
    $asAudioCodecUnique = array_unique($asAudioCodec);
    $asAudioChannelsUnique = array_unique($asAudioChannels);
    $asAspectRatiosUnique = array_unique($asAspectRatios);
    //using multiple script blocks to stay under 23k byte limit
?>
    <script type="text/javascript">
        //season.js variables
        var asEpisodeTitle = <?= getJSArray(array_map('getTruncateTitle', $episodes), true, '0')?>;
        var asEpisodeTitleCSS = <?= getJSArray(array_map('getTitleCSS', $episodes), false, '0')?>;
        var asRuntime = <?= getJSArray(array_map('runtimeDescription', $episodes), true, '0', true)?>;
    </script>
    <script type="text/javascript">
<?
        echo count($asVideoOutputUnique) > 1 ? "\t\tvar asVideoOutput = " . getJSArray($asVideoOutput, true, '0') . ";\n" : '';
        echo count($asContainerUnique) > 1 ? "\t\tvar asContainer = " . getJSArray($asContainer, true, '0') . ";\n" : '';
        echo count($asAudioCodecUnique) > 1 ? "\t\tvar asAudioCodec = " . getJSArray($asAudioCodec, true, '0') . ";\n" : '';
        echo count($asAudioChannelsUnique) > 1 ? "\t\tvar asAudioChannels = " . getJSArray($asAudioChannels, true, '0') . ";\n" : '';
        echo count($asAspectRatiosUnique) > 1 ? "\t\tvar asAspectRatios = " . getJSArray($asAspectRatios, true, '0') . ";\n" : '';
?>

        function showMediainfo(episodeIndex) {
<?
        echo count($asVideoOutputUnique) > 1 ? "\t\t\telVideoOutputImg.setAttribute(\"src\", asVideoOutput[episodeIndex]);\n" : '';
        echo count($asContainerUnique) > 1 ? "\t\t\telContainerImg.setAttribute(\"src\", asContainer[episodeIndex]);\n" : '';
        echo count($asAudioCodecUnique) > 1 ? "\t\t\telAudioCodecImg.setAttribute(\"src\", asAudioCodec[episodeIndex]);\n" : '';
        echo count($asAudioChannelsUnique) > 1 ? "\t\t\telAudioChannelsImg.setAttribute(\"src\", asAudioChannels[episodeIndex]);\n" : '';
        echo count($asAspectRatiosUnique) > 1 ? "\t\t\telAspectRatioImg.setAttribute(\"src\", asAspectRatios[episodeIndex]);\n" : '';
?>
        }
    </script>
    <script type="text/javascript">
        var asEpisodePlot = <?= getJSArray(array_map('getPlot', $episodes), true, '0')?>;
    </script>
    <script type="text/javascript">
        var asEpisodeAdditionalAudio = <?= getJSArray(array_map('getFirstAdditionalAudio', $episodes), true, '0')?>;
    </script>
    <script type="text/javascript">
        var asEpisodeImage = <?= getJSArray(array_map('getImage', $episodes), true, '0')?>;
    </script>
    <script type="text/javascript">
        //both season.js and episodePaging.js
        //not really used by my code season, used by paging
        var asEpisodeUrl = <?= getJSArray(array_map('getURL', $episodes), true, '0')?>;

        //used to make episode list item text
        var asEpisodeWatched = <?= getJSArray(array_map('getPlayed', $episodes), false, '0')?>;
        var asEpisodeTitleShort = <?= getJSArray(array_map('getShortTitle', $episodes), true, '0')?>;
        var asSeasonNo = <?= getJSArray(array_map('getParentIndexNumber', $episodes), false, '0')?>;
        var asEpisodeNo = <?= getJSArray(array_map('getIndexNumber', $episodes), false, '0')?>;
        var asEpisodeNoEnd = <?= getJSArray(array_map('getIndexNumberEnd', $episodes), false, '0')?>;
    </script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/uiUpdateUtils.js"></script>
    <script type="text/javascript" src="js/season/season.js"></script>
<?
if ($episodeCount > EPISODESPERPAGE) {
?>
    <script type="text/javascript">
        //episodePaging.js
        var asEpisodeVod = <?= getJSArray(array_map('getVOD', $episodes), false, '0')?>;
    </script>
    <script type="text/javascript" src="js/season/episodePaging.js"></script>
<?
}
?>
    <script type="text/javascript">
        var sPlotLong = "<?= JSEscape($series->Overview) ?>";
        var sTitleLong = "<?= $series->Name ?>";
        var fWatch = true;
        var fTVplaylist = false;
    </script>
<?
}

function TopBarSpacerWidth($seasonIndexNumber)
{
    if ($seasonIndexNumber < 10) {
        $width = 90;
    } elseif ($seasonIndexNumber > 9 && $seasonIndexNumber < 100) {
        $width = 70;
    } elseif ($seasonIndexNumber > 99 && $seasonIndexNumber < 1000) {
        $width = 50;
    } elseif ($seasonIndexNumber > 999) {
        $width = 30;
    }
    $width += 131;
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
                <img width="244" height="45" src="<?= getImageURL($bannerId, new ImageParams(45, 244), ImageType::BANNER) ?>" />
            <? } ?></td>
            <td width="30"></td>
            <td align="center" class="tvseason"><?= ($season->IndexNumber > 0) ? "S" . $season->IndexNumber : "Sp" ?></td>
            <td width="20"></td>
            <td align="center" class="tvyear secondaryText"><?= $season->ProductionYear ?></td>
            <td width="50"></td>
            <?= $ShowVideoOutput ? '<td><img id="videoOutput" src="' . videoOutputImageURL($streams->Video) . '"/></td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowContainer ? '<td><img id="container" src="' . containerImageURL($streams->Container) . '"/></td><td width="9"></td>' . "\n" : null ?>
            <?= $ShowAudioCodec ? '<td width="146"><img id="audioCodec" align="top" src="' . audioCodecImageURL($streams->Audio) . '"/><img id="audioChannels" align="top" src="' . audioChannelsImageURL($streams->Audio) . '"/></td>' . "\n" : null ?>
            <td width="<?= TopBarSpacerWidth($season->IndexNumber) ?>" align="right" class="rating"><?
            //TODO: use episode CommunityRating?
                if ($series->CommunityRating) {
                    if ($star_rating) { ?>
                        <img hspace="10" vspace="10" src="images/detail/rating_<?= round($series->CommunityRating)*10?>.png" />
                    <? }
                    if ($tvNumberRating) {
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
                    <td width="410" class="tvplot" align="left" valign="top"><p id="episodePlot"><?= truncatePlot($selectedEpisode->Overview) ?? "&#160; " ?></p><p id="episodeAdditionalAudio"><?= getFirstAdditionalAudio($selectedEpisode) ?></p></td>
                </tr>
	        </table>
        </td>
        <td width="22" valign="top"></td>
        <td width="360" valign="top">

<table width="100%" border="0" cellspacing="0" cellpadding="0" >
<tr>
        <td></td>
		<td></td>
		<td align="right" width="70">
        <!-- episode paging indicator -->
			<table border="0" cellpadding="0" cellspacing="0">
                <tr><td align="right">
                    <a href="" id="a_e_page" name="epispageCount" onmouseover="toggleRight()" class="TvLink secondaryText" >
                    <? if ($epPages > 1) { echo '<span id="currentPage">' . $selectedPage . '</span> / ' . $epPages . ' (' . $episodeCount . ')'; } ?>
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
    <div id="popupWrapper"><div id="noNMT">
        <a href="#" class="clickInfos" onclick="showSeasonInfo(); return false;">Season Summary</a>
        <a href="#" class="clickUp" onclick="clickUp(); return false;">Click Up</a>
        <a href="#" class="clickDown" onclick="clickDown(); return false;">Click Down</a><br />
        <a href="#" class="pageUp" onclick="toggleLeft(); return false;">Previous Page</a>
        <a href="#" class="pageDown" onclick="toggleRight(); return false;">Next Page</a><br />
        <a class="clickIndex" href="">Index</a><br />
    </div></div>
<?
}

function printSeasonFooter()
{
    global $series, $season, $selectedEpisode;
    global $tvid_season_info, $tvid_season_play,
        $tvid_season_pgup, $tvid_season_pgdn,
        $tvid_season_itemdetails, $tvid_season_series;
    global $pageObj;
?>
        </table>
    <a TVID="<?= $tvid_season_info ?>" name="gt_tvshow" href="#" onclick="showSeasonInfo()"></a>
    <a id="openEpisode" TVID="<?= $tvid_season_play ?>" <?= videoAttributes($selectedEpisode) ?> ></a>
    <a href="#" onclick="return  toggleEpisodeDetails();" tvid=""></a>
    <div id="popupWrapper">
        <div id="divEpisodeImgBackSabish" class="abs"><img src="images/season/epi_back.png" width="308" id="episodeImgBack"/></div>
        <div id="divEpisodeImgSabish" class="abs"><img id="episodeImg" src="<?= $selectedEpisode->ImageTags->Primary ? getImageURL($selectedEpisode->Id, new ImageParams(null, 278, $selectedEpisode->ImageTags->Primary), ImageType::PRIMARY) : "images/wall/transparent.png" ?>" width="278" height="164"/></div>
        <div id="divEpisodeCertification" class="abs"><img id="episodeOfficialRating" src="<?= officialRatingImageURL($series) ?>"/></div>
        <div id="runtime" class="abs TvLink"><?= runtimeDescription($selectedEpisode, false) ?></div>
        <div id="divEpisodeAR" class="abs"><img id="aspectRatio" src="<?= getAspectRatioURL(getStreams($selectedEpisode)->Video)?>" /></div>
    </div>
<?
    if ($pageObj->PCMenu) {
        printPCMenu();
    }
    $seriesMenuItem = parse($series);
?>
    <a TVID="<?= $tvid_season_pgup ?>" ONFOCUSLOAD="" name="pgdn" href=""></a>
    <a TVID="<?= $tvid_season_pgdn ?>" ONFOCUSLOAD="" name="pgup" href=""></a>

    <a TVID="<?= $tvid_season_itemdetails ?>" href="<?= itemDetailsLink($season->Id) ?>"></a>
    <a TVID="<?= $tvid_season_series ?>" href="<?= $seriesMenuItem->DetailURL ?>"></a>
<?
    // preload current page of episode images hidden
    // will this speed navigation
    global $episodes, $selectedPage, $episodeCount;
    $episodeOffset = ($selectedPage - 1) * EPISODESPERPAGE;
    for ($i=0; $i < EPISODESPERPAGE && $i < $episodeCount ; $i++) {
        $episodeIndex = $episodeOffset + $i;
        $urlImage = getImage($episodes[$episodeIndex]);
?>
    <img class="abs hidden" src="<?= $urlImage ?>" />
<?
    }
    // preload video/audio flags
    global $asVideoOutputUnique, $asContainerUnique, $asAudioCodecUnique, $asAudioChannelsUnique, $asAspectRatiosUnique;
    printImageArray($asVideoOutputUnique);
    printImageArray($asContainerUnique);
    printImageArray($asAudioCodecUnique);
    printImageArray($asAudioChannelsUnique);
    printImageArray($asAspectRatiosUnique);

    $pageObj->printFooter();
}

function printImageArray($Images)
{
    // don't output array if only 1 item
    // no need to preload since image is already sourced
    if (count($Images) > 1) {
        foreach ($Images as $urlImage) {
?>
    <img class="abs hidden" src="<?= $urlImage ?>" />
<?
        }
    }
}
?>
