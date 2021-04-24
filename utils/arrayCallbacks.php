<?  #Item Array Callbacks
#region UserData
function getPlayed($item)
{
    return $item->UserData->Played;
}

function getStartPosition($item)
{
    global $skipTrim;

    return $skipTrim->getStartPosition($item->UserData);
}
#endregion UserData

function getRuntimeSeconds($item)
{
    return TicksToSeconds($item->RunTimeTicks);
}

function getVOD($item)
{
    return 'vod';
}

#region Name
function getTruncateTitle($item)
{
    return truncateTitle($item->Name);
}

function getTitleCSS($item)
{
    return titleCSS(strlen($item->Name));
}

function getShortTitle($item)
{
    return substr($item->Name, 0, TITLETRUNCATE);
}
#endregion Name

function getPlot($item)
{
    return truncatePlot($item->Overview, true);
}

function getIndexNumberEnd($item)
{
    return $item->IndexNumberEnd ?? '';
}

function getImage($item)
{
    return $item->ImageTags->Primary ? getImageURL($item->Id, new ImageParams(null, 278, $item->ImageTags->Primary), ImageType::PRIMARY) : 'images/wall/transparent.png';
}

function getURL($item)
{
    return translatePathToNMT($item->Path);
}
?>