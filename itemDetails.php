<?
include_once 'data.php';

$id = $_GET["id"];

$item = getItem($id);
?>

Name: <?= $item->Name ?><br/>
ProductionYear: <?= $item->ProductionYear ?><br/>
Taglines: <?= $item->Taglines[0] ?><br/>
Overview: <?= $item->Overview ?><br/>


OfficialRating: <?= $item->OfficialRating ?><br/>
CommunityRating: <?= $item->CommunityRating ?><br/>
Studio: <?= $item->Studios[0]->Name ?><br/>

<?
$durationInMinutes = round($item->RunTimeTicks / 1000 / 10000 /60);
?> 

<?= $durationInMinutes ?> minutes<br/>



Imdb:<?= $item->ProviderIds->Imdb ?><br/>
tvdb:<?= $item->ProviderIds->Tvdb ?><br/>
Genres:<?= var_dump($item->Genres) ?><br/> <!-- or GenreItems -->

<br/>

<?
$firstSource = $item->MediaSources[0];
foreach ($firstSource->MediaStreams as $mediastream) {
    if ($mediastream->Type == 'Video' && $mediastream->IsDefault) {
        $videoStream = $mediastream;
    }
}
$audioStream = $firstSource->MediaStreams[$firstSource->DefaultAudioStreamIndex];
//can have subs without a default
$subtitleStream = $firstSource->MediaStreams[$firstSource->DefaultSubtitleStreamIndex];

?>

<br/>


<br/>
<?= $videoStream->Type ?>:<br/>
DisplayTitle: <?= $videoStream->DisplayTitle ?><br/>
<?= $firstSource->Container ?><br/>
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


<br/>
CC = <?= $item->HasSubtitles ?><br/>


<?= $item->Path ?><br/>


Images:<br/>

<img src="<?= getImageURL($id, 200, null, "Primary", null, null, $item->ImageTags->Primary) ?>" />
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