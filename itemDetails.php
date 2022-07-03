<?
include_once 'utils.php';
include_once 'listings.php';
include_once 'utils/checkinJS.php';

const POSTER_WIDTH = 276;
const THUMB_WIDTH = 396;
const THREESPACES = '&nbsp;&nbsp;&nbsp;';

$availableOverviewHeight = 416;

abstract class SubitemType
{
    const MORELIKETHIS = 'More Like This';
    const CASTANDCREW = 'Cast & Crew';
    const CHILDREN = 'children';
    const SPECIALFEATURES = 'Special Features';
    const TRAILERS = 'Trailers';
}

class ItemDetailsPage extends ListingsPage
{
    private $additionalparts;
    private $trailers;
    private $specialfeatures;

    public $available_subitems;
    private $selected_subitems_index;

    public $allVideos;

    public function printJavascript() 
    {
        global $item;

        parent::printJavascript();

        //make array of all video items/mediasources
        CheckinJS::render($this->allVideos);
        ?>

        <script type="text/javascript" src="js/uiUpdateUtils.js"></script>
        <script type="text/javascript" src="js/itemDetails.js"></script>
        <?

        //print mediainfo JS arrays
        /*  */
        ?>
        <script type="text/javascript">
        var asItemRuntimeDesc = <?= getJSArray(array_map('runtimeDescription', $this->allVideos), false) ?>;
        var asItemEndsAtDesc = <?= getJSArray(array_map('endsAtDescription', $this->allVideos), false) ?>;
        var asItemVideoDesc = <?= getJSArray(array_map(function($i) { return getStreams($i)->Video->DisplayTitle ?? "None"; }, $this->allVideos), false) ?>;
        var asItemAudioDesc = <?= getJSArray(array_map(function($i) { return getStreams($i)->Audio->DisplayTitle ?? "None"; }, $this->allVideos), false) ?>;
        var asItemSubtitleDesc = <?= getJSArray(array_map(function($i) { return getStreams($i)->Subtitle->DisplayTitle ?? "None"; }, $this->allVideos), false) ?>;
        </script>
        <?
    }

    private function getAllVideos($item)
    {
        $isMultiple = IsMultipleVersion($item);
        if ($isMultiple) {
            SortMediaSourcesByName($item);    
            //get other sources full data, #2 and up
            for ($i=0; $i < $item->MediaSourceCount; $i++) { 
                //media source name is what is displayed in ui
                $versions[] = $item->MediaSources[$i];
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
    }
    
    private function setEpisodeIndexStyle($item)
    {
        global $displayepisode;

        $displayepisode = true;

        if ($item->MediaStreams[0]->AspectRatio == "4:3") {
            //4:3
            $this->indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup4x3AspectRatio);
            $this->indexStyle->offsetY = 403;
        } else {
            //16x9
            $this->indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup);
            $this->indexStyle->offsetY = 410;
        }
        $this->indexStyle->Limit = $this->indexStyle->nbThumbnailsPerLine;
        $this->indexStyle->ImageType = ImageType::PRIMARY;
    }

    private function setupMoreLikeThisItems($item)
    {
        global $page;
        if ($item->Type == ItemType::EPISODE || $item->Type == ItemType::MUSICVIDEO) {
            $this->setEpisodeIndexStyle($item);
        }
        if ($item->Type == ItemType::EPISODE) {
            //get episodes from this season
            $params = new UserItemsParams();
            $params->StartIndex = ($page - 1) * $this->indexStyle->Limit;
            $params->Limit = $this->indexStyle->Limit;
            $params->ParentID = $item->SeasonId;
            $children = getItems($params);
        } else {
            $children = getSimilarItems($item->Id, $this->indexStyle->Limit);
        }
        $this->items = $children->Items;
        return $children->TotalRecordCount;
    }

    private function setupCastAndCrewItems($item, $startIndex)
    {
        //get first X cast and crew
        $this->items = $item->People;
        $this->items = array_filter($this->items, 'filterPeople');
        $totalItems = count($this->items);
        $this->items = array_slice($this->items, $startIndex, $this->indexStyle->Limit);
        return $totalItems;
    }

    private function setupSpecialFeatures($startIndex)
    {
        //get first X SpecialFeatures
        $this->setEpisodeIndexStyle($this->specialfeatures[0]);
        $this->items = $this->specialfeatures;
        $totalItems = count($this->items);
        $this->items = array_slice($this->items, $startIndex, $this->indexStyle->Limit);
        return $totalItems;
    }
    
    private function setupTrailers($startIndex)
    {
        //get first X Trailers
        $this->setEpisodeIndexStyle($this->trailers[0]);
        $this->items = $this->trailers;
        $totalItems = count($this->items);
        $this->items = array_slice($this->items, $startIndex, $this->indexStyle->Limit);
        return $totalItems;
    }

    private function setupChildrenItems($item)
    {
        global $page;
        //get first X children
        $params = new UserItemsParams();
        $params->StartIndex = ($page - 1) * $this->indexStyle->Limit;
        $params->Limit = $this->indexStyle->Limit;
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
                $this->setEpisodeIndexStyle($item);
                $params->StartIndex = ($page - 1) * $this->indexStyle->Limit;
                $params->Limit = $this->indexStyle->Limit;
            }
            //just get child items //other than series, what will have children, music stuff?
            $params->ParentID = $item->Id;
            $children = getItems($params);
        }
        $this->items = $children->Items;
        return $children->TotalRecordCount;
    }

    public function setupChildData($item)
    {
        global $page;

        $this->allVideos = $this->getAllVideos($item);

        //must be set before head so grid.css.php can run right
        $this->indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup9x3);
        //9x1
        $this->indexStyle->Limit = 9;
        $this->indexStyle->offsetY = 500;

        $this->available_subitems = array();

        if ($item->Type == ItemType::EPISODE) {
            //display more episodes, before cast
            $this->available_subitems[] = SubitemType::MORELIKETHIS;
        }
        if ($item->ChildCount) {
            $this->available_subitems[] = SubitemType::CHILDREN;
        }
        if (!empty($item->People)) {
            $this->available_subitems[] = SubitemType::CASTANDCREW;
        }
        if ($this->specialfeatures) {
            $this->available_subitems[] = SubitemType::SPECIALFEATURES;
        }
        if ($this->trailers) {
            $this->available_subitems[] = SubitemType::TRAILERS;
        }
        //only display "more like this" for movies, series, episodes(more from this season), not seasons
        //episodes list more, first, then crew...
        if (($item->Type == ItemType::MOVIE || $item->Type == ItemType::SERIES) && $item->Type != ItemType::EPISODE) {
            $this->available_subitems[] = SubitemType::MORELIKETHIS;
        }

        $this->selected_subitems_index = array_search($_GET["subitems"], $this->available_subitems) ?: 0;
        $subitems = $this->available_subitems[$this->selected_subitems_index];

        $startIndex = ($page - 1) * $this->indexStyle->Limit;

        if ($subitems == SubitemType::MORELIKETHIS) {
            $totalItems = $this->setupMoreLikeThisItems($item);
        }
        if ($subitems == SubitemType::CASTANDCREW) {
            $totalItems = $this->setupCastAndCrewItems($item, $startIndex);
        }
        if ($subitems == SubitemType::CHILDREN) {
            $totalItems = $this->setupChildrenItems($item, $startIndex);
        }
        if ($subitems == SubitemType::SPECIALFEATURES) {
            $totalItems = $this->setupSpecialFeatures($startIndex);
        }
        if ($subitems == SubitemType::TRAILERS) {
            $totalItems = $this->setupTrailers($startIndex);
        }

        if ($this->items) {
            setNumPagesAndIndexCount($totalItems);
            $newindex = $this->selected_subitems_index + 1;
            $newindex = count($this->available_subitems) == $newindex ? 0 : $newindex;

            $label = $this->updateLabel($item, $subitems) ?? $this->available_subitems[$this->selected_subitems_index];
            
            $nextLabel = $this->available_subitems[$newindex];
            $nextLabel = $this->updateLabel($item, $nextLabel) ?? $nextLabel;

            $this->TitleTableNoteRight = $this->getSubitemLink($item, $newindex, $nextLabel);
            $this->TitleTableNoteLeft = $label;
        }
    }

    private function updateLabel($item, $subitems)
    {
        if ($subitems == SubitemType::CHILDREN) {
            //Update $label
            switch ($item->Type) {
                case ItemType::PERSON:
                    $label = '';
                    break;
                case ItemType::STUDIO:
                    $label = 'Productions';
                    break;
                case ItemType::SEASON:
                    $label = ItemType::EPISODE . 's';
                    break;
                case ItemType::SERIES:
                    $label = ItemType::SEASON . 's';
                    break;
                default:
                    break;
            }
        } elseif ($subitems == SubitemType::MORELIKETHIS && $item->Type == ItemType::EPISODE) {
            $label = 'More from ' . $item->SeasonName;
        }
        return $label;
    }

    private function getSubitemLink($item, $index, $Label)
    {
        global $tvid_itemdetails_more;
        if (count($this->available_subitems) > 1) {
            return '<a TVID="' . $tvid_itemdetails_more . '" href="' . itemDetailsLink($item->Id) . "&subitems=" . urlencode($this->available_subitems[$index])
                . '"><span class="' . $tvid_itemdetails_more . '">' . $tvid_itemdetails_more . '</span> for ' . $Label . '</a><br>';
        }
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

$pageObj->setupChildData($item);

//get skip and trim from tags
$skipTrim = new SkipAndTrim($item);


$pageObj->onloadset = 'play';
$pageObj->onload = 'init(); ';
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

function SortMediaSourcesByName($item)
{
    $col = array_column($item->MediaSources, 'Name');
    array_multisort($col, SORT_ASC, $item->MediaSources);
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

function printPoster($item)
{
    $imageProps = new ImageParams();
    $imageProps->width = THUMB_WIDTH;
    if ($item->ImageTags->Primary) { 
        $imageProps->tag = $item->ImageTags->Primary;
        if ($item->PrimaryImageAspectRatio < 1) {
            $imageProps->width = POSTER_WIDTH;
        }
        $url = getImageURL($item->Id, $imageProps, ImageType::PRIMARY);
    } else if ($item->ImageTags->Thumb) {
        $imageProps->tag = $item->ImageTags->Thumb;
        $url = getImageURL($item->Id, $imageProps, ImageType::THUMB);
    } else {
        $imageProps->width = POSTER_WIDTH;
        $url = 'images/1x1.png';
    } 
    ?><img width="<?= $imageProps->width ?>" src="<?= $url ?>" /> <?
    return $imageProps;
}

function printItemNames($item)
{
    global $parentName, $itemName, $availableOverviewHeight;
    if ($parentName) {
    ?>
        <h1 class="parentName"><?= $parentName ?></h1>&nbsp;<br>
        <h3 class="itemName"><?= $itemName ?></h3>
    <?
        $availableOverviewHeight -= (46 + 22);
    } else {
    ?>
        <h1 class="itemName"><?= $itemName ?></h1>
    <?
        $availableOverviewHeight -= 37;
    }

    if ($item->OriginalTitle && $item->OriginalTitle != $item->Name) {
    ?>
        &nbsp;<br><h4 class="itemName"><?= $item->OriginalTitle ?></h4>
    <? 
        $availableOverviewHeight -= 30;
    }
}

function getItemDate($item)
{
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
    return $date;
}

function runtimeDescription($item)
{
    if ($item->RunTimeTicks)
    {
        return round(TicksToSeconds($item->RunTimeTicks) / 60) . ' mins';
    }
}

function endsAtDescription($stream)
{
    return date('g:i A', time() + TicksToSeconds($stream->RunTimeTicks));
}

function printYearDurationEtc($item, $stream, $date)
{
    ?>&nbsp;<br>
        <table id="YearDurationEtc" border="0" cellspacing="0" cellpadding="0"><tr valign="middle">
    <? 
    if ($date) {
    ?>        
        <td><?= $date  ?>&nbsp;&nbsp;&nbsp;</td>
    <?
    }
    
    if ($item->MediaType) {
    ?>          
            <td><span id="Runtime"><?= runtimeDescription($stream) ?></span>&nbsp;&nbsp;&nbsp;</td>
    <? 
    }
    
    if ($item->OfficialRating) {
    ?>
            <td><div class="border">
    &nbsp;<?= $item->OfficialRating ?>&nbsp;</div></td><td>&nbsp;&nbsp;&nbsp;</td>
    <?
    } 
    
    if ($item->CommunityRating) {
        ?><td valign="top"><img src="images/star.png"/></td><td>
            &nbsp;<?= number_format($item->CommunityRating,1) . THREESPACES ?></td>
        <?
    }

    if ($item->CriticRating) {
        if ($item->CriticRating >= 60) {
            $rt_icon = 'images/fresh.png';
        } else {
            $rt_icon = 'images/rotten.png';
        }
        ?><td valign="top"><img src="<?= $rt_icon ?>"/></td><td>
            &nbsp;<?= $item->CriticRating . THREESPACES ?></td>
        <?
    }
    
    if ($item->MediaType && $stream->RunTimeTicks > 0) {
    ?>  
        <td>Ends at <span id="endsAt"><?= endsAtDescription($stream) ?></span></td>
    <?
    } 
    ?>
        </tr></table>
    <?
}

function printPersonVitals($item)
{
    if ($item->Type == ItemType::PERSON) {
        if ($item->PremiereDate) { 
            ?>
            &nbsp;<br><div>Born: <?= formatDate($item->PremiereDate) ?></div>
            <?                
        }
        if ($item->ProductionLocations[0]) {
            ?>
            &nbsp;<br><div>Birth place: <?= $item->ProductionLocations[0] ?></div>
            <?    
        }
        if ($item->EndDate) {
            ?>
            &nbsp;<br><div>Died: <?= formatDate($item->EndDate) ?></div>
            <?    
        } 
    } 
}

function printAirDays($item)
{
    if ($item->AirDays) { 
    ?> 
        <div>Airs <?= $item->AirDays[0] ?> at <?= $item->AirTime ?> on <?= itemDetailsLink($item->Studios[0]->Id, false, $item->Studios[0]->Name) ?></div>
    <? 
    } 
}

function printGenreRow($item)
{
    global $availableOverviewHeight;

    if ($item->GenreItems && count($item->GenreItems) > 0) {
        echo '<tr><td>&nbsp;<br></td></tr><tr><td><div>Genres' . THREESPACES . '</div></td><td colspan="5"><div id="genres">';
        foreach ($item->GenreItems as $genre) {
            $url = categoryBrowseURL('Genres', $genre->Name);
            printf('<a href="%2$s">%1$s</a>', $genre->Name, $url);            
            if ($genre != end($item->GenreItems)) {
                echo ', ';
            }
        }
        echo '</div></td></tr>';
        $availableOverviewHeight -= 27;
    }
}

function printCastRow($cast, $castDivId, $castLabel)
{
    global $availableOverviewHeight;

    $castLabel .= count($cast) > 1 ? 's' : null;

    if (!empty($cast)) {
        echo '<tr><td>&nbsp;<br></td></tr><tr><td><div>' . $castLabel . THREESPACES .
            '</div></td><td colspan="5"><div id="' . $castDivId . '">' .
            formatCast($cast, 4, ', ') . '</div></td></tr>';
        $availableOverviewHeight -= 27;
    }
}

function printStreamInfoRow($item)
{
    global $availableOverviewHeight;

    if ($item->MediaType) {
        $availableOverviewHeight -= 27;
        $streams = getStreams($item);
    ?>          
            <tr><td>&nbsp;<br></td></tr><tr>
            <? printStreamInfo($streams->Video) ?>
            <? printStreamInfo($streams->Audio) ?>
            <? printStreamInfo($streams->Subtitle) ?>
            </tr>
    <?
    }
}

function printStreamInfo($stream)
{
?>
    <?= !empty($stream) ? '<td><div>' . $stream->Type . THREESPACES . '</div></td><td class="nowrap"><div><span id="' . $stream->Type . '">' . $stream->DisplayTitle . '</span>' . THREESPACES . THREESPACES . '</div></td>' : null ?>
<?
}

function printPlayButton($mediaSource, $skipTrim, $isMultiple, $index = null, $includeCallbackLink = true)
{     
    global $tvid_itemdetails_play;
    #region videoPlayLink setup
    $linkName = 'play' . $index;
    $attrs = array('id'=>$linkName, 'tvid'=>$tvid_itemdetails_play);
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
&nbsp;<br><table class="nobuffer button" ><tr><td><?= videoPlayLink($mediaSource, $linkHTML, $linkName, $attrs, $callbackJS, $callbackName, $callbackAdditionalAttributes, $includeCallbackLink) ?></td></tr></table>
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

function printPlayVersionDropdown($item)
{
    global $availableOverviewHeight;
    if ($item->MediaType && IsMultipleVersion($item)) { 
        $availableOverviewHeight -= 28;
        $items = $item->MediaSources;
    ?>
        <tr><td>&nbsp;<br></td></tr>
        <tr><td><div>Version <?= THREESPACES ?></div></td><td colspan="3"><select onkeydownset="play" id="ddlEpisodeId" 
        onchange="iEpisodeId = document.getElementById('ddlEpisodeId').selectedIndex; updateMediaInfoDisplay(iEpisodeId); document.getElementById('play').setAttribute('href','#playcallback' + iEpisodeId); iEpisodeId = iEpisodeId + 1;"
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
    ?>
        </select></td></tr>
    <?
    }
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
    global $pageObj, $availableOverviewHeight;
    $date = getItemDate($item);

    $directors = array_filter($item->People, function($p) { return $p->Type == 'Director'; });
    $writers = array_filter($item->People, function($p) { return $p->Type == 'Writer'; });
    ?>

    <table class="main" border="0" cellpadding="0" cellspacing="0">
        <tr valign="top">
            <td width="<?= POSTER_WIDTH ?>px" height="416px">
            <? 
            $imageProps = printPoster($item);
            ?>

            </td>
            <td><img src="images/1x1.png" width="30" height="1" /></td>
            <td>
    <?
    printItemNames($item);
    if ($item->Type != ItemType::PERSON && ($date || $item->MediaType || $item->OfficialRating || $item->CommunityRating)) {
        printYearDurationEtc($item, $pageObj->allVideos[0], $date);
        $availableOverviewHeight -= 29;
    }
    ?>
    <table id="GenreDirectorWriter" border="0" cellspacing="0" cellpadding="0">
    <?
    printGenreRow($item);
    printCastRow($directors, 'directors', 'Director');
    printCastRow($writers, 'writers', 'Writer');
    printStreamInfoRow($item);
    printPlayVersionDropdown($item);
    ?>
    </table>
    <?
    if ($item->MediaType) { 
        $pageObj->printPlayButtonGroups($item);
        $availableOverviewHeight -= 53;
    }

    if ($item->Taglines[0]) {
        echo '&nbsp;<br><h3 class="tagline">' . $item->Taglines[0] . '</h3>';
        $availableOverviewHeight -= 31;
    }

    if ($item->Type == ItemType::PERSON) {
        if ($item->PremiereDate) { 
            $availableOverviewHeight -= 27;              
        }
        if ($item->ProductionLocations[0]) {
            $availableOverviewHeight -= 27;   
        }
        if ($item->EndDate) {
            $availableOverviewHeight -= 27;   
        } 
    }
    if ($item->AirDays) {
        $availableOverviewHeight -= 27;
    } 

    if ($item->Overview) {
        $availableOverviewHeight -= 9;
    } 

    //spacer pixel at bottom
    $availableOverviewHeight -= 1;
    
    echo $item->Overview ? '&nbsp;<br><div id="overview">' . truncate($item->Overview, $availableOverviewHeight / 21 * 98) . '</div>' : null;
    
    printPersonVitals($item);
    printAirDays($item); 
    ?>

    <img src="images/1x1.png" width="<?= 1096-30-$imageProps->width ?>" height="1" />
        </td>
    </tr>    

    <tr height="182">
        <td colspan="3" align="center">
    <?
    if ($pageObj->items) {
        $pageObj->printPosterTable($pageObj->items);
    }
    ?>
            </td>
        </tr>
    </table>
    <?
}
?>