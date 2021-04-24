<?
include_once 'config.php';

function getBackdropIDandTag($item, $backdropID = null)
{
    $retval = new stdClass();

    $retval->Id = $backdropID;
    $retval->Tag = null;
    
    if ($item) {
        if ($item->BackdropImageTags && count($item->BackdropImageTags) > 0) {
            $retval->Id = $item->Id;
            $retval->Tag = $item->BackdropImageTags[0];
        } elseif ($item->ParentBackdropImageTags && count($item->ParentBackdropImageTags) > 0) {
            $retval->Id = $item->ParentBackdropItemId;
            $retval->Tag = $item->ParentBackdropImageTags[0];
        }
    }

    return $retval;
}

function getStreams($item)
{
    $retval = new stdClass();

    $firstSource = $item->MediaSources[0];
    if ($firstSource) {
        $retval->Container = $firstSource->Container;
        foreach ($firstSource->MediaStreams as $mediastream) {
            if ($mediastream->Type == 'Video') {
                $retval->Video = $mediastream;
            }
        }
        $retval->Audio = $firstSource->MediaStreams[$firstSource->DefaultAudioStreamIndex];
        //can have subs without a default
        $retval->Subtitle = $firstSource->MediaStreams[$firstSource->DefaultSubtitleStreamIndex];
    }

    return $retval;
}

function HTMLattributes($assocArray)
{
    $html = null;
    if ($assocArray) {
        foreach ($assocArray as $key => $value) {
            $html .= $key . '="' . $value . '" ';
        }
    }
    return $html;
}

function videoAttributesByUrl($url, $vod = null)
{
    //translate if not http
    if (substr($url, 0, 4) != 'http') {
        $url  = translatePathToNMT($url);
    }
    $attrs['href'] = $url;

    if ($vod == 'zcd') {
        $vod = null;
        $attrs['zcd'] = '2';
    }
    $attrs['vod'] = $vod;

    return HTMLattributes($attrs);
}

function videoAttributes($mediaSource)
{
    if ($mediaSource->VideoType != "VideoFile") {
        return videoAttributesByUrl($mediaSource->Path, 'zcd');
    } else {
        return videoAttributesByUrl($mediaSource->Path);
    }
}

//pass mediasource instead of item when multiple versions
//item currently has Path and VideoType for first version, so item can still be passed, like for episodes
function videoPlayLink($mediaSource, 
    $linkHTML = null, $linkName = null, $additionalAttributes = null, 
    $callbackJS = null, $callbackName = null, $callbackAdditionalAttributes = null)
{
    //generate 1 link to play with no callback
    //2 links to call server before playing video, to checkin/scrobble

    $html = '<a ';
    if ($linkName) {
        $html .= 'name="' . $linkName . '" ';
    }

    $html .= HTMLattributes($additionalAttributes);

    if ($callbackJS) {
        $html .= 'onclick="' . $callbackJS . '" ';
        $html .= 'href="#' . $callbackName . '" ';
    } else {
        $html .= videoAttributes($mediaSource);
    }
    
    $html .= '>' . $linkHTML . '</a>';

    if ($callbackJS) {
        $html .= '<a onfocusload="" ';
        $html .= 'name="' . $callbackName . '" ';
        $html .= 'onfocusset="' . $linkName . '" ';
        $html .= HTMLattributes($callbackAdditionalAttributes);      
        $html .= videoAttributes($mediaSource);
        $html .= 'onfocus="stop();" '; //call stop method
        $html .= '></a>';
    }

    return $html;
}

class SkipAndTrim
{
    public $skipSeconds = 0;
    public $trimSeconds = 0;

    public function __construct($item) {
        $this->getSkipAndTrim($item);   
    }     

    static private function getSecondsFromSkipTrimTag(String $tag)
    {
            $amountStr = substr($tag, 9);
            preg_match('/((?<minutes>\d{1,2})m)?((?<seconds>\d{1,2})s)?/', $amountStr, $skipArray);
            return $skipArray['minutes'] * 60 + $skipArray['seconds'];
    }
    
    private function getSkipAndTrim($item)
    {
        //find nmt-skip|trim tags
        foreach ($item->Tags as $tag) {
            if (substr($tag, 0, 8) === 'nmt-skip') {
                //skip tag found, should only be one
                $this->skipSeconds = SkipAndTrim::getSecondsFromSkipTrimTag($tag);
            }
            if (substr($tag, 0, 8) === 'nmt-trim') {
                //trim tag found, should only be one
                $this->trimSeconds = SkipAndTrim::getSecondsFromSkipTrimTag($tag);
            }
        }
    }    
    public function getStartPosition($UserData)
    {
        //start at skipSeconds if user position is 0
        //otherwise use resume position
        $startPosition = intval($UserData->PlaybackPositionTicks / (10000 * 1000));
        return ($startPosition == 0) ? $this->skipSeconds : $startPosition;
    }
}

function CheckinJS()
{
?>
        <script type="text/javascript" src="js/empty.js" id="checkinjs"></script>
        <script type="text/javascript">
            function checkin(itemId, duration, position, trim) { 
                var url = "checkin.php?id=" + itemId + "&duration=" + duration + "&position=" + position + "&trim=" + trim;
                document.getElementById("checkinjs").setAttribute('src', url + "&JS=true");
            }
    
            function stop() {
                var url = 'checkin.php?action=stop';
                document.getElementById("checkinjs").setAttribute('src', url + "&JS=true");
            }
            
            function callback(id, inlineMsg) {
                document.getElementById("checkinjs").setAttribute('src', "js/empty.js");
            }
        </script>
<?
}

function TicksToSeconds($ticks)
{
    return intval($ticks / 10000000);
}

function escapeURL($url)
{
    return implode("/", array_map("rawurlencode", explode("/", $url)));
}

function translatePathToNMT($path)
{
    global $NMT_path,$NMT_playerpath;
    return str_replace($NMT_path,$NMT_playerpath,escapeURL($path));
}

function formatCast($cast, $limit = 27, $separator = ' / ')
{
    $links = array();
    $cast = array_slice($cast, 0, $limit);
    foreach($cast as $person) {
        $links[] = itemDetailsLink($person->Id, false, $person->Name);
    }
    return implode($separator, $links);
}

function formatDate($datetimeString)
{
    //use gmdate because PremiereDate usually is only a date, time is not significant
    //don't do localtime translation
    return gmdate("n/j/Y",strtotime($datetimeString));
}

function formatDateTime($datetimeString)
{
    return date("n/j/Y g:i A",strtotime($datetimeString));
}

function ProductionRangeString($item)
{
    $retval = $item->ProductionYear . ' - ';
    if ($item->EndDate) {
        $retval .= gmdate("Y",strtotime($item->EndDate));
    } else {
        $retval .= 'Present';
    }
    return $retval;
}

function itemDetailsLink($id, $urlOnly = true, $linkText = null) {
    $url = 'itemDetails.php?id=' . $id;
    if ($urlOnly) {
        return $url;
    } else {
        return '<a href="' . $url . '">' . $linkText . '</a>';
    }
}

class CategoryBrowseParams
{
    public $topParentName = null;
    public $topParentId = null;
    public $parentId = null;
    public $folderType = null;
    public $collectionType = 'search';

    public $name;
    public $backdropId = null;

    public $categoryName = null;
    public $searchTerm = null;
}

function categoryBrowseURL($categoryName, $searchTerm, $collectionType = 'search', $topParentId = null, $topParentName = null)
{
    if (empty($collectionType)) {
        $collectionType = 'search';
    }

    $cbp = new CategoryBrowseParams();
    $cbp->name = $searchTerm;
    $cbp->searchTerm = $searchTerm;
    $cbp->categoryName = $categoryName;
    $cbp->collectionType = $collectionType;
    $cbp->topParentName = $topParentName;
    $cbp->topParentId = $topParentId;

    if ($cbp->collectionType === 'search') {
        //top level, just link on the category page 
    } else {
        //filter by the displayed collectiontype, tv, movie, boxset...
        $cbp->folderType = 'CollectionFolder'; 
    }
    return categoryBrowseURLEx($cbp);
}

function categoryBrowseURLEx(CategoryBrowseParams $cbp)
{
    return 'browse.php?' . http_build_query($cbp);
}

?>