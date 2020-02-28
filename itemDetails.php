<?
include_once 'data.php';
include_once 'listings.php';

printHeadEtc();

render();

printFooter();

function formatCast($cast)
{
    $links = array();
    foreach($cast as $person) {
        $links[] = '<a href="itemDetails.php?id=' . $person->Id . '">' . $person->Name . '</a>';
    }
    return implode(' / ', $links);
}

function render()
{
    $id = $_GET["id"];

    $item = getItem($id);



    if ($item->SeriesName) {
        $parentName = $item->SeriesName . ' - ' . $item->SeasonName;
        $itemName = $item->IndexNumber . '. ' . $item->Name;
    } else {
        $itemName = $item->Name;
    }
    
    $durationInSeconds = round($item->RunTimeTicks / 1000 / 10000);
    $durationInMinutes = round($durationInSeconds / 60);
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


    if ($item->Type == 'Movie') {
        $date = $item->ProductionYear;
    } else {
        if ($item->PremiereDate) {
            $date = formatDate($item->PremiereDate);
        }
    }

    $added = formatDateTime($item->DateCreated);

    $directors = array();
    $writers = array();
    $actors = array();
    if ($item->People) {
        foreach ($item->People as $person) {
            if ($person->Type == 'Director') {
                $directors[] = $person;
            }
            if ($person->Type == 'Writer') {
                $writers[] = $person;
            }
            if ($person->Type == 'Actor') {
                $actors[] = $person;
            }
        }
    }
?>

<table border="1">
    <tr>
        <td valign="top">
        <? if ($item->ImageTags->Primary) { ?><img src="<?= getImageURL($id, 360, null, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? } ?>

        </td>
        <td>
<?
    if ($parentName) {
?>
        <h1><?= $parentName ?></h1>
        <h3><?= $itemName ?></h3>
<?
    } else {
?>
        <h1><?= $itemName ?></h1>
<?
    }
?>


    <?= $date ?>
    <?= $durationInSeconds > 0 ? $durationInMinutes . ' mins' : null ?> 
    <?= $item->OfficialRating ?>
    <?= $item->CommunityRating ? '*' . $item->CommunityRating : null ?>
    <?= $durationInSeconds > 0 ? 'Ends at ' . date('g:i A', time() + ($durationInSeconds) ) : null ?>
    <br/>

    <?
    if ($item->GenreItems && count($item->GenreItems) > 0) {
        echo 'Genres: ';

        foreach ($item->GenreItems as $genre) {
            echo '<a href="' . $genre->Id . '">' . $genre->Name . '</a>';
            if ($genre != end($item->GenreItems)) {
                echo ', ';
            }
        }
    }
    echo '<br/>';
    ?>

    <?= count($directors) > 0 ? 'Directed by: ' . formatCast($directors) . '<br/>' : null ?>
    <?= count($writers) > 0 ? 'Written by: ' . formatCast($writers) . '<br/>'  : null ?>
    <?= count($actors) > 0 ? 'Starring: ' . formatCast($actors) . '<br/>'  : null ?>
    <br/>
    <br/>
    <?= $videoStream ? $videoStream->Type . ': ' . $videoStream->DisplayTitle : null ?>
    <?= $audioStream ? $audioStream->Type . ': ' . $audioStream->DisplayTitle : null ?>
    <?= $subtitleStream ? $subtitleStream->Type . ': ' . $subtitleStream->DisplayTitle : null ?>
    <br/>
    <br/>

    <?= $item->Taglines[0] ? '<h3>' . $item->Taglines[0] . '</h3>' : null ?>
    <?= $item->Overview ?><br/><br/>
    
    <?= $item->mediaType ? 'Added: ' . $added . '<br/>' : null ?>
    


        </td>
    </tr>
</table>

   <!--
    Images:<br/>
    
    <? if ($item->ImageTags->Primary) { ?><img src="<?= getImageURL($id, 200, null, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? } ?>
    <? if ($item->ImageTags->Thumb) { ?><img src="<?= getImageURL($id, 200, null, "Thumb", null, null, $item->ImageTags->Thumb) ?>" /> <? } ?>
    <br/>
    <? if ($item->ImageTags->Logo) { ?><img src="<?= getImageURL($id, 50, null, "Logo", null, null, $item->ImageTags->Logo) ?>" /> <? } ?>
    <br/>
    <? if ($item->BackdropImageTags[0]) { ?><img src="<?= getImageURL($id, 200, null, "Backdrop", null, null, $item->BackdropImageTags[0]) ?>" /> <? } ?>
    
    
    Parent Images (movies have no parent):<br/>
    
    <? if ($item->ParentId) { ?><img src="<?= getImageURL($item->ParentId, 200, null, "Primary", null, null, null) ?>" /> <? } ?>
    <? if ($item->ParentThumbImageTag) { ?><img src="<?= getImageURL($item->ParentThumbItemId, 200, null, "Thumb", null, null, $item->ParentThumbImageTag) ?>" /> <? } ?>
    <br/>
    <? if ($item->ParentLogoImageTag) { ?><img src="<?= getImageURL($item->ParentLogoItemId, 50, null, "Logo", null, null, $item->ParentLogoImageTag) ?>" /> <? } ?>
    <br/>
    <? if ($item->ParentBackdropImageTags[0]) { ?><img src="<?= getImageURL($item->ParentBackdropItemId, 200, null, "Backdrop", null, null, $item->ParentBackdropImageTags[0]) ?>" /> <? } ?>


    <br/>
    Studio: <?= $item->Studios[0]->Name ?><br/>
    

    
    
    
    Imdb:<?= $item->ProviderIds->Imdb ?><br/>
    tvdb:<?= $item->ProviderIds->Tvdb ?><br/>

    
 
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
    Size: <?= round($firstSource->Size/1024/1024) ?> MB<br/>
-->

<?   
}

?>