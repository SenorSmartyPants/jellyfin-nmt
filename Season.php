<?
/*
no tv rating in JF episode data?

*/
include_once 'data.php';
include_once 'listings.php';

$titleTruncate = 34;

printHeadEtc();

render();

printFooter();

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
                    <span class="tabTvShow" id="s_e_<?= $indexInList ?>"><?= $episode->IndexNumber ?>. <?= substr($episode->Name, 0, $titleTruncate) ?></span>
                </a>
                <a style="display:none;visibility:hidden" width="0" height="0" onfocusload="" 
                href="file:///opt/sybhttpd/localhost.drives/NETWORK_SHARE/storage/media/Videos/No%20Trakt/The%20Daily%20Show/The%20Daily%20Show%2025x22%20-%20%5BWEBDL-720p%5D%5BAAC%202.0%5D%5Bx264%5D%20Noah%20Baumbach-TBS.mkv" 
                vod="" 
                id="a2_e_<?= $indexInList ?>" name="playepisode<?= $indexInList ?>" onfocusset="episode<?= $indexInList ?>" />
            </td>
        </tr>
    </table><a href="#" class="tabTvShow" TVID="<?= $episode->IndexNumber ?>" onclick="setFocusNew(<?= $indexInList ?>); return false;" id="t_e_<?= $indexInList ?>" />


<?
}

function render()
{
    $id = $_GET["id"];

    $itemsAndCount = getUsersItems(null, "Path,Overview,Height,Width,MediaSources,ProviderIds", null, $id);
    $items = $itemsAndCount->Items;
    $item = $items[0];

    renderEpisodeJS($item);

    renderEpisodeHTML($item, 1);

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
    $CC = $item->HasSubtitles;


    $seasonYear = $item->ProductionYear;
    //use gmdate because PremiereDate usually is only a date, time is not significant
    //don't do localtime translation
    if ($item->PremiereDate) {
        $date = gmdate("n/j/Y", strtotime($item->PremiereDate));
    }


    $added = date("n/j/Y g:i A", strtotime($item->DateCreated));

?>

    <h1>
        S<?= $item->ParentIndexNumber ?>
        <?= $seasonYear ?>

        <?= $audioStream->Codec ?>
        <?= $audioStream->ChannelLayout ?>

        <?= $firstSource->Container ?>
        <?= $videoStream->Codec ?>
        <?= $videoStream->Height ?>

        <?= $item->CommunityRating ?>*
    </h1>

    <br />
    Season
    Overview and OfficialRating - missing, pull separately?
    <br />
    episodes support ParentalRating, but not populated in JF
    <br />
    Support watched. Check skin to see <?= $item->UserData->Played ?>



    <table border="1">
        <tr>
            <td valign="top">
                <? if ($item->ImageTags->Primary) { ?><img src="<?= getImageURL($item->Id, 360, null, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? } ?>

            </td>
            <td>

                <h1><?= $item->IndexNumber . '. ' . $item->Name ?></h1>
                <br />

                <?= $item->Overview ?><br /><br />


            </td>
        </tr>
    </table>


    Images:<br />
    support season banner and backdrop. But most in JF don't have<br />

    <? if ($item->ImageTags->Primary) { ?><img src="<?= getImageURL($item->Id, 200, null, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? } ?>
    <br />
    Parent Images:<br />

    <!--no banners set for seasons yet -->
    Banner(doesn't inherit):<br />
    <? if ($item->SeriesId) { ?><img src="<?= getImageURL($item->SeriesId, 200, null, "Banner", null, null, null) ?>" /> <? } ?>
    <br />
    Backdrop(inherit):<br />
    <? if ($item->ParentBackdropImageTags[0]) { ?><img src="<?= getImageURL($item->ParentBackdropItemId, 200, null, "Backdrop", null, null, $item->ParentBackdropImageTags[0]) ?>" /> <? } ?>

    <br />
    tvdb:<?= $item->ProviderIds->Tvdb ?><br />

    <!--
 
    <h2>Some Media Info</h2>
    
    <br/>
    <?= $videoStream->Type ?>:<br/>
    DisplayTitle: <?= $videoStream->DisplayTitle ?><br/>
    Codec: <?= $videoStream->Codec ?><br/>
    <?= $videoStream->AspectRatio ?><br/>
    <?= $videoStream->Width ?><br/>
    <?= $videoStream->RealFrameRate ?><br/>
    
    
    <br/>
    <?= $audioStream->Type ?>:<br/>
    DisplayTitle: <?= $audioStream->DisplayTitle ?><br/>
    Title:<?= $audioStream->Title ?><br/> VB 3x01 for example<br/>
    Codec: <?= $audioStream->Codec ?><br/>
    <?= $audioStream->ChannelLayout ?><br/>
    <?= $audioStream->Language ?><br/>
    
    <br/>
    <? if ($subtitleStream) {
    ?>
    <?= $subtitleStream->Type ?>:
    <?= $subtitleStream->DisplayTitle ?><br/>
    <?= $subtitleStream->Codec ?><br/>
    <?= $subtitleStream->Language ?><br/>
    <?
    }
    ?>

    Container: <?= $firstSource->Container ?><br/>
    Path: <?= $item->Path ?><br/>
    Size: <?= round($firstSource->Size / 1024 / 1024) ?> MB<br/>

-->
<?
}

?>