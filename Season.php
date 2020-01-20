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
    Support watched. Check skin to see <?= $item->UserData->Played ?>


*/
include_once 'data.php';
include_once 'listings.php';
include_once 'templates.php';

$titleTruncate = 34;
$ShowAudioCodec = true;
$ShowContainer = true;
$ShowVideoOutput = true;
$star_rating = true;
$tvNumberRating = false;

$id = $_GET["id"];

$itemsAndCount = getUsersItems(null, "Path,Overview,Height,Width,MediaSources,ProviderIds", null, $id);
$items = $itemsAndCount->Items;
$item = $items[0];

$firstSource = $item->MediaSources[0];
if ($firstSource) {
    foreach ($firstSource->MediaStreams as $mediastream) {
        if ($mediastream->Type == 'Video' && $mediastream->IsDefault) {
            $videoStream = $mediastream;
        }
    }
    $audioStream = $firstSource->MediaStreams[$firstSource->DefaultAudioStreamIndex];
    //can have subs without a default
    $subtitleStream = $firstSource->MediaStreams[$firstSource->DefaultSubtitleStreamIndex];
}

printSeasonHeadEtc();

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
    global $titleTruncate;
?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <a class="TvLink" id="a_e_<?= $indexInList ?>" name="episode<?= $indexInList ?>" 
                    onkeydownset="todown" onkeyrightset="toright" onkeyupset="toup" onkeyleftset="toleft" 
                    onclick="return clicked(this);" onfocus="resetGetter();"
                    onmouseover="showEpisode(<?= $indexInList ?>)" href="#playepisode<?= $indexInList ?>" season="<?= $episode->ParentIndexNumber ?>" episode="<?= $episode->IndexNumber ?>" tvdbid="<?= $episode->ProviderIds->Tvdb ?>">
                    <span class="tabTvShow" id="s_e_<?= $indexInList ?>"><?= sprintf('%02d', $episode->IndexNumber) ?>. <?= substr($episode->Name, 0, $titleTruncate) ?></span>
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
    global $item;

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
    <? if ($item->ParentBackdropImageTags[0]) { ?> background="<?= getImageURL($item->ParentBackdropItemId, 720, 1280, "Backdrop", null, null, $item->ParentBackdropImageTags[0]) ?>"> <? } ?>

    <table height="656" width="1102" border="0" cellspacing="0" cellpadding="0" background="/New/Jukebox/pictures/sabishmod/tvbg-v2.png">
        <tr>
            <td valign="top">
<?
    printTopBar();
}

function printTopBar()
{
    global $item, $videoStream, $audioStream, $firstSource;
    global $ShowAudioCodec, $ShowContainer, $ShowVideoOutput, $star_rating, $tvNumberRating;
?>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr height="50" valign="bottom">
            <td width="18"></td>
            <td width="250">
                <img width="244" height="45" src="<?= getImageURL($item->SeriesId, 45, 244, "Banner") ?>" />
            </td>
            <td width="30"></td>
            <td align="center" valign="center" class="tvseason"><?= ($item->ParentIndexNumber > 0) ? "S" . $item->ParentIndexNumber : "Sp" ?></td>
            <td width="20"></td>

            <td align="center" valign="center" class="tvyear"><?= $item->ProductionYear ?></td>

            <td width="50"></td>
            <td valign="center" style="font-size: medium"><?= $ShowAudioCodec ? audioCodec($audioStream) : null ?><?= $ShowContainer ? container($firstSource->Container) : null ?><?= $ShowVideoOutput ? videoOutput($videoStream) : null ?></td>

            <?= $item->ParentIndexNumber < 10 ? '<td width="90"></td>' : null ?> 
            <?= ($item->ParentIndexNumber > 9 and $item->ParentIndexNumber < 100) ? '<td width="70"></td>' : null ?>
            <?= ($item->ParentIndexNumber > 99 and $item->ParentIndexNumber < 1000) ? '<td width="50"></td>' : null ?>
            <?= ($item->ParentIndexNumber > 999) ? '<td width="30"></td>' : null ?>
            
            <td align="right" valign="center" class="rating">						
                <? 
                if ($item->CommunityRating) 
                {
                    if ($star_rating) 
                    { ?>
                        <img hspace="10" vspace="10" src="/New/Jukebox/pictures/detail/rating_<?= round($item->CommunityRating)*10?>.png" >
                        </img>
                    <? }
                    if ($tvNumberRating) 
                    {
                        echo "&nbsp;(" . $item->CommunityRating . "/10)"; 
                    }
                } ?>
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