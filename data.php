<?php

include 'secrets.php';

$apiCallCount = 0;

function apiCall($path, $debug = false)
{
    global $api_url, $api_key, $apiCallCount;

    $apiCallCount++;

    $url = $api_url . '/emby' . $path . "&api_key=" . $api_key;
    if ($debug) echo "<a href=\"" . $url . "\">url</a><br/>";

    return json_decode(file_get_contents($url));
}

function itemImageExists($itemId, $ImageType = 'Primary')
{
    if ($itemId != '') {
        $path =  "/Items/" . $itemId . "/Images/?";
        $images = apiCall($path);

        $foundImages = array_filter($images, function($image) use ($ImageType) { return $image->ImageType == $ImageType; });
        return (count($foundImages) > 0);
    } else {
        return false;
    }
}

function firstEpisodeFromSeason($seasonId, $seasonNumber)
{
    //seasonNumber - don't include specials in regular seasons
    $all_episodes = getUsersItems(null, "Path", 1, $seasonId, $seasonNumber);

    //return first
    return $all_episodes->Items[0];
}

function firstEpisodeFromSeries($seriesId)
{
    $all_episodes = getUsersItems(null, "Path", 1, $seriesId, null, null, "episode", null, null, true);

    return $all_episodes->Items[0];
}

function firstSeasonFromSeries($seriesId)
{
    $seasons = getUsersItems(null, null, 1, $seriesId, null, null, "season");

    return $seasons->Items[0];
}

function YAMJpath($item) {
    global $jukebox_url;
    
    return $jukebox_url . pathinfo($item->Path)['filename'] . ".html";
}

function getSeasonBySeriesIdURL($seriesId)
{
    //find first episode in season, this will be YAMJ filename
    return YAMJpath(firstEpisodeFromSeries($seriesId));
}

function getSeasonURL($SeasonId, $ParentIndexNumber)
{
    //find first episode in season, this will be YAMJ filename
    return YAMJpath(firstEpisodeFromSeason($SeasonId, $ParentIndexNumber));
}

function getUsersItems($suffix = null, $fields = null, $limit = null, 
    $parentID = null, $parentIndexNumber = null, $sortBy = null, $type = null,
    $groupItems = null, $isPlayed = null, $Recursive = null, $startIndex = 0, $excludeItemTypes = null,
    $genres = null, $nameStartsWith = null, $ratings = null, $tags = null, $years = null, 
    $personIDs = null, $studioIDs = null)
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
    $path .= $genres ? "&Genres=" . urlencode($genres) : "";
    $path .= $nameStartsWith ? "&NameStartsWith=" . urlencode($nameStartsWith) : "";
    $path .= $ratings ? "&OfficialRatings=" . $ratings : "";
    $path .= $tags ? "&Tags=" . urlencode($tags) : "";
    $path .= $years ? "&Years=" . $years : "";
    $path .= $personIDs ? "&PersonIDs=" . $personIDs : "";
    $path .= $studioIDs ? "&StudioIDs=" . $studioIDs : "";
    $path .= !is_null($groupItems) ? "&GroupItems=" . ( $groupItems ? "true" : "false" ) : "";
    $path .= !is_null($isPlayed) ? "&IsPlayed=" . ( $isPlayed ? "true" : "false" ) : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . ( $Recursive ? "true" : "false" ) : "";


    return apiCall($path);
}

function getUsersViews()
{
    global $user_id;

    $path = "/Users/" . $user_id . "/Views/?IncludeExternalContent=false";
    return apiCall($path);
}

function getLatest($Limit)
{
    global $GroupItems;

    $type = $_GET["type"];
    return getUsersItems("Latest", "Path", $Limit, null, null, null, $type, $GroupItems);
}

function getNextUp($Limit, $startIndex = 0)
{
    global $user_id;

    $path = "/Shows/NextUp?UserID=" . $user_id .
        "&Fields=Path&Limit=" . $Limit . "&StartIndex=" . $startIndex;
    //TODO: ProviderID could be added to fields for play/checkin from browse screen

    return apiCall($path);
}

function getItems($parentID, $StartIndex, $Limit, $type = null, $recursive = null, 
    $genres = null, $nameStartsWith = null, $ratings = null, $tags = null, $years = null, 
    $personIDs = null, $studioIDs = null)
{
    return getUsersItems(null, "Path,ChildCount", $Limit, $parentID, null, "SortName", $type, 
        null, null, $recursive, $StartIndex, null, 
        $genres, $nameStartsWith, $ratings, $tags, $years, $personIDs, $studioIDs);
}

function getItem($Id) {
    return getUsersItems($Id);
}

function getSimilarItems($Id, $limit = null)
{
    global $user_id;
    $path =  "/Items/" . $Id . "/Similar?UserID=" . $user_id;
    $path .= $limit ? "&Limit=" . $limit : "";
    return apiCall($path);
}

function getFilters($parentID = null, $type = null, $Recursive = null) {
    global $user_id;

    $path = "/Items/Filters?UserID=" . $user_id;

    $path .= $parentID ? "&ParentID=" . $parentID : "";
    $path .= $type ? "&IncludeItemTypes=" . $type : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . ( $Recursive ? "true" : "false" ) : "";
    
    return apiCall($path);
}

function getImageURL($id, $height = null, $width = null, $imageType = null, $unplayedCount = null, 
    $playedIndicator = false, $tag = null, $quality = null, $itemsOrUsers = null,
    $maxHeight = null, $maxWidth = null)
{
    global $api_url; 

    $itemsOrUsers = $itemsOrUsers ?? "Items";
    $imageType = $imageType ?? "Primary";

    $URL = $api_url . "/emby/" . $itemsOrUsers . "/" . $id . "/Images/" . $imageType . "?" . ($unplayedCount ? "&UnplayedCount=" . $unplayedCount : null) .
        ($height ? "&Height=" . $height : null) . ($width ? "&Width=" . $width : null) . 
        ($maxHeight ? "&maxHeight=" . $maxHeight : null) . ($maxWidth ? "&maxWidth=" . $maxWidth : null) . 
        ($playedIndicator ? "&AddPlayedIndicator=true" : null) .
        ($tag ? "&tag=" . $tag : null) . ($quality ? "&quality=" . $quality : null);

    return $URL;
}

function getFavIconURL()
{
    global $api_url; 

    return $api_url . "/web/favicon.ico";
}

function getLogoURL()
{
    global $api_url; 

    return $api_url . "/web/assets/img/banner-light.png";
}
?>