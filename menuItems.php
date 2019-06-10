<?php

include 'data.php';
$useSeasonNameForMenuItems = true;

//2 API calls total for series
//1 here + 1 in parseEpisode
function parseSeries($item)
{
    global $libraryBrowse;
    
    if ($item->UserData->Played or $libraryBrowse) {
        return parseSeries2($item);
    } else {
        //gets first unwatched episode for this series
        //sorting by Premiere Date - not quite right for dvd ordered series
        //but next up doesn't work right after show is deleted
        $unwatched = getUsersItems(null, null, 1, $item->Id, null,
            "PremiereDate", "Episode", true, false, true);

        $first_unwatched = $unwatched->Items[0];

        $menuItem = parseEpisode($first_unwatched, $item->UserData->UnplayedItemCount);

        return $menuItem;
    }
}

function parseSeries2($item) {
    global $jukebox_url, $popupHeight, $popupWidth;
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    //go to set page
    //TODO: should change this to if one season go to season page... can be with series/set redirect page...
    $menuItem->DetailURL = $jukebox_url . "Set_" . $item->Name . "_1.html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = $item->UserData->UnplayedItemCount;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?UnplayedCount=" . $menuItem->UnplayedCount . "&Height=" . $popupHeight . "&Width=" . $popupWidth;

    return $menuItem;
}

//2 API calls for Episode from Latest
//1 API additional calls for Series from Latest
function parseEpisode($item, $unplayedCount = null, $useSeasonImage = true)
{
    global $popupHeight, $popupWidth;
    global $useSeasonNameForMenuItems;
    global $libraryBrowse;

    $menuItem = new stdClass();

    if ($useSeasonNameForMenuItems) {
        $menuItem->Name = $item->SeriesName;
        $menuItem->Subtitle = $item->SeasonName;
    } else {
        $menuItem->Name = $item->SeriesName;
        $menuItem->Subtitle = 'S' . $item->ParentIndexNumber . ':E' . $item->IndexNumber . ' - ' . $item->Name;
    }

    $menuItem->DetailURL = "seasonRedirect.php?SeasonId=" . $item->SeasonId . "&ParentIndexNumber=" . $item->ParentIndexNumber;

    if ($useSeasonImage) {
        //API
        $menuItem->PosterID = (seasonPosterExists($item->SeasonId)) ? $item->SeasonId : $item->SeriesId;
    } else {
        $menuItem->PosterID = $item->SeriesId;
    }
    
    if ($unplayedCount == null) {
        //API
        $series = getItem($item->SeriesId);
        $unplayedCount = $series->UserData->UnplayedItemCount;
    }

    //libraryBrowse, but should be based on if watched are hidden, like always in next up, or sometimes in latest
    $minUnplayedCount = $libraryBrowse ? 0 : 1;
    $menuItem->UnplayedCount = $unplayedCount > $minUnplayedCount ? $unplayedCount : null;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?UnplayedCount=" . $menuItem->UnplayedCount . "&Height=" . $popupHeight . "&Width=" . $popupWidth;

    return $menuItem;
}

//0 additional API calls
function parseMovie($item) {
    global $jukebox_url, $popupHeight, $popupWidth;
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    $menuItem->Subtitle = $item->ProductionYear;
    $menuItem->DetailURL = $jukebox_url . pathinfo($item->Path)['filename'] . ".html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = null;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . 
        "/Images/Primary?Height=" . $popupHeight . "&Width=" . $popupWidth . 
        ($item->UserData->Played ? "&AddPlayedIndicator=true" : null);

    return $menuItem;
}

function parseCollectionFolder($item) {
    global $popupHeight, $popupWidth;

    switch ($item->CollectionType) {
        case "tvshows":
        case "movies":
            $menuItem = new stdClass();
            $menuItem->Name = $item->Name;
            $menuItem->DetailURL = "browse.php?parentId=" . $item->Id . 
                "&CollectionType=" . $item->CollectionType .
                "&Name=" . $item->Name;
            $menuItem->PosterID = $item->Id;
            $menuItem->UnplayedCount = null;
            $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?Height=" . $popupHeight . "&Width=" . $popupWidth;
            break;
        default:
            $menuItem = null;
            break;
    }

    return $menuItem;
}
?>