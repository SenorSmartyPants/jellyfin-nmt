<?php

include_once 'config.php';
include_once 'data.php';
include_once 'utils.php';
$useSeasonNameForMenuItems = true;

//2 API calls total for series
//1 here + 1 in parse(episode)
function parseSeries($item)
{
    global $libraryBrowse;
    
    if ($item->UserData->Played || $libraryBrowse) {
        return parse($item);
    } else {
        //gets first unwatched episode for this series
        //sorting by Premiere Date - not quite right for dvd ordered series
        //but next up doesn't work right after show is deleted

        $params = new UserItemsParams();
        $params->Limit = 1;
        $params->ParentID = $item->Id;
        $params->SortBy = 'PremiereDate';
        $params->IncludeItemTypes = ItemType::EPISODE;
        $params->GroupItems = true;
        $params->IsPlayed = false;
        $params->Recursive = true;

        $unwatched = getUsersItems($params);

        $first_unwatched = $unwatched->Items[0];
        $first_unwatched->UserData->UnplayedItemCount = $item->UserData->UnplayedItemCount;
        return parse($first_unwatched);
    }
}

function parse($item) {
    global $indexStyle;

    $menuItem = new stdClass();
    $menuItem->Name = getName($item);
    $menuItem->Subtitle = getSubtitle($item);
    $menuItem->BackdropID = getBackdropIDandTag($item)->Id;
    setDetailURL($item, $menuItem);
    $menuItem->PosterID = getPosterID($item);
    $menuItem->UnplayedCount = getUnplayedCount($item);

    $played = ($item->Type == ItemType::SERIES || $item->Type == ItemType::SEASON ? null : $item->UserData->Played);

    if ($menuItem->PosterID) {
        $imageProps = new ImageParams();
        $imageProps->height = ($indexStyle->popupHeight ?? $indexStyle->thumbnailsHeight);
        $imageProps->width = ($indexStyle->popupWidth ?? $indexStyle->thumbnailsWidth); 
        $imageProps->unplayedCount = $menuItem->UnplayedCount;
        $imageProps->AddPlayedIndicator = $played;

        $menuItem->PosterURL = getImageURL($menuItem->PosterID, $imageProps, $indexStyle->ImageType);
    }

    return $menuItem;
}

function getName($item) {
    if ($item->Type == ItemType::EPISODE) {
        $name = $item->SeriesName;
    } else {
        $name = $item->Name;
    }
    return $name;
}

function getSubtitle($item) {
    global $useSeasonNameForMenuItems;
    switch ($item->Type) {
        case ItemType::EPISODE:
            if ($useSeasonNameForMenuItems) {
                $subtitle = $item->SeasonName;
            } else {
                $subtitle = 'S' . $item->ParentIndexNumber . ':E' . $item->IndexNumber . ' - ' . $item->Name;
            }
            break;
        case ItemType::SERIES:
            $subtitle = ProductionRangeString($item);
            break;
        //PersonTypes
        case "Actor":
        case "Director":
        case "Writer":
        case "Producer":
        case "GuestStar":
        case "Composer":
        case "Conductor":
        case "Lyricist":
            $subtitle = ($item->Role ? "as " . $item->Role : $item->Type);
            if ($subtitle == "GuestStar") {
                $subtitle = "Guest star";
            }
            break;
        default:
            $subtitle = $item->ProductionYear;
            break;
    }
    return $subtitle;
}

function getFolderURL($item, $menuItem)
{
    global $forceItemDetails;

    //set up default browsing params
    $cbp = new CategoryBrowseParams();
    $cbp->name = $item->Name;
    $cbp->folderType = $item->Type;
    $cbp->collectionType = $item->CollectionType;
    $cbp->parentId = $item->Id;
    $cbp->backdropId = $menuItem->BackdropID;
    switch ($item->Type) {
        case ItemType::COLLECTIONFOLDER:
        case ItemType::USERVIEW:
            //set topParentId from Id
            $cbp->name = null;
            $cbp->topParentId = $item->Id;
            $cbp->topParentName = $item->Name;
            $detailURL = categoryBrowseURLEx($cbp);
            break;            
        case ItemType::SEASON:
            $detailURL = "Season.php?id=" . $item->Id;
            break;   
        case ItemType::SERIES:
            //go directly to season page, or continue to default
            if ($item->ChildCount == 1) {
                $detailURL = "seasonRedirect.php?SeasonType=first&SeriesId=" . $item->Id;
                break;
            }   
        default:
            //get topParentId from querystring
            $cbp->topParentId = $_GET['topParentId'];
            $cbp->topParentName = $_GET['topParentName'];
            $detailURL = categoryBrowseURLEx($cbp);
            break;
    }
    if ($forceItemDetails) {
        //default to itemDetails page
        $detailURL = itemDetailsLink($item->Id);
    }
    return $detailURL;
}

function getNonFolderURL($item, $menuItem)
{
    global $forceItemDetails;

    switch ($item->MediaType) {
        case "Video":
            if ($item->Type == ItemType::EPISODE) {
                //check for season info, very rarely an episode has no season IDs provided
                if ($item->SeasonId) {
                    $detailURL = "Season.php?id=" . $item->SeasonId . "&episode=" . $item->IndexNumber;
                } else {
                    //try season redirect, latest season will probably be the one that doesn't have all metadata
                    //I think this is why an episode won't have a seasonID
                    $detailURL = "seasonRedirect.php?SeasonType=latest&SeriesId=" . $item->SeriesId
                        . "&IndexNumber=" . $item->IndexNumber;
                }
            }
            break;
        case "Audio":
            $detailURL = translatePathToNMT($item->Path);
            $menuItem->OnDemandTag = "AOD";
            break;
        case "Photo":
            $detailURL = translatePathToNMT($item->Path);
            $menuItem->OnDemandTag = "POD";
            break;                
        default:
            break;
    }
    if (!$detailURL || $forceItemDetails) {
        //default to itemDetails page
        $detailURL = itemDetailsLink($item->Id);
    }
    return $detailURL; 
}

function setDetailURL($item, $menuItem) {   
    if ($item->IsFolder) {
        $detailURL = getFolderURL($item, $menuItem);
    } else {
        $detailURL = getNonFolderURL($item, $menuItem);
    }
    $menuItem->DetailURL = $detailURL;
}

function getEpisodePosterID($item, $useSeasonImage)
{
    global $indexStyle;
    //API
    if ($useSeasonImage && itemImageExists($item->SeasonId, $indexStyle->ImageType)) {
        return $item->SeasonId;
    } else {
        return $item->SeriesPrimaryImageTag ? $item->SeriesId : null;
    }
}

function getPosterID($item, $useSeasonImage = true) {
    global $indexStyle;
    global $displayepisode;
    switch ($item->Type) {
        case ItemType::SEASON:
            $posterID = $item->ImageTags->{$indexStyle->ImageType} ? $item->Id : $item->SeriesId;
            break;
        case ItemType::EPISODE:
            if (!$displayepisode) {
                $posterID = getEpisodePosterID($item, $useSeasonImage); 
                break;
            }
        default:
            if ($item->ImageTags) {
                $posterID = $item->ImageTags->{$indexStyle->ImageType} ? $item->Id : null;
            } else {
                $posterID = $item->PrimaryImageTag ? $item->Id : null;
            }
            break; 
    }
    return $posterID;
}

function getUnplayedCount($item) {
    global $libraryBrowse;
    global $displayepisode;

    if ($item->Type == ItemType::EPISODE) {
        if (!$displayepisode) {
            //API
            $series = getItem($item->SeriesId);
            $unplayedCount = $series->UserData->UnplayedItemCount;
        }
    } else {
        $unplayedCount = $item->UserData->UnplayedItemCount;
    }
    //libraryBrowse, but should be based on if watched are hidden, like always in next up, or sometimes in latest
    $minUnplayedCount = $libraryBrowse ? 0 : 1;
    return $unplayedCount > $minUnplayedCount ? $unplayedCount : null;
}

function getMenuItem($item) {
    if ($item->Type == ItemType::SERIES) {
        $menuItem = parseSeries($item);
    } else {
        $menuItem = parse($item);
    }
    return $menuItem;
}
?>