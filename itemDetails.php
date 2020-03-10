<?
include_once 'data.php';
include_once 'listings.php';

const POSTER_WIDTH = 276;
const THUMB_WIDTH = 396;

$id = $_GET["id"];
$item = getItem($id);

$useSeasonNameForMenuItems = false;
$forceItemDetails = true;

setNames($item);

setupChildData($item);

printHeadEtc("play","itemDetails.css", $Title);

render();

printTitleTable();

printLogo();

printFooter();

function setupChildData($item)
{
    global $indexStyle, $itemsToDisplay;

    //must be set before head so grid.css.php can run right
    $indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup9x3);
    //9x1
    $indexStyle->Limit = 9;
    $indexStyle->offsetY = 500;
    
    //people displayed before children (series actors before seasons)
    if (count($item->People) > 0) {
        //get first X cast and crew
        $itemsToDisplay = $item->People;
        $itemsToDisplay = array_slice($itemsToDisplay, 0, $indexStyle->Limit);
    } else if ($item->ChildCount) {
        //get first X children
        if ($item->Type == "Person") {
            //filter items to ones where PersonID is included
            $children = getItems(null, 0, $indexStyle->Limit, null, true, null, null, null, null, null, $item->Id);
        } else if ($item->Type == "Studio") {
            //filter items to ones where StudioID is included
            $children = getItems(null, 0, $indexStyle->Limit, null, true, null, null, null, null, null, null, $item->Id);
        } else {
            //just get child items
            $children = getItems($item->Id, 0, $indexStyle->Limit);
        }
        $itemsToDisplay = $children->Items;
    }
    
    if ($itemsToDisplay) {
        setNumPagesAndIndexCount(count($itemsToDisplay));
    }
    
}

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

    $streams = getStreams($item);
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
        <td width="<?= POSTER_WIDTH ?>px" height="416px">
        <? 
        if ($item->ImageTags->Primary) { 
            if ($item->PrimaryImageAspectRatio < 1) {
                $width = POSTER_WIDTH;
            } else {
                $width = THUMB_WIDTH;
            }
            ?><img width="$width" src="<?= getImageURL($item->Id, null, $width, "Primary", null, null, $item->ImageTags->Primary) ?>" /> <? 
        } else if ($item->ImageTags->Thumb) { 
            ?><img width="THUMB_WIDTH" src="<?= getImageURL($item->Id, null, THUMB_WIDTH, "Thumb", null, null, $item->ImageTags->Thumb) ?>" /> <? 
        } else {
            ?><img src="images/1x1.png" width="<?= POSTER_WIDTH ?>" /> <?
        } ?>

        </td>
        <td><img src="images/1x1.png" width="30" height="1" /></td>
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

    if ($item->Type != "Person" && ($date || $item->MediaType || $item->OfficialRating || $item->CommunityRating)) {
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
    </tr></table>&nbsp;<br>
    <?   
    }

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

<?
        if ($item->MediaType) { //only display play button for single items
?>          
        <div id="mediainfo">
        <?= $streams->Video ? $streams->Video->Type . ': ' . $streams->Video->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        <?= $streams->Audio ? $streams->Audio->Type . ': ' . $streams->Audio->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        <?= $streams->Subtitle ? $streams->Subtitle->Type . ': ' . $streams->Subtitle->DisplayTitle . '&nbsp;&nbsp;&nbsp;' : null ?>
        </div>&nbsp;<br>
<?
        }

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

    <img src="images/1x1.png" width="<?= 1096-30-$width ?>" height="1" />
        </td>
    </tr>    

    <tr height="182">
        <td colspan="3" align="center">
<?
global $itemsToDisplay;
if ($itemsToDisplay) {
    printPosterTable($itemsToDisplay);
}
?>
        </td>
    </tr>
</table>

   <!--

    <br/>
    Studio: <?= $item->Studios[0]->Name ?><br/>
    
    Imdb:<?= $item->ProviderIds->Imdb ?><br/>
    tvdb:<?= $item->ProviderIds->Tvdb ?><br/>

-->

<?   
}

?>