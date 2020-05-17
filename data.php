<?php

include 'secrets.php';

const ITEMSPATH = '/Items/';

$apiCallCount = 0;

abstract class ImageType
{
    const PRIMARY = 'Primary';
    const BANNER = 'Banner';
    const THUMB = 'Thumb';
    const LOGO = 'Logo';
}

abstract class ItemType
{
    const BOXSET = 'BoxSet';
    const SERIES = 'Series';
    const SEASON = 'Season';
    const EPISODE = 'Episode';
    const MOVIE = 'Movie';
    const PERSON = 'Person';
    const STUDIO = 'Studio';
}

const CLIENTNAME = 'Jellyfin-NMT';
const CLIENTVERSION = '0.2.0';

class Device
{
    public $name = 'My testbed class';
    public $id = 1;

    function __construct() {
        $this->id = $_SERVER['REMOTE_ADDR'];

        if (stripos($_SERVER['HTTP_USER_AGENT'],"Chrome")!==false) {
            $this->name = 'Chrome';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'],"Syabas")!==false) {
            $this->name = 'Popcorn Hour';
        } else {
            $this->name = $_SERVER['HTTP_USER_AGENT'];
        }        
    }
}

function strbool($value)
{
    return $value ? 'true' : 'false';
}

function apiCall($path, $debug = false, $includeAPIKey = true)
{
    global $api_url, $api_key, $apiCallCount;

    $apiCallCount++;

    $url = $api_url . '/emby' . $path;
    if ($includeAPIKey) {
        $url .= "&api_key=" . $api_key;
    }
    if ($debug) {
        echo "<a href=\"" . $url . "\">url</a><br/>";
    }

    return json_decode(file_get_contents($url));
}

function apiCallPost($path, $post = null, $contentType = 'application/x-www-form-urlencoded')
{
    global $api_url;

    $authHeaderFormat = 'X-Emby-Authorization: MediaBrowser Client="%s", Version="%s", Device="%s", DeviceId="%s"';
    $tokenFormat = ', Token="%s"';

    $dev = new Device;

    $authHeader = sprintf($authHeaderFormat, CLIENTNAME, CLIENTVERSION, $dev->name, $dev->id);
    if ($_SESSION["accessToken"]) {
        //add token if exists to header
        $authHeader .= sprintf($tokenFormat, $_SESSION["accessToken"]);
    }

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: ' . $contentType . "\r\n" . $authHeader
        )
    );

    if ($post) {
        if ($contentType == 'application/x-www-form-urlencoded') 
        {
            $postdata = http_build_query($post);
            $opts['http']['content'] = $postdata;
        } else {
            $opts['http']['content'] = $post;
        }
    }    
    
    $context = stream_context_create($opts);

    $url = $api_url . '/emby' . $path;
    return json_decode(file_get_contents($url, false, $context));
}

function itemImageExists($itemId, $ImageType = ImageType::PRIMARY)
{
    if ($itemId != '') {
        $path =  ITEMSPATH . $itemId . "/Images/?";
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
    $all_episodes = getUsersItems(null, "Path", 1, $seriesId, null, null, ItemType::EPISODE, null, null, true);

    return $all_episodes->Items[0];
}

function firstSeasonFromSeries($seriesId)
{
    $seasons = getUsersItems(null, null, 1, $seriesId, null, null, ItemType::SEASON);

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

function addNonNullParameter($name, $value, $includeLeadingAmpersand = true, $urlencode = false)
{
    $amp = $includeLeadingAmpersand ? '&' : '';
    if ($urlencode) {
        $value = urlencode($value);
    }
    return $value ? $amp. $name . '=' . $value : '';
}

function getUsersItems($suffix = null, $fields = null, $limit = null, 
    $parentID = null, $parentIndexNumber = null, $sortBy = null, $type = null,
    $groupItems = null, $isPlayed = null, $Recursive = null, $startIndex = 0, $excludeItemTypes = null,
    $genres = null, $nameStartsWith = null, $ratings = null, $tags = null, $years = null, 
    $personIDs = null, $studioIDs = null)
{
    global $user_id;

    $path = "/Users/" . $user_id . ITEMSPATH . $suffix . "?";

    $path .= addNonNullParameter('Fields', $fields, false);
    $path .= addNonNullParameter('StartIndex', $startIndex);
    $path .= addNonNullParameter('Limit', $limit);
    $path .= addNonNullParameter('ParentID', $parentID);
    $path .= addNonNullParameter('ParentIndexNumber', $parentIndexNumber);
    $path .= addNonNullParameter('IncludeItemTypes', $type);
    $path .= addNonNullParameter('ExcludeItemTypes', $excludeItemTypes);
    $path .= addNonNullParameter('SortBy', $sortBy);
    $path .= addNonNullParameter('Genres', $genres, true, true);
    $path .= addNonNullParameter('NameStartsWith', $nameStartsWith, true, true);
    $path .= addNonNullParameter('OfficialRatings', $ratings);
    $path .= addNonNullParameter('Tags', $tags, true, true);
    $path .= addNonNullParameter('Years', $years);
    $path .= addNonNullParameter('PersonIDs', $personIDs);
    $path .= addNonNullParameter('StudioIDs', $studioIDs);
    $path .= !is_null($groupItems) ? "&GroupItems=" . strbool($groupItems) : "";
    $path .= !is_null($isPlayed) ? "&IsPlayed=" . strbool($isPlayed) : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . strbool($Recursive) : "";

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
    $path =  ITEMSPATH . $Id . "/Similar?UserID=" . $user_id;
    $path .= $limit ? "&Limit=" . $limit : "";
    return apiCall($path);
}

function getFilters($parentID = null, $type = null, $Recursive = null) {
    global $user_id;

    $path = ITEMSPATH . "Filters?UserID=" . $user_id;

    $path .= $parentID ? "&ParentID=" . $parentID : "";
    $path .= $type ? "&IncludeItemTypes=" . $type : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . strbool($Recursive) : "";
    
    return apiCall($path);
}

function getImageURL($id, $height = null, $width = null, $imageType = null, $unplayedCount = null, 
    $playedIndicator = false, $tag = null, $quality = null, $itemsOrUsers = null,
    $maxHeight = null, $maxWidth = null)
{
    global $api_url; 

    $itemsOrUsers = $itemsOrUsers ?? "Items";
    $imageType = $imageType ?? ImageType::PRIMARY;

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