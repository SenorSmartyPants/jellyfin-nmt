<?
include_once 'data.php';
include_once 'listings.php';

$id = $_GET["id"];
$item = getItem($id);

setNames($item);
printHeadEtc("play","itemDetails.css", $Title);

render();

printLogo();

printFooter();

function printLogo()
{
    global $item;

    if ($item->ImageTags->Logo) { 
        $logoId = $item->Id;
        $logoTag = $item->ImageTags->Logo;
    } else if ($item->ParentLogoImageTag) {
        $logoId = $item->ParentLogoItemId;
        $logoTag = $item->ParentLogoImageTag;
    }

    if ($logoId) { 
        ?>
        <div id="popupWrapper">
                <img class="abs" id="logo" 
                 src="<?= getImageURL($logoId, null, null, "Logo", null, null, $logoTag, null, null, 155, 400) ?>" />
        </div>
        <? 
    }
}

function setNames($item)
{
    global $parentName, $itemName, $Title;

    switch ($item->Type) {
        case 'Season':
            $parentName = itemDetailsLink($item->SeriesId, false, $item->SeriesName);
            $itemName = $item->Name;
            $Title = $item->Name . ' - ' . $item->SeriesName;
            break;
        case 'Episode':
            $parentName = itemDetailsLink($item->SeriesId, false, $item->SeriesName) . ' - ' . itemDetailsLink($item->SeasonId, false, $item->SeasonName);
            $itemName = $item->IndexNumber . '. ' . $item->Name;
            $Title = $item->Name . ' - ' . $item->SeasonName . ' - ' . $item->SeriesName;
            break;
        default:
            $itemName = $item->Name;
            $Title = $item->Name;
            break;
    }
}
    
function render()
{
    global $item;
    global $parentName, $itemName;
    
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


    switch ($item->Type) {
        case 'Movie':
            $date = $item->ProductionYear;
            break;
        case 'Series':
            $date = ProductionRangeString($item);
            break;
        case 'Season':
            $date = null;
            break;               
        default:
            if ($item->PremiereDate) {
                $date = formatDate($item->PremiereDate);
            }
            break;
    }

    $added = formatDateTime($item->DateCreated);

    if ($item->UserData->Played && $item->MediaType) {
        $played = formatDateTime($item->UserData->LastPlayedDate);
    }

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

<table class="main" border="0" cellpadding="0" cellspacing="0">
    <tr valign="top">
        <td width="276px" height="416px">
        <? 
        if ($item->ImageTags->Primary) { 
            ?><img width="276" src="<?= getImageURL($item->Id, null, 276, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? 
        } else if ($item->ImageTags->Thumb) { 
            ?><img width="276" src="<?= getImageURL($item->Id, null, 276, "Thumb", null, null, $item->ImageTags->Thumb) ?>" /> <? 
        } ?>

        </td>
        <td width="30px">
        </td>
        <td>
<?
    if ($parentName) {
?>
        <h1 class="parentName"><?= $parentName ?></h1>&nbsp;<br>
        <h3 class="itemName"><?= $itemName ?></h3>&nbsp;<br>
<?
    } else {
?>
        <h1 class="itemName"><?= $itemName ?></h1>&nbsp;<br>
<?
    }

    if ($item->OriginalTitle && $item->OriginalTitle != $item->Name) {
?>
        <h4 class="itemName"><?= $item->OriginalTitle ?></h4>&nbsp;<br>
<? 
    }
?>

    <table id="YearDurationEtc" border="0" cellspacing="0" cellpadding="0"><tr>
<? 
    if ($date) {
?>        
        <td class=""><?= $date  ?>&nbsp;&nbsp;&nbsp;</td>
<?
    }

    if ($item->MediaType) {
?>          
        <td class="" ><?= $durationInSeconds > 0 ? $durationInMinutes . ' mins' : null ?>&nbsp;&nbsp;&nbsp;</td>
<? 
    }

    if ($item->OfficialRating) {
?>
        <td><div class="border">
&nbsp;<?= $item->OfficialRating ?>&nbsp;</div></td><td>&nbsp;&nbsp;&nbsp;</td>
<?
    } 

    if ($item->CommunityRating) {
?>        
        <td>*<?= $item->CommunityRating ?>&nbsp;&nbsp;&nbsp;</td>
<?
    } 

    if ($item->MediaType) {
?>  
        <td><?= $durationInSeconds > 0 ? 'Ends at ' . date('g:i A', time() + ($durationInSeconds) ) : null ?></td>
<?
    } 
?>


    </tr></table>
    &nbsp;<br>

    <?
    if ($item->GenreItems && count($item->GenreItems) > 0) {
        echo '<div id="genres">Genres: ';
        foreach ($item->GenreItems as $genre) {
            printf('<a href="browse.php?CollectionType=search&Name=%1$s&Genres=%1$s">%1$s</a>', $genre->Name);
            if ($genre != end($item->GenreItems)) {
                echo ', ';
            }
        }
        echo '</div>&nbsp;<br>';
    }
    ?>

        <?= count($directors) > 0 ? '<div id="directors">Directed by: ' . formatCast($directors, 4, ', ') . '</div>&nbsp;<br>' : null ?>
        <?= count($writers) > 0 ? '<div id="writers">Written by: ' . formatCast($writers, 4, ', ') . '</div>&nbsp;<br>'  : null ?>

        <div id="mediainfo">
        <?= $videoStream ? $videoStream->Type . ': ' . $videoStream->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        <?= $audioStream ? $audioStream->Type . ': ' . $audioStream->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        <?= $subtitleStream ? $subtitleStream->Type . ': ' . $subtitleStream->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        </div>&nbsp;<br>

<?
        if ($item->MediaType) { //only display play button for single items
?>  
    <table class="nobuffer button" ><tr><td><a name="play" tvid="play" <?= videoAttributes($item) ?>>Play</a></td></tr></table>&nbsp;<br>
<?
        }
?>    


    <?= $played ? '<div id="played">Date played ' . $played . '</div>&nbsp;<br>' : null ?>




    <?= $item->Taglines[0] ? '<h3 class="tagline">' . $item->Taglines[0] . '</h3>&nbsp;<br>' : null ?>
    <?= $item->Overview ? '<div id="overview">' . $item->Overview . '</div>&nbsp;<br>' : null ?>
    
    <? if ($item->Type == "Person") {
        if ($date) { 
            ?>
		    <div>Born: <?= $date ?></div>&nbsp;<br>
            <?                
        }
        if ($item->ProductionLocations[0]) {
            ?>
            <div>Birth place: <?= $item->ProductionLocations[0] ?></div>&nbsp;<br>
            <?    
        }
        if ($item->EndDate) {
            ?>
            <div>Died: <?= formatDate($item->EndDate) ?></div>&nbsp;<br>
            <?    
        }        
    } 
    ?>

    <?= $item->MediaType ? '<div id="added">Added ' . $added . '</div>' : null ?>
    
    <? if ($item->AirDays) { ?> 
    <div>Airs <?= $item->AirDays[0] ?> at <?= $item->AirTime ?> on <?= itemDetailsLink($item->Studios[0]->Id, false, $item->Studios[0]->Name) ?></div>
    <? } ?>

        </td>
    </tr>    

    <tr height="182">
        <td colspan="3" align="center">
        </td>
    </tr>
</table>

   <!--
    Images:<br/>
    
    <? if ($item->ImageTags->Primary) { ?><img src="<?= getImageURL($item->Id, 200, null, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? } ?>
    <? if ($item->ImageTags->Thumb) { ?><img src="<?= getImageURL($item->Id, 200, null, "Thumb", null, null, $item->ImageTags->Thumb) ?>" /> <? } ?>
    <br/>
    <? if ($item->ImageTags->Logo) { ?><img src="<?= getImageURL($item->Id, 50, null, "Logo", null, null, $item->ImageTags->Logo) ?>" /> <? } ?>
    <br/>
    <? if ($item->BackdropImageTags[0]) { ?><img src="<?= getImageURL($item->Id, 200, null, "Backdrop", null, null, $item->BackdropImageTags[0]) ?>" /> <? } ?>
    
    
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