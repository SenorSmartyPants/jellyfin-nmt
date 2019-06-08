<?php

include 'data.php';
$useSeasonNameForMenuItems = true;

//2 API calls total for series
//1 here + 1 in parseEpisode
function parseSeries($item)
{
    global $user_id;

    //gets first unwatched episode for this series
    //sorting by Premiere Date - not quite right for dvd ordered series
    //but next up doesn't work right after show is deleted
        $unwatched = getUsersItems(null, null, 1, $item->Id, null,
            "PremiereDate", "Episode", true, false, true);

    $first_unwatched = $unwatched->Items[0];

    $menuItem = parseEpisode($first_unwatched, $item->UserData->UnplayedItemCount);

    return $menuItem;
}

//2 API calls for Episode from Latest
//1 API additional calls for Series from Latest
function parseEpisode($item, $unplayedCount = null)
{
    global $popupHeight, $popupWidth;
    global $useSeasonNameForMenuItems;

    $menuItem = new stdClass();

    if ($useSeasonNameForMenuItems) {
        $menuItem->Name = $item->SeriesName . ' ' . $item->SeasonName;
    } else {
        $menuItem->Name = $item->SeriesName . ' S' . $item->ParentIndexNumber . ':E' . $item->IndexNumber . ' - ' . $item->Name;
    }

    $menuItem->DetailURL = "seasonRedirect.php?SeasonId=" . $item->SeasonId . "&ParentIndexNumber=" . $item->ParentIndexNumber;
    //API
    $menuItem->PosterID = (seasonPosterExists($item->SeasonId)) ? $item->SeasonId : $item->SeriesId;

    if ($unplayedCount == null) {
        //API
        $series = getItem($item->SeriesId);
        $unplayedCount = $series->UserData->UnplayedItemCount;
    }
    //or 1 if I want it to show up
    $menuItem->UnplayedCount = $unplayedCount > 1 ? $unplayedCount : null;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?UnplayedCount=" . $menuItem->UnplayedCount . "&Height=" . $popupHeight . "&Width=" . $popupWidth;

    return $menuItem;
}

//0 additional API calls
function parseMovie($item) {
    global $jukebox_url, $popupHeight, $popupWidth;
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    $menuItem->DetailURL = $jukebox_url . pathinfo($item->Path)['filename'] . ".html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = null;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?Height=" . $popupHeight . "&Width=" . $popupWidth;

    return $menuItem;
}

?>