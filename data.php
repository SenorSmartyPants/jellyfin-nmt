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
    if ($seasonId != '') {
        //seasons usually have a Primary or nothing
        $path =  "/Items/" . $seasonId . "/Images/?";
        $images = apiCall($path);

        return (count($images) > 0);
    } else {
        return false;
    }
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

function getSeasonURL($SeasonId, $ParentIndexNumber)
{
    global $jukebox_url;
    //find first episode in season, this will be YAMJ filename
    $first_from_season = firstEpisodeFromSeason($SeasonId, $ParentIndexNumber);

    return $jukebox_url . pathinfo($first_from_season->Path)['filename'] . ".html";
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

function getNextUp($Limit)
{
    global $user_id;

    $path = "/Shows/NextUp?UserID=" . $user_id .
        "&Fields=Path&Limit=" . $Limit;
    //TODO: ProviderID could be added to fields for play/checkin from browse screen

    return apiCall($path);
}

function getItem($Id) {
    global $user_id;

    $path = "/Users/" . $user_id . "/Items/" . $Id . "?";

    return apiCall($path);
}
?>