<?

//NMT player path
$NMT_path = "/storage/media/Videos/"; //server based path to share to NMT
$NMT_playerpath = "file:///opt/sybhttpd/localhost.drives/NETWORK_SHARE/storage/media/Videos/";  //NMT path to the share

function getBackdropIDandTag($item)
{
    $retval = new stdClass();
    if ($item->BackdropImageTags && count($item->BackdropImageTags) > 0) {
        $retval->Id = $item->Id;
        $retval->Tag = $item->BackdropImageTags[0];
    } elseif ($item->ParentBackdropImageTags && count($item->ParentBackdropImageTags) > 0) {
        $retval->Id = $item->ParentBackdropItemId;
        $retval->Tag = $item->ParentBackdropImageTags[0];
    } else {
        $retval->Id = null;
        $retval->Tag = null;
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
    if ($assocArray) {
        foreach ($assocArray as $key => $value) {
            $html .= $key . '="' . $value . '" ';
        }
    }
    return $html;
}

function videoAttributes($item)
{
    $attrs = array('vod' => '', 
        'href' => translatePathToNMT($item->Path));

    if ($item->VideoType != "VideoFile")
    {
        $attrs['zcd'] = "2";
    }
    return HTMLattributes($attrs);
}

function videoPlayLink($item, 
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
        $html .= videoAttributes($item);
    }
    
    $html .= '>' . $linkHTML . '</a>';

    if ($callbackJS) {
        $html .= '<a onfocusload="" ';
        $html .= 'name="' . $callbackName . '" ';
        $html .= 'onfocusset="' . $linkName . '" ';
        $html .= HTMLattributes($callbackAdditionalAttributes);      
        $html .= videoAttributes($item);
        $html .= '></a>';
    }

    return $html;
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

?>