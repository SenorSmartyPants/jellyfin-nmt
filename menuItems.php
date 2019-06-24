<?php

include 'config.php';
include 'data.php';
$useSeasonNameForMenuItems = true;

//2 API calls total for series
//1 here + 1 in parse(episode)
function parseSeries($item)
{
    global $libraryBrowse;
    
    if ($item->UserData->Played or $libraryBrowse) {
        return parse($item);
    } else {
        //gets first unwatched episode for this series
        //sorting by Premiere Date - not quite right for dvd ordered series
        //but next up doesn't work right after show is deleted
        $unwatched = getUsersItems(null, null, 1, $item->Id, null,
            "PremiereDate", "Episode", true, false, true);

        $first_unwatched = $unwatched->Items[0];
        $first_unwatched->UserData->UnplayedItemCount = $item->UserData->UnplayedItemCount;
        $menuItem = parse($first_unwatched);

        return $menuItem;
    }
}

function parse($item) {
    global $popupHeight, $popupWidth;

    $menuItem = new stdClass();
    $menuItem->Name = getName($item);
    $menuItem->Subtitle = getSubtitle($item);    
    $menuItem->BackdropID = getBackdropID($item);
    setDetailURL($item, $menuItem);
    $menuItem->PosterID = getPosterID($item);
    $menuItem->UnplayedCount = getUnplayedCount($item);

    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?UnplayedCount=" . $menuItem->UnplayedCount . 
        "&Height=" . $popupHeight . "&Width=" . $popupWidth . 
        ($item->UserData->Played ? "&AddPlayedIndicator=true" : null);

    return $menuItem;
}

function getName($item) {
    if ($item->Type == 'Episode') {
        $name = $item->SeriesName;
    } else {
        $name = $item->Name;
    }
    return $name;
}

function getSubtitle($item) {
    global $useSeasonNameForMenuItems;
    switch ($item->Type) {
        case "Episode":
            if ($useSeasonNameForMenuItems) {
                $subtitle = $item->SeasonName;
            } else {
                $subtitle = 'S' . $item->ParentIndexNumber . ':E' . $item->IndexNumber . ' - ' . $item->Name;
            }
            break;
        case "Series":
            break;
        default:
            $subtitle = $item->ProductionYear;
            break;
    }
    return $subtitle;
}

function setDetailURL($item, $menuItem) {
    global $jukebox_url, $NMT_path, $NMT_playerpath;
    
    if ($item->IsFolder) {
        switch ($item->Type) {
            case "Season":
                $detailURL = "seasonRedirect.php?SeasonId=" . $item->Id . "&ParentIndexNumber=" . $item->IndexNumber;
                break;   
            case "Series":
                //go directly to season page, or continue to default
                if ($item->ChildCount == 1) {
                    $detailURL = "seasonRedirect.php?SeriesId=" . $item->Id;
                    break;
                }   
            default:
                $detailURL = "browse.php?parentId=" . $item->Id . 
                    "&FolderType=" . $item->Type .
                    "&CollectionType=" . $item->CollectionType .
                    "&Name=" . $item->Name .
                    ($menuItem->BackdropID ? "&backdropId=" . $menuItem->BackdropID : null);
                break;
        }
    } else {
        switch ($item->MediaType) {
            case "Video":
        switch ($item->Type) {
            case "Movie":
                $detailURL = $jukebox_url . pathinfo($item->Path)['filename'] . ".html";
                break; 
            case "Episode":
                //check for season info, very rarely an episode has no season IDs provided
                if ($item->SeasonId) {
                    $detailURL = "seasonRedirect.php?SeasonId=" . $item->SeasonId . "&ParentIndexNumber=" . $item->ParentIndexNumber;
                } else {
                    //try season redirect, probably only one season
                    $detailURL = "seasonRedirect.php?SeriesId=" . $item->SeriesId;
                }
                        break;
                    default:
                        $detailURL = str_replace($NMT_path,$NMT_playerpath,$item->Path);
                        $menuItem->OnDemandTag = "VOD";
                        break; 
                }
                break;
            case "Audio":
                $detailURL = str_replace($NMT_path,$NMT_playerpath,$item->Path);
                $menuItem->OnDemandTag = "AOD";
                break;
            case "Photo":
                $detailURL = str_replace($NMT_path,$NMT_playerpath,$item->Path);
                $menuItem->OnDemandTag = "POD";
                break;                
            default:
                break;
        }
    }
    $menuItem->DetailURL = $detailURL;
}

function getPosterID($item, $useSeasonImage = true) {
    switch ($item->Type) {
        case "Season":
            $posterID = $item->ImageTags->Primary ? $item->Id : $item->SeriesId;
            break;
        case "Episode":
            //API
            $posterID = ($useSeasonImage && seasonPosterExists($item->SeasonId)) ? $item->SeasonId : $item->SeriesId;
            break;
        default:
            $posterID = $item->ImageTags->Primary ? $item->Id : null;
            break; 
    }
    return $posterID;
}

function getBackdropID($item) {
    $backdropID = (count($item->BackdropImageTags) > 0) ? $item->Id : 
        (($item->ParentBackdropImageTags && count($item->ParentBackdropImageTags) > 0) ? $item->ParentBackdropItemId : null);
    return $backdropID;
}

function getUnplayedCount($item) {
    global $libraryBrowse;

    switch ($item->Type) {
        case "Episode":
            //API
            $series = getItem($item->SeriesId);
            $unplayedCount = $series->UserData->UnplayedItemCount;
            break;
        default:
            $unplayedCount = $item->UserData->UnplayedItemCount;
            break; 
    }
    //libraryBrowse, but should be based on if watched are hidden, like always in next up, or sometimes in latest
    $minUnplayedCount = $libraryBrowse ? 0 : 1;
    $unplayedCount = $unplayedCount > $minUnplayedCount ? $unplayedCount : null;

    return $unplayedCount;
}

function getMenuItem($item) {
    switch ($item->Type) {
        case "Series":
            $menuItem = parseSeries($item);
            break;
        default:
            $menuItem = parse($item);
            break;                    
    }
    return $menuItem;
}
?>