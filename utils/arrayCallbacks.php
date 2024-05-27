<?php #Item Array Callbacks
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
    return mb_substr($item->Name, 0, TITLETRUNCATE);
}
#endregion Name

function getPlot($item)
{
    global $prettySpecialFeatures;
    return truncatePlot($item->Overview ?? $prettySpecialFeatures[$item->ExtraType], true);
}

function getFirstAdditionalAudio($item)
{
    $firstAdditional = getAdditionalAudioStreams($item)[0];
    if ($firstAdditional) {
        $title = $firstAdditional->Title ?? $firstAdditional->DisplayTitle;
    }
    return $title ? 'Additional audio: ' . $title : '';
}

function getParentIndexNumber($item)
{
    // 0 = specials season, X = regular season, -1 = extras/special features
    return $item->ParentIndexNumber ?? -1;
}

function getIndexNumber($item)
{
    return $item->IndexNumber ?? '';
}

function getIndexNumberEnd($item)
{
    return $item->IndexNumberEnd ?? '';
}

function getImage($item)
{
    $imageProps = new ImageParams(null, 278, $item->ImageTags->Primary);
    $imageProps->setIndicators($item, ImageParams::SMALLINDICATORS);

    return $item->ImageTags->Primary ? getImageURL($item->Id, $imageProps, ImageType::PRIMARY) : 'images/wall/transparent.png';
}

function getMediaSourceID($item)
{
    return $item->MediaSources[0]->Id ?? $item->Id;
}

function getURL($item)
{
    return translatePathToNMT($item->MediaSources[0]->Path ?? $item->Path);
}

function getRuntimeSeconds($item)
{
    return TicksToSeconds($item->MediaSources[0]->RunTimeTicks ?? $item->RunTimeTicks);
}

function runtimeDescription($item, $JSStyle = true)
{
    $runTimeTicks = $item->MediaSources[0]->RunTimeTicks ?? $item->RunTimeTicks;
    if ($runTimeTicks) {
        $br = $JSStyle ? '\xa0' : '&nbsp;';
        return round(TicksToSeconds($runTimeTicks) / 60) . $br . 'mins';
    }
}

function filterPeople($person)
{
   return $person->Type != 'Producer' && $person->Type != 'Writer' && $person->Type != 'Director';
}
