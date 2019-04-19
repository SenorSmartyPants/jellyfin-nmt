<?php

include 'secrets.php';

function seasonPosterExists($seasonId) {
    global $api_url, $api_key;

    $url = $api_url . "/Items/" . $seasonId . "/Images/?api_key=" . $api_key;

    //seasons usually have a Primary or nothing
    $contents = file_get_contents($url);
    $images = json_decode($contents);

    return (count($images)>0);
}

function firstEpisodeFromSeason($seasonId) {
    global $api_url, $user_id, $api_key;

    //all episodes from unwatched season, no season data
    $url = $api_url . "/Users/" . $user_id .
        "/Items/?ParentID=" . $seasonId . "&api_key=" . $api_key;

    $contents = file_get_contents($url);
    $all_episodes = json_decode($contents);

    //return first
    return $all_episodes->Items[0];
}

function getLatest($Limit)
{
    global $api_url, $user_id, $api_key, $GroupItems;

    $url = $api_url . "/Users/" . $user_id .
        "/Items/Latest?&GroupItems=" . $GroupItems .
        "&Limit=" . $Limit .
        "&api_key=" . $api_key;

    //echo "<a href=\"" . $url . "\">url</a><br/>";

    $contents = file_get_contents($url);
    return json_decode($contents);
}

function getSeries($seriesId) {
    global $api_url, $user_id, $api_key;

    //all episodes from unwatched season, no season data
    $url = $api_url . "/Users/" . $user_id .
        "/Items/?Ids=" . $seriesId . "&api_key=" . $api_key;

    $contents = file_get_contents($url);
    $all_episodes = json_decode($contents);

    //return first
    return $all_episodes->Items[0];
}

function parseSeries($item) {
    global $api_url, $user_id, $api_key;

    //gets unwatched episodes for this series
    $url = $api_url . "/Users/" . $user_id .
        "/Items/Latest?ParentID=" . $item->Id . "&GroupItems=false" . 
        "&api_key=" . $api_key;

    $contents = file_get_contents($url);
    $unwatched = json_decode($contents);

    $first_unwatched = $unwatched[count($unwatched)-1];

    $menuItem = parseEpisode($first_unwatched,$item->UserData->UnplayedItemCount);
    
    return $menuItem;
}

function parseEpisode($item,$unplayedCount = null) {
    //find first episode in season, this will be YAMJ filename
    $first_from_season = firstEpisodeFromSeason($item->SeasonId);   

    $menuItem = new stdClass();
    $menuItem->Name = $first_from_season->SeriesName . ' ' . $first_from_season->SeasonName;
    $menuItem->DetailBaseURL = pathinfo($first_from_season->Path)['filename'] . ".html";
    $menuItem->PosterID = (seasonPosterExists($first_from_season->SeasonId)) ? $first_from_season->SeasonId : $first_from_season->SeriesId;
    
    if ($unplayedCount == null)
    {
        $series = getSeries($item->SeriesId);
        $unplayedCount = $series->UserData->UnplayedItemCount;
    }
    //or 1 if I want it to show up
    $menuItem->UnplayedCount = $unplayedCount > 1 ? $unplayedCount : null;

    return $menuItem;
}

function parseMovie($item) {
    $menuItem = new stdClass();
    $menuItem->Name = $item->Name;
    $menuItem->DetailBaseURL = pathinfo($item->Path)['filename'] . ".html";
    $menuItem->PosterID = $item->Id;
    $menuItem->UnplayedCount = null;

    return $menuItem;
}

?>