<?php

include 'secrets.php';

$apiCallCount = 0;

function apiCall($path, $debug = false)
{
    global $api_url, $api_key, $apiCallCount;

    $apiCallCount++;

    $url = $api_url . $path . "&api_key=" . $api_key;
    if ($debug) echo "<a href=\"" . $url . "\">url</a><br/>";

    return json_decode(file_get_contents($url));
}

function seasonPosterExists($seasonId)
{
    //seasons usually have a Primary or nothing
    $path =  "/Items/" . $seasonId . "/Images/?";
    $images = apiCall($path);

    return (count($images) > 0);
}

function firstEpisodeFromSeason($seasonId, $seasonNumber)
{
    global $user_id;

    //all episodes from unwatched season, no season data
    //ParentIndexNumber - don't include specials in regular seasons
    $path = "/Users/" . $user_id .
        "/Items/?Limit=1&ParentID=" . $seasonId . "&ParentIndexNumber=" . $seasonNumber .
        "&Fields=Path";
    $all_episodes = apiCall($path);

    //return first
    return $all_episodes->Items[0];
}

function getDetailBaseURL($SeasonId, $ParentIndexNumber)
{
    //find first episode in season, this will be YAMJ filename
    $first_from_season = firstEpisodeFromSeason($SeasonId, $ParentIndexNumber);

    return pathinfo($first_from_season->Path)['filename'] . ".html";
}

function getLatest($Limit)
{
    global $user_id, $GroupItems;

    $type = $_GET["type"];
    $path = "/Users/" . $user_id .
        "/Items/Latest?GroupItems=" . $GroupItems .
        "&IncludeItemTypes=" . $type . "&Fields=Path&Limit=" . $Limit;

    return apiCall($path);
}

function getItem($Id) {
    global $user_id;

    $path = "/Users/" . $user_id . "/Items/" . $Id . "?";

    return apiCall($path);
}

//3 API calls total for series
//1 here + 2 in parseEpisode
function parseSeries($item)
{
    global $user_id;

    //gets first unwatched episode for this series
    $path = "/Users/" . $user_id . "/Items?ParentID=" . $item->Id .
        "&Recursive=true&IncludeItemTypes=Episode&IsPlayed=false&Limit=1";

    $unwatched = apiCall($path);

    $first_unwatched = $unwatched->Items[0];

    $menuItem = parseEpisode($first_unwatched, $item->UserData->UnplayedItemCount);

    return $menuItem;
}

//3 API calls for Episode from Latest
//2 API additional calls for Series from Latest
function parseEpisode($item, $unplayedCount = null)
{
    global $popupHeight, $popupWidth;

    $menuItem = new stdClass();
    $menuItem->Name = $item->SeriesName . ' ' . $item->SeasonName;
    $menuItem->DetailBaseURL = getDetailBaseURL($item->SeasonId, $item->ParentIndexNumber);
    $menuItem->PosterID = (seasonPosterExists($item->SeasonId)) ? $item->SeasonId : $item->SeriesId;

    if ($unplayedCount == null) {
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
    global $popupHeight, $popupWidth;
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    $menuItem->DetailBaseURL = pathinfo($item->Path)['filename'] . ".html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = null;
    $menuItem->PosterBaseURL = "/Items/" . $menuItem->PosterID . "/Images/Primary?Height=" . $popupHeight . "&Width=" . $popupWidth;

    return $menuItem;
}

?>