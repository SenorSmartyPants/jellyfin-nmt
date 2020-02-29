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

function videoAttributes($item)
{
    $attrs = 'vod="" href="' . translatePathToNMT($item->Path) . '"';
    if ($item->VideoType != "VideoFile")
    {
        $attrs .= ' zcd="2"';
    }
    return $attrs;
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

function formatCast($cast, $limit, $separator = ' / ')
{
    $links = array();
    $cast = array_slice($cast, 0, $limit);
    foreach($cast as $person) {
        $links[] = '<a href="itemDetails.php?id=' . $person->Id . '">' . $person->Name . '</a>';
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
?>