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

function getLatest($Limit)
{
    global $user_id, $GroupItems;

    $path = "/Users/" . $user_id .
        "/Items/Latest?GroupItems=" . $GroupItems .
        "&Limit=" . $Limit;

    return apiCall($path);
}

function getSeries($seriesId) {
    global $user_id;

    //all episodes from unwatched season, no season data
    $path = "/Users/" . $user_id . "/Items/?Ids=" . $seriesId;
    $all_episodes = apiCall($path);

    //return first
    return $all_episodes->Items[0];
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
    //find first episode in season, this will be YAMJ filename
    $first_from_season = firstEpisodeFromSeason($item->SeasonId, $item->ParentIndexNumber);

    $menuItem = new stdClass();
    $menuItem->Name = $first_from_season->SeriesName . ' ' . $first_from_season->SeasonName;
    $menuItem->DetailBaseURL = pathinfo($first_from_season->Path)['filename'] . ".html";
    $menuItem->PosterID = (seasonPosterExists($first_from_season->SeasonId)) ? $first_from_season->SeasonId : $first_from_season->SeriesId;

    if ($unplayedCount == null) {
        $series = getSeries($item->SeriesId);
        $unplayedCount = $series->UserData->UnplayedItemCount;
    }
    //or 1 if I want it to show up
    $menuItem->UnplayedCount = $unplayedCount > 1 ? $unplayedCount : null;

    return $menuItem;
}

//0 additional API calls
function parseMovie($item) {
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    $menuItem->DetailBaseURL = pathinfo($item->Path)['filename'] . ".html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = null;

    return $menuItem;
}

?>