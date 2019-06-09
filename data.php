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
    //all episodes from unwatched season, no season data
    //ParentIndexNumber - don't include specials in regular seasons
    $all_episodes = getUsersItems(null, "Path", 1, $seasonId, $seasonNumber);
    //$all_episodes = apiCall($path);

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

function getUsersItems($suffix = null, $fields = null, $limit = null, 
    $parentID = null, $parentIndexNumber = null, $sortBy = null, $type = null,
    $groupItems = null, $isPlayed = null, $Recursive = null, $startIndex = 0, $excludeItemTypes = null)
{
    global $user_id;

    $path = "/Users/" . $user_id . "/Items/" . $suffix . "?";

    $path .= $fields ? "Fields=" . $fields : "";
    $path .= $startIndex ? "&StartIndex=" . $startIndex : "";
    $path .= $limit ? "&Limit=" . $limit : "";
    $path .= $parentID ? "&ParentID=" . $parentID : "";
    $path .= $parentIndexNumber ? "&ParentIndexNumber=" . $parentIndexNumber : "";
    $path .= $type ? "&IncludeItemTypes=" . $type : "";
    $path .= $excludeItemTypes ? "&ExcludeItemTypes=" . $excludeItemTypes : "";
    $path .= $sortBy ? "&SortBy=" . $sortBy : "";
    $path .= !is_null($groupItems) ? "&GroupItems=" . ( $groupItems ? "true" : "false" ) : "";
    $path .= !is_null($isPlayed) ? "&IsPlayed=" . ( $isPlayed ? "true" : "false" ) : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . ( $Recursive ? "true" : "false" ) : "";


    return apiCall($path);
}

function getUsersViews()
{
    global $user_id;

    $path = "/Users/" . $user_id . "/Views/?";
    return apiCall($path);
}

function getLatest($Limit)
{
    global $GroupItems;

    $type = $_GET["type"];
    return getUsersItems("Latest", "Path", $Limit, null, null, null, $type, $GroupItems);
}

function getNextUp($Limit)
{
    global $user_id;

    $path = "/Shows/NextUp?UserID=" . $user_id .
        "&Fields=Path&Limit=" . $Limit;
    //TODO: ProviderID could be added to fields for play/checkin from browse screen

    return apiCall($path);
}

function getItems($parentID, $StartIndex, $Limit, $type = null, $recursive = null)
{
    return getUsersItems(null, "Path", $Limit, $parentID, null, "SortName", $type, null, null, $recursive, $StartIndex, null);
}

function getItem($Id) {
    return getUsersItems($Id);
}
?>