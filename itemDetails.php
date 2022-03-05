<?
include_once 'utils.php';
include_once 'listings.php';
include_once 'utils/checkinJS.php';

const POSTER_WIDTH = 276;
const THUMB_WIDTH = 396;
const THREESPACES = '&nbsp;&nbsp;&nbsp;';


abstract class SubitemType
{
    const MORELIKETHIS = 'More Like This';
    const CASTANDCREW = 'Cast & Crew';
    const CHILDREN = 'children';
}

class ItemDetailsPage extends ListingsPage
{
    private $additionalparts;
    private $trailers;
    private $specialfeatures;

    public function printJavascript() 
    {
        global $skipTrim, $item;

        parent::printJavascript();

        //make array of all video items/mediasources
        $items = $this->getAllVideos($item);
        CheckinJS::render($items);
    }

    private function getAllVideos($item)
    {
        $isMultiple = IsMultipleVersion($item);
        if ($isMultiple) {
            SortVersionsByName($item);    
            //get other sources full data, #2 and up
            for ($i=0; $i < $item->MediaSourceCount; $i++) { 
                //version name is different from MediaSource name
                $versions[] = getItem($item->MediaSources[$i]->Id);
            } 
        } else {
            $versions[] = $item;
        }

        //what is intro count attribute?
        if ($item->PartCount && $item->PartCount > 0) {
            //Additional Parts
            $this->additionalparts = getItemExtras($item->Id, ExtrasType::ADDITIONALPARTS);
        }
        if ($item->LocalTrailerCount && $item->LocalTrailerCount > 0) {
            //Trailers
            $this->trailers = getItemExtras($item->Id, ExtrasType::LOCALTRAILERS);          
        }
        if ($item->SpecialFeatureCount && $item->SpecialFeatureCount > 0) {
            //Special Features
            $this->specialfeatures = getItemExtras($item->Id, ExtrasType::SPECIALFEATURES);          
        }

        //media sources doesn't contain userdata
        return array_merge($versions, (array) $this->additionalparts, (array) $this->trailers, (array) $this->specialfeatures);
    }

    public function printPlayButtonGroups($item)
    {
        global $skipTrim;
        $isMultiple = IsMultipleVersion($item);
        printVideoCallbackLinks($item->MediaSources);
        if ($isMultiple) {
            $mediaSource = null;
        } else {
            //use mediaSources for better names than pulling each item
            $mediaSource = $item->MediaSources[0];
        }
        printPlayButton($mediaSource, $skipTrim, false, null, false);
        $previousPlayButtons = count($item->MediaSources);

        //check for ExtrasTypes
        if (!empty($this->additionalparts)) {
            foreach ($this->additionalparts as $index => $part) {
                //display a small name 'Part X'
                $part->MediaSources[0]->Name = 'Part ' . (2 + $index);
            }
            $previousPlayButtons = PrintExtras($this->additionalparts, 'Additional Parts', $previousPlayButtons); 
        }
        $previousPlayButtons = PrintExtras($this->trailers, 'Trailers', $previousPlayButtons);
        $previousPlayButtons = PrintExtras($this->specialfeatures, 'Special Features', $previousPlayButtons);
    }
}

$pageObj = new ItemDetailsPage($Title, false);

$id = htmlspecialchars($_GET["id"]);
$item = getItem($id);

$libraryBrowse = true;
$useSeasonNameForMenuItems = false;
$forceItemDetails = true;

$pageObj->QSBase = "id=" . $id . "&subitems=" . urlencode($_GET["subitems"]);

setNames($item);
$pageObj->title = $Title;

setupChildData($item);

    //get skip and trim from tags
    if ($item->Type == ItemType::EPISODE) {
        //use series for skip and trim
        $series = getItem($item->SeriesId);
        $skipTrim = new SkipAndTrim($series);
    } else {
        $skipTrim = new SkipAndTrim($item);
    }

$pageObj->indexStyle = $indexStyle;
$pageObj->onloadset = 'play';
$pageObj->additionalCSS = 'itemDetails.css';
$pageObj->printHead();

render($item);

$pageObj->printTitleTable($page, $numPages);

printLogo();

$pageObj->printFooter();

function IsMultipleVersion($item)
{
    return $item->MediaSourceCount && $item->MediaSourceCount > 1;
}

function SortVersionsByName($item)
{
    $col = array_column($item->MediaSources, 'Name');
    array_multisort($col, SORT_ASC, $item->MediaSources);
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
    global $pageObj; //move into page class
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
        $available_subitems[] = SubitemType::MORELIKETHIS;
    }
    if ($item->ChildCount) {
        $available_subitems[] = SubitemType::CHILDREN; 
    }
    if (!empty($item->People)) {
        $available_subitems[] = SubitemType::CASTANDCREW;
    }
    //only display "more like this" for movies, series, episodes(more from this season), not seasons
    //episodes list more, first, then crew...
    if (($item->MediaType == "Video" || $item->Type == ItemType::SERIES) && $item->Type != ItemType::EPISODE) {
        $available_subitems[] = SubitemType::MORELIKETHIS;
    }

    $selected_subitems_index = array_search($_GET["subitems"], $available_subitems) ?: 0;
    $subitems = $available_subitems[$selected_subitems_index];

    $startIndex = ($page - 1) * $indexStyle->Limit;

    if ($subitems == SubitemType::MORELIKETHIS) {
        if ($item->Type == ItemType::EPISODE || $item->Type == ItemType::MUSICVIDEO) {
            setEpisodeIndexStyle($item);
        }
        if ($item->Type == ItemType::EPISODE) {
            //get episodes from this season
            $params = new UserItemsParams();
            $params->StartIndex = $startIndex;
            $params->Limit = $indexStyle->Limit;
            $params->ParentID = $item->SeasonId;
            $children = getItems($params);
        } else {
            $children = getSimilarItems($item->Id, $indexStyle->Limit);
        }
        $itemsToDisplay = $children->Items;
        $totalItems = $children->TotalRecordCount;
    } 
    if ($subitems == SubitemType::CASTANDCREW) {
        //get first X cast and crew
        $itemsToDisplay = $item->People;
        $itemsToDisplay = array_filter($itemsToDisplay, 'filterPeople');
        $totalItems = count($itemsToDisplay);
        $itemsToDisplay = array_slice($itemsToDisplay, $startIndex, $indexStyle->Limit);
    }
    if ($subitems == SubitemType::CHILDREN) {
        //get first X children
        $params = new UserItemsParams();
        $params->StartIndex = $startIndex;
        $params->Limit = $indexStyle->Limit;
        if ($item->Type == ItemType::PERSON) {
            //filter items to ones where PersonID is included
            $params->Recursive = true;
            $params->PersonIDs = $item->Id;
            //JF-web does not include seasons on person page
            $params->ExcludeItemTypes = ItemType::SEASON;
            $params->SortBy = UserItemsParams::SORTNAME;
            $children = getItems($params);
        } else if ($item->Type == ItemType::STUDIO) {
            //filter items to ones where StudioID is included
            $params->Recursive = true;
            $params->StudioIDs = $item->Id;
            $children = getItems($params);
        } else {
            //if season, then display episode style
            if ($item->Type == ItemType::SEASON) {
                setEpisodeIndexStyle($item);
                $params->StartIndex = $startIndex;
                $params->Limit = $indexStyle->Limit;
            }
            //just get child items
            $params->ParentID = $item->Id;
            $children = getItems($params);
        }
        $itemsToDisplay = $children->Items;
        $totalItems = $children->TotalRecordCount;
    }

    if ($itemsToDisplay) {
        setNumPagesAndIndexCount($totalItems);
        $pageObj->TitleTableNoteRight = getSubitemLink($item);
        $pageObj->TitleTableNoteLeft = $subitems;
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
        $imageProps = new ImageParams();
        $imageProps->tag = $logoTag; 
        $imageProps->maxHeight = 155;
        $imageProps->maxWidth = 400;
        ?>
        <div id="popupWrapper">
                <img class="abs" id="logo" 
                 src="<?= getImageURL($logoId, $imageProps, ImageType::LOGO) ?>" />
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
        case ItemType::MUSICVIDEO:
            if (!empty($item->ArtistItems)) {
                $parentName = $item->Name;
                $itemName = itemDetailsLink($item->ArtistItems[0]->Id, false, $item->ArtistItems[0]->Name);
                $Title = $item->Name . ' - ' . $item->ArtistItems[0]->Name;
                break;
            } // else fall thru to default
        default:
            $itemName = $item->Name;
            $Title = $item->Name;
            break;
    }
}

function printCastRow($cast, $castDivId, $castLabel)
{
    $castLabel .= count($cast) > 1 ? 's' : null;
?>
    <?= !empty($cast) ? '<tr><td><div>' . $castLabel . THREESPACES . '</div></td><td colspan="5"><div id="' . $castDivId . '">'. formatCast($cast, 4, ', ') . '</div></td></tr><tr><td>&nbsp;<br></td></tr>'  : null ?>
<?
}

function printStreamInfo($stream)
{
?>
    <?= !empty($stream) ? '<td><div>' . $stream->Type . THREESPACES . '</div></td><td><div id="mediainfo">' . $stream->DisplayTitle . THREESPACES . THREESPACES . '</div></td>' : null ?>
<?
}

function printPlayButton($mediaSource, $skipTrim, $isMultiple, $index = null, $includeCallbackLink = true)
{     
    global $tvid_itemdetails_play;
    #region videoPlayLink setup
    $attrs = array('tvid'=>$tvid_itemdetails_play);
    $linkName = 'play' . $index;
    if ($isMultiple) {
        $linkHTML = 'Play - ' . $mediaSource->Name;
    } else {
        $linkHTML = 'Play';
    }

    if (is_null($index)) {
        $callbackJS = CheckinJS::getCallback($skipTrim);
    } else {
        $callbackJS = CheckinJS::getCallback($skipTrim, $index + 1);
    }

    $callbackName = 'playcallback' . (is_null($index) ? 0 : $index);
    $callbackAdditionalAttributes = null;
    #endregion

?>  
<table class="nobuffer button" ><tr><td><?= videoPlayLink($mediaSource, $linkHTML, $linkName, $attrs, $callbackJS, $callbackName, $callbackAdditionalAttributes, $includeCallbackLink) ?></td></tr></table>&nbsp;<br>
<?
}

function printPlayButtons($items, $skipTrim, $isMultiple, $previousPlayButtons = 0)
{
    foreach ($items as $item) {
        if ($item->MediaSources) {
            $mediaSource = $item->MediaSources[0];
        } else {
            $mediaSource = $item;
        }
        printPlayButton($mediaSource, $skipTrim, $isMultiple, $previousPlayButtons++);
    }
    return $previousPlayButtons;
}

function printPlayVersionDropdown($items)
{  
?>
    <tr><td><div>Version <?= THREESPACES ?></div></td><td><select onkeydownset="play" id="ddlEpisodeId" 
    onchange="iEpisodeId = document.getElementById('ddlEpisodeId').selectedIndex; document.getElementById('play').setAttribute('href','#playcallback' + iEpisodeId); iEpisodeId = iEpisodeId + 1;"
    >
<?
    foreach ($items as $item) {
        if ($item->MediaSources) {
            $mediaSource = $item->MediaSources[0];
        } else {
            $mediaSource = $item;
        }
?>  
        <option><?= $mediaSource->Name ?></option>
<?
    }
    echo "</select></td></tr><tr><td>&nbsp;<br></td></tr>";
}

function PrintExtras($extras, $Label, $previousPlayButtons)
{
    global $skipTrim;
    if (!empty($extras)) {
        echo "<h4>$Label</h4>";
        $previousPlayButtons = printPlayButtons($extras, $skipTrim, true, $previousPlayButtons);   
    }
    return $previousPlayButtons;
}

function render($item)
{
    global $parentName, $itemName;
    global $pageObj;
    
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
            } else if ($item->ProductionYear) {
                $date = $item->ProductionYear;
            }
            break;
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
        $imageProps = new ImageParams();
        if ($item->ImageTags->Primary) { 
            $imageProps->tag = $item->ImageTags->Primary;
            if ($item->PrimaryImageAspectRatio < 1) {
                $imageProps->width = POSTER_WIDTH;
            } else {
                $imageProps->width = THUMB_WIDTH;
            }
            ?><img width="<?= $imageProps->width ?>" src="<?= getImageURL($item->Id, $imageProps, ImageType::PRIMARY) ?>" /> <? 
        } else if ($item->ImageTags->Thumb) { 
            ?><img width="<?= THUMB_WIDTH ?>" src="<?= getImageURL($item->Id, new ImageParams(null, THUMB_WIDTH, $item->ImageTags->Thumb), ImageType::THUMB) ?>" /> <? 
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

    if ($item->CommunityRating || $item->CriticRating) {
        echo '<td>';
        if ($item->CommunityRating) {
            echo '*' . $item->CommunityRating . THREESPACES;
        }
        if ($item->CriticRating) {
            echo $item->CriticRating . '/100' . THREESPACES;
        }
        echo '</td>';
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
    ?>
    <table id="GenreDirectorWriter" border="0" cellspacing="0" cellpadding="0">
    <?
    if ($item->GenreItems && count($item->GenreItems) > 0) {
        echo '<tr><td><div>Genres' . THREESPACES . '</div></td><td><div id="genres">';
        foreach ($item->GenreItems as $genre) {
            $url = categoryBrowseURL('Genres', $genre->Name);
            printf('<a href="%2$s">%1$s</a>', $genre->Name, $url);            
            if ($genre != end($item->GenreItems)) {
                echo ', ';
            }
        }
        echo '</div></td></tr><tr><td>&nbsp;<br></td></tr>';
    }
    printCastRow($directors, 'directors', 'Director');
    printCastRow($writers, 'writers', 'Writer');

    if ($item->MediaType) {
?>          
        <tr>
        <? printStreamInfo($streams->Video) ?>
        <? printStreamInfo($streams->Audio) ?>
        <? printStreamInfo($streams->Subtitle) ?>
        </tr><tr><td>&nbsp;<br></td></tr>
<?
    }

    // print dropdown for multiple versions here
    if ($item->MediaType && IsMultipleVersion($item)) { 
        global $skipTrim;
        printPlayVersionDropdown($item->MediaSources);
    }
    ?>
    </table>
    <?
        if ($item->MediaType) { 
            $pageObj->printPlayButtonGroups($item);
        }
?>    

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
    
    <? if ($item->AirDays) { ?> 
    <div>Airs <?= $item->AirDays[0] ?> at <?= $item->AirTime ?> on <?= itemDetailsLink($item->Studios[0]->Id, false, $item->Studios[0]->Name) ?></div>
    <? } ?>

    <img src="images/1x1.png" width="<?= 1096-30-$imageProps->width ?>" height="1" />
        </td>
    </tr>    

    <tr height="182">
        <td colspan="3" align="center">
<?
global $itemsToDisplay, $pageObj;
if ($itemsToDisplay) {
    $pageObj->printPosterTable($itemsToDisplay);
}
?>
        </td>
    </tr>
</table>
<?
}

function getSubitemLink($item)
{
    global $available_subitems, $selected_subitems_index, $tvid_itemdetails_more;
    if (count($available_subitems) > 1)
    {
        $newindex = $selected_subitems_index + 1;
        $newindex = count($available_subitems) == $newindex ? 0 : $newindex;
        return '<a TVID="' . $tvid_itemdetails_more . '" href="' . itemDetailsLink($item->Id) . "&subitems=" . urlencode($available_subitems[$newindex]) 
            . '"><span class="' . $tvid_itemdetails_more . '">' . $tvid_itemdetails_more . '</span> for ' . $available_subitems[$newindex] . '</a><br>';
    }
}

?>