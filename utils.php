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

function getStreamsFromMediaSource($mediaSource)
{
    if ($mediaSource->MediaStreams)
    {
        $retval = new stdClass();
        $retval->Container = $mediaSource->Container;
        foreach ($mediaSource->MediaStreams as $mediastream) {
            if ($mediastream->Type == 'Video') {
                $retval->Video = $mediastream;
            }
        }
        $retval->Audio = $mediaSource->MediaStreams[$mediaSource->DefaultAudioStreamIndex];
    
        // add sanity check, JF 10.8.0 results 0 which points to video stream on some conditions
        if ($retval->Audio->Type != 'Audio') {
            //just return the first audio stream in this edge case
            $audiostreams = array_filter($mediaSource->MediaStreams, function($stream) { return $stream->Type == 'Audio'; });
            $retval->Audio =  current($audiostreams);
        }
        
        $substreams = array_filter($mediaSource->MediaStreams, function($stream) { return $stream->Type == 'Subtitle'; });
        //can have subs without a default
        $retval->Subtitle = current($substreams);
        return $retval;
    }
}

function getStreams($item)
{
    $mediaSource = $item->MediaSources[0];
    if (!$mediaSource) {
        //item does not have mediasource, perhaps it is a mediasource
        $mediaSource = $item;
    }
    return getStreamsFromMediaSource($mediaSource);
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

function videoCallbackLink($mediaSource, $callbackName, $linkName,  
    $callbackAdditionalAttributes = null)
{
    $html = '<a onfocusload="" ';
    $html .= 'name="' . $callbackName . '" ';
    $html .= 'onfocusset="' . $linkName . '" ';
    $html .= HTMLattributes($callbackAdditionalAttributes);
    $html .= videoAttributes($mediaSource);
    $html .= 'onfocus="stop();" '; //call stop method
    $html .= '></a>';

    return $html;
}

function printVideoCallbackLinks($items)
{
    $index = 0;
    foreach ($items as $item) {
        if ($item->MediaSources) {
            $mediaSource = $item->MediaSources[0];
        } else {
            $mediaSource = $item;
        }
        
        #region videoPlayLink setup
        $linkName = 'play';
        $callbackName = 'playcallback' . $index;
        #endregion
        $index++;

        echo videoCallbackLink($mediaSource, $callbackName, $linkName) . "\n";
    }
}

//pass mediasource instead of item when multiple versions
//item currently has Path and VideoType for first version, so item can still be passed, like for episodes
function videoPlayLink($mediaSource, 
    $linkHTML = null, $linkName = null, $additionalAttributes = null, 
    $callbackJS = null, $callbackName = null, $callbackAdditionalAttributes = null, $includeCallbackLink = true)
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

    if ($callbackJS && $includeCallbackLink) {
        $html .= videoCallbackLink($mediaSource, $callbackName, $linkName, $callbackAdditionalAttributes);
    }

    return $html;
}

class SkipAndTrim
{
    public $skipSeconds = 0;
    public $trimSeconds = 0;

    public function __construct($item) {
        if ($item->Type == ItemType::EPISODE) {
            //use series for skip and trim
            $item = getItem($item->SeriesId);
        }

        $this->getSkipAndTrim($item);
        if ($item->Type == ItemType::SERIES && $this->skipSeconds == 0 && $this->trimSeconds == 0) {
            //if series, also check studio if nothing set for series
            $firstStudio = getItem($item->Studios[0]->Id);
            $this->getSkipAndTrim($firstStudio);
        }
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

function JSEscape($str)
{
    return str_replace(array("\n", "\r"), '', $str);
}

function truncate($str, $maxlength, $JSescape = false)
{
    if (strlen($str) > $maxlength) {
        $str = substr($str, 0, $maxlength) . '...';
    }
    if ($JSescape) {
        $str = JSEscape($str);
    }    
    return $str;
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
    //SMB catia settings
    //vfs objects = catia
    //catia:mappings = 0x22:0xa8,0x2a:0xa4,0x2f:0xf8,0x3a:0xf7,0x3c:0xab,0x3e:0xbb,0x3f:0xbf,0x5c:0xff,0x7c:0xa6

    //handle catia character mappings
    $mapping = array('\\' => 'ÿ',
    ':' => '÷', '*' => '¤', '?' => '¿',
    '"' => '¨', '<' => '«', '>' => '»',
    '|' => '¦');
    $path = strtr($path, $mapping);
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
    public $folderType = null;
    public $collectionType = null;

    public $name;
    public $backdropId = null;
    public $params;

    function __construct() {
        $this->params = new UserItemsParams();    
    }
}

function categoryBrowseURL($categoryName, $searchTerm, $collectionType = 'search', $topParentId = null, $topParentName = null)
{
    if (empty($collectionType)) {
        $collectionType = 'search';
    }

    $cbp = new CategoryBrowseParams();
    $cbp->collectionType = $collectionType;
    $cbp->topParentName = $topParentName;
    $cbp->topParentId = $topParentId;
    $cbp->params->addParam($categoryName, $searchTerm);

    if ($cbp->collectionType === 'search') {
        //top level, just link on the category page 
    } else {
        //filter by the displayed collectiontype, tv, movie, boxset...
        $cbp->folderType = 'CollectionFolder'; 
    }
    return categoryBrowseURLEx($cbp);
}

function categoryBrowseQSShort($categoryName, $searchTerm, $prefix = null)
{
    $cbp = new CategoryBrowseParams();
    if (is_array($categoryName)) {
        for ($i=0; $i < count($categoryName); $i++) { 
            $cbp->params->addParam($categoryName[$i], $searchTerm[$i]);
        }
    } else {
        $cbp->params->addParam($categoryName, $searchTerm);
    }
    return categoryBrowseURLEx($cbp, $prefix);
}

function categoryBrowseURLEx(CategoryBrowseParams $cbp, $prefix = 'browse.php?')
{
    return $prefix . http_build_query($cbp);
}

?>