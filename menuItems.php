<?php

include_once 'config.php';
include_once 'data.php';
include_once 'utils.php';
$useSeasonNameForMenuItems = true;
$episodeNameInTitle = false;

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

function parse($item)
{
    global $indexStyle;

    $menuItem = new stdClass();

    // massage recordings to look like final output
    if ($item->Type == ItemType::RECORDING && $item->IsSeries) {
        // treat it like an episode
        $item->Type = ItemType::EPISODE;
        $item->Name = $item->EpisodeTitle;
    }

    $menuItem->Name = getName($item);
    $menuItem->Subtitle = getSubtitle($item);
    $menuItem->BackdropID = getBackdropIDandTag($item)->Id;
    setDetailURL($item, $menuItem);
    setPosterInfo($item, $menuItem);
    $menuItem->UnplayedCount = getUnplayedCount($item);

    $played = ($item->Type == ItemType::SERIES || $item->Type == ItemType::SEASON ? null : $item->UserData->Played);

    if ($menuItem->PosterID) {
        $imageProps = new ImageParams();
        $imageProps->height = ($indexStyle->popupHeight ?? $indexStyle->thumbnailsHeight);
        $imageProps->width = ($indexStyle->popupWidth ?? $indexStyle->thumbnailsWidth);
        $imageProps->unplayedCount = $menuItem->UnplayedCount;
        $imageProps->AddPlayedIndicator = $played;
        $imageProps->percentPlayed = $item->UserData->PlayedPercentage > 0 ? $item->UserData->PlayedPercentage : null;
        $imageProps->mediaSourceCount = $item->MediaSourceCount && $item->MediaSourceCount > 1 ? $item->MediaSourceCount : null;

        $menuItem->PosterURL = getImageURL($menuItem->PosterID, $imageProps, $menuItem->ImageType);
    } elseif ($item->Type == ItemType::ACTOR || $item->Type == ItemType::GUESTSTAR) {
        $menuItem->PosterID = -1;
        $menuItem->PosterURL = 'images/person/person' . rand(1, 5) . '.png';
    }

    return $menuItem;
}

function getName($item)
{
    global $episodeNameInTitle;
    if ($item->Type == ItemType::EPISODE) {
        if ($episodeNameInTitle) {
            $name = $item->IndexNumber . '. ' . $item->Name;
        } else {
            $name = $item->SeriesName;
        }
    } else {
        $name = $item->Name;
    }
    return $name;
}

function getSubtitle($item)
{
    global $useSeasonNameForMenuItems, $prettySpecialFeatures, $episodeNameInTitle;
    switch ($item->Type) {
        case ItemType::EPISODE:
            if ($useSeasonNameForMenuItems) {
                $subtitle = $item->SeasonName;
            } else {
                if (!$episodeNameInTitle) {
                    $subtitle = 'S' . $item->ParentIndexNumber . ':E' . $item->IndexNumber . ' - ' . $item->Name;
                }
            }
            break;
        case ItemType::SERIES:
            $subtitle = ProductionRangeString($item);
            break;
        //PersonTypes
        case ItemType::ACTOR:
        case "Director":
        case "Writer":
        case "Producer":
        case ItemType::GUESTSTAR:
        case "Composer":
        case "Conductor":
        case "Lyricist":
            $subtitle = ($item->Role ? "as " . $item->Role : $item->Type);
            if ($subtitle == ItemType::GUESTSTAR) {
                $subtitle = "Guest star";
            }
            break;
        default:
            $subtitle = $item->ProductionYear;
            if ($item->ExtraType) {
                if ($item->ExtraType !== 'Unknown') {
                    $subtitle = $prettySpecialFeatures[$item->ExtraType];
                } else {
                    $subtitle = ' ';
                }
            }
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
    $cbp->params->ParentID = $item->Id;
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
    global $forceItemDetails, $regularSeason;
    $regularSeason = null;

    switch ($item->MediaType) {
        case "Video":
            if ($item->Type == ItemType::EPISODE) {
                //check for season info, very rarely an episode has no season IDs provided
                if ($item->SeasonId) {
                    $detailURL = "Season.php?id=" . $item->SeasonId . "&episode=" . $item->IndexNumber;
                    //for specials check airs[after|before] info and display that season
                    //but seasonID for airs season not included
                    $specialInSeason = $item->AirsBeforeSeasonNumber ?? $item->AirsAfterSeasonNumber;
                    if ($specialInSeason) {
                        $regularSeason = getSeasonFromSeriesBySeasonNumber($item->SeriesId, $specialInSeason);
                        $detailURL = "Season.php?id=" . $regularSeason->Id . "&episode=" . $item->IndexNumber . "&special";
                    }
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

function setDetailURL($item, $menuItem)
{
    if ($item->IsFolder) {
        $detailURL = getFolderURL($item, $menuItem);
    } else {
        $detailURL = getNonFolderURL($item, $menuItem);
    }
    $menuItem->DetailURL = $detailURL;
}

function getEpisodePosterID($item, $useSeasonImage)
{
    global $indexStyle, $regularSeason;

    //check for special in a regular season
    $seasonID = is_null($regularSeason) ? $item->SeasonId : $regularSeason->Id;

    //API
    if ($useSeasonImage && itemImageExists($seasonID, $indexStyle->ImageType)) {
        return $seasonID;
    } else {
        return $item->SeriesPrimaryImageTag ? $item->SeriesId : null;
    }
}

function setPosterInfo($item, $menuItem)
{
    global $indexStyle;
    global $displayepisode;

    $menuItem->ImageType = $indexStyle->ImageType;

    switch ($item->Type) {
        case ItemType::SEASON:
            $posterID = $item->ImageTags->{$indexStyle->ImageType} ? $item->Id : $item->SeriesId;
            break;
        case ItemType::EPISODE:
            if (!$displayepisode) {
                $posterID = getEpisodePosterID($item, true);
                break;
            }
        default:
            if ($item->ImageTags) {
                $posterID = $item->ImageTags->{$indexStyle->ImageType} ? $item->Id : null;
            } else {
                $posterID = $item->PrimaryImageTag ? $item->Id : null;
            }
            if (!$posterID && $item->MediaType == 'Video') {
                //show parent thumb instead
                $menuItem->ImageType = ImageType::THUMB;
                $posterID = $item->ParentThumbItemId;
            }
            break;
    }
    $menuItem->PosterID = $posterID;
}

function getUnplayedCount($item)
{
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

function getMenuItem($item)
{
    if ($item->Type == ItemType::SERIES) {
        $menuItem = parseSeries($item);
    } else {
        $menuItem = parse($item);
    }
    return $menuItem;
}
