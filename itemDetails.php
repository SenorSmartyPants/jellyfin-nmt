<?
include_once 'listings.php';

const POSTER_WIDTH = 276;
const THUMB_WIDTH = 396;
const MORE_TVID = 'RED';
const THREESPACES = '&nbsp;&nbsp;&nbsp;';

$id = htmlspecialchars($_GET["id"]);
$item = getItem($id);

$libraryBrowse = true;
$useSeasonNameForMenuItems = false;
$forceItemDetails = true;

$QSBase = "?id=" . $id . "&subitems=" . $_GET["subitems"] . "&page=";

setNames($item);

setupChildData($item);

$pageObj = new ListingsPage($Title);
$pageObj->indexStyle = $indexStyle;
$pageObj->onloadset = 'play';
$pageObj->additionalCSS = 'itemDetails.css';
$pageObj->InitJSFunction = 'printInitJS';
$pageObj->printHead();

render($item);

$pageObj->printTitleTable($page, $numPages);

printLogo();

$pageObj->printFooter();

function printInitJS()
{
    printListingsInitJS();
    CheckinJS();
}

function setEpisodeIndexStyle($item)
{
    global $indexStyle, $displayepisode;
    global $page, $startIndex;

    $displayepisode = true;

    if ($item->MediaStreams[0]->AspectRatio == "4:3") {
        //4:3
        $indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup4x3AspectRatio);
        $indexStyle->offsetY = 403;
    } else {
        //16x9
        $indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup);
        $indexStyle->offsetY = 410;
    }
    $indexStyle->ImageType = ImageType::PRIMARY;

    $startIndex = ($page - 1) * $indexStyle->Limit;
}

function setupChildData($item)
{
    global $indexStyle, $itemsToDisplay;
    global $subitems, $available_subitems, $selected_subitems_index;
    global $page, $startIndex;

    //must be set before head so grid.css.php can run right
    $indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup9x3);
    //9x1
    $indexStyle->Limit = 9;
    $indexStyle->offsetY = 500;

    $available_subitems = array();

    if ($item->Type == ItemType::EPISODE) {
        //display more episodes, before cast
        $available_subitems[] = "more";
    }
    if ($item->ChildCount) {
        $available_subitems[] = "children";
    }
    if (count($item->People) > 0) {
        $available_subitems[] = "people";
    }
    //only display "more like this" for movies, series, episodes(more from this season), not seasons
    //episodes list more, first, then crew...
    if ($item->Type != ItemType::SEASON && $item->Type != ItemType::EPISODE) {
        $available_subitems[] = "more";
    }

    $selected_subitems_index = array_search($_GET["subitems"], $available_subitems) ?: 0;
    $subitems = $available_subitems[$selected_subitems_index];

    $startIndex = ($page - 1) * $indexStyle->Limit;

    if ($subitems == "more") {
        if ($item->Type == ItemType::EPISODE) {
            setEpisodeIndexStyle($item);
            //get episodes from this season
            $children = getItems($item->SeasonId, $startIndex, $indexStyle->Limit);
        } else {
            $children = getSimilarItems($item->Id, $indexStyle->Limit);
        }
        $itemsToDisplay = $children->Items;
        $totalItems = $children->TotalRecordCount;
    } 
    if ($subitems == "people") {
        //get first X cast and crew
        $itemsToDisplay = $item->People;
        $totalItems = count($item->People);
        //TODO: exclude writers and directors since they are already displayed on the page
        $itemsToDisplay = array_slice($itemsToDisplay, $startIndex, $indexStyle->Limit);
    }
    if ($subitems == "children") {
        //get first X children
        if ($item->Type == ItemType::PERSON) {
            //filter items to ones where PersonID is included
            $children = getItems(null, $startIndex, $indexStyle->Limit, null, true, null, null, null, null, null, $item->Id);
        } else if ($item->Type == ItemType::STUDIO) {
            //filter items to ones where StudioID is included
            $children = getItems(null, $startIndex, $indexStyle->Limit, null, true, null, null, null, null, null, null, $item->Id);
        } else {
            //if season, then display episode style
            if ($item->Type == ItemType::SEASON) {
                setEpisodeIndexStyle($item);
            }
            //just get child items
            $children = getItems($item->Id, $startIndex, $indexStyle->Limit);
        }
        $itemsToDisplay = $children->Items;
        $totalItems = $children->TotalRecordCount;
    }

    if ($itemsToDisplay) {
        setNumPagesAndIndexCount($totalItems);
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
                 src="<?= getImageURL($logoId, null, null, ImageType::LOGO, null, null, $logoTag, null, null, 155, 400) ?>" />
        </div>
        <? 
    }
}

function setNames($item)
{
    global $parentName, $itemName, $Title;

    switch ($item->Type) {
        case ItemType::SEASON:
            $parentName = itemDetailsLink($item->SeriesId, false, $item->SeriesName);
            $itemName = $item->Name;
            $Title = $item->Name . ' - ' . $item->SeriesName;
            break;
        case ItemType::EPISODE:
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
    
function render($item)
{
    global $parentName, $itemName;
    
    $durationInSeconds = round($item->RunTimeTicks / 1000 / 10000);
    $durationInMinutes = round($durationInSeconds / 60);

    $streams = getStreams($item);
    $CC = $item->HasSubtitles;


    switch ($item->Type) {
        case ItemType::MOVIE:
            $date = $item->ProductionYear;
            break;
        case ItemType::SERIES:
            $date = ProductionRangeString($item);
            break;
        case ItemType::SEASON:
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
            ?><img width="$width" src="<?= getImageURL($item->Id, null, $width, ImageType::PRIMARY, null, null, $item->ImageTags->Primary) ?>" /> <? 
        } else if ($item->ImageTags->Thumb) { 
            ?><img width="THUMB_WIDTH" src="<?= getImageURL($item->Id, null, THUMB_WIDTH, ImageType::THUMB, null, null, $item->ImageTags->Thumb) ?>" /> <? 
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

    if ($item->Type != ItemType::PERSON && ($date || $item->MediaType || $item->OfficialRating || $item->CommunityRating)) {
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
        <?= $streams->Video ? $streams->Video->Type . ': ' . $streams->Video->DisplayTitle . THREESPACES : null ?>
        <?= $streams->Audio ? $streams->Audio->Type . ': ' . $streams->Audio->DisplayTitle . THREESPACES : null ?>
        <?= $streams->Subtitle ? $streams->Subtitle->Type . ': ' . $streams->Subtitle->DisplayTitle . THREESPACES : null ?>
        </div>&nbsp;<br>
<?
        }

        if ($item->MediaType) { //only display play button for single items
            
            #region videoPlayLink setup
            $attrs = array("tvid"=>"play");
            $linkName = "play";
            $linkHTML = "Play";

            $callbackJS = "checkin('" . $item->Id . "', " . TicksToSeconds($item->RunTimeTicks) . ");";
            $callbackName = "playcallback";
            $callbackAdditionalAttributes = null;
            #endregion

?>  
    <table class="nobuffer button" ><tr><td><?= videoPlayLink($item, $linkHTML, $linkName, $attrs, $callbackJS, $callbackName, $callbackAdditionalAttributes) ?></td></tr></table>&nbsp;<br>
<?
        }
?>    


    <?= $played ? '<div id="played">Date played ' . $played . '</div>&nbsp;<br>' : null ?>




    <?= $item->Taglines[0] ? '<h3 class="tagline">' . $item->Taglines[0] . '</h3>&nbsp;<br>' : null ?>
    <?= $item->Overview ? '<div id="overview">' . $item->Overview . '</div>&nbsp;<br>' : null ?>
    
    <? if ($item->Type == ItemType::PERSON) {
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
<?
global $available_subitems, $selected_subitems_index;
if (count($available_subitems) > 1)
    {
?>
    <a TVID="<?= MORE_TVID ?>" href="<?= itemDetailsLink($item->Id) . "&subitems=" . $available_subitems[$selected_subitems_index + 1] ?>"></a>
<?
    }
?>
   <!--

    <br/>
    Studio: <?= $item->Studios[0]->Name ?><br/>
    
    Imdb:<?= $item->ProviderIds->Imdb ?><br/>
    tvdb:<?= $item->ProviderIds->Tvdb ?><br/>

-->

<?   
}

?>