<?php
include_once 'enums.php';
include_once 'secrets.php';

const ITEMSPATH = '/Items/';
const USERSPATH = '/Users/';

$apiCallCount = 0;

const CLIENTNAME = 'Jellyfin-NMT';
const CLIENTVERSION = '0.3.0';
const POSTCONTENTTYPE = 'application/json';

class Device
{
    private const HTTP_USER_AGENT = 'HTTP_USER_AGENT';
    public $name = 'My testbed class';
    public $id = 1;

    function __construct() {
        $this->id = $_SERVER['REMOTE_ADDR'];

        if (stripos($_SERVER[self::HTTP_USER_AGENT],"Chrome")!==false) {
            $this->name = 'Chrome';
        } else if (stripos($_SERVER[self::HTTP_USER_AGENT],"Syabas")!==false) {
            $this->name = 'Popcorn Hour';
        } else {
            $this->name = $_SERVER[self::HTTP_USER_AGENT];
        }        
    }
}

function mapItemTypeToCollectionType($itemType)
{
    $itemTypeToCollectionType = array(ItemType::SERIES => CollectionType::TVSHOWS, 
        ItemType::SEASON => CollectionType::TVSHOWS, ItemType::EPISODE => CollectionType::TVSHOWS,
        ItemType::MOVIE => CollectionType::MOVIES, ItemType::BOXSET => CollectionType::BOXSETS,
        ItemType::PLAYLIST => CollectionType::PLAYLISTS, ItemType::MUSICVIDEO  => CollectionType::MUSICVIDEOS);

    return $itemTypeToCollectionType[$itemType];
}

function mapFolderTypeToSingleItemType($folderType, $collectionType)
{
    $collectionTypeToItemType = array(CollectionType::TVSHOWS => ItemType::SERIES, 
        CollectionType::MOVIES => ItemType::MOVIE, CollectionType::BOXSETS => ItemType::BOXSET,
        CollectionType::PLAYLISTS => ItemType::PLAYLIST, CollectionType::MUSICVIDEOS => ItemType::MUSICVIDEO);

    //folders are itemtypes
    if ($folderType == ItemType::COLLECTIONFOLDER || $folderType == ItemType::USERVIEW) {
        return $collectionTypeToItemType[$collectionType];
    } else {
        return $folderType;
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

    $url = $api_url . $path;
    if ($includeAPIKey) {
        $url .= "&api_key=" . $api_key;
    }
    if ($debug) {
        echo "<a href=\"" . $url . "\">url</a><br/>";
    }

    return json_decode(file_get_contents($url));
}

//JF 10.7.0+ only supports JSON post calls
function apiCallPost($path, $post = null)
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
            'header'  => 'Content-Type: ' . POSTCONTENTTYPE . "\r\n" . $authHeader
        )
    );

    if ($post) {
        $opts['http']['content'] = json_encode($post);
    }    
    
    $context = stream_context_create($opts);

    $url = $api_url . $path;
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

function getUsersItems($suffix = null, $fields = null, $limit = null, 
    $parentID = null, $parentIndexNumber = null, $sortBy = null, $type = null,
    $groupItems = null, $isPlayed = null, $Recursive = null, $startIndex = 0, $excludeItemTypes = null,
    $genres = null, $nameStartsWith = null, $ratings = null, $tags = null, $years = null, 
    $personIDs = null, $studioIDs = null)
{
    global $user_id;

    $path = USERSPATH . $user_id . ITEMSPATH . $suffix . '?';

    $params = http_build_query(['Fields' => $fields,
        'StartIndex' => $startIndex ?: null,
        'Limit' => $limit ?: null,
        'ParentID' => $parentID ?: null,
        'ParentIndexNumber' => $parentIndexNumber ?: null,
        'IncludeItemTypes' => $type ?: null,
        'ExcludeItemTypes' => $excludeItemTypes ?: null,
        'SortBy' => $sortBy ?: null,
        'Genres' => $genres ?: null,
        'NameStartsWith' => $nameStartsWith ?: null,
        'OfficialRatings' => $ratings ?: null,
        'Tags' => $tags ?: null,
        'Years' => $years ?: null,
        'PersonIDs' => $personIDs ?: null,
        'StudioIDs' => $studioIDs ?: null,
        'GroupItems' => !is_null($groupItems) ? strbool($groupItems) : null,
        'IsPlayed' => !is_null($isPlayed) ? strbool($isPlayed) : null,
        'Recursive' => !is_null($Recursive) ? strbool($Recursive) : null]);

    return apiCall($path . $params);
}

function getUsersViews()
{
    global $user_id;

    $path = USERSPATH . $user_id . "/Views/?IncludeExternalContent=false";
    return apiCall($path);
}


function getUsersPublic()
{
    $path = USERSPATH . 'Public';
    return apiCall($path, false, false);
}

function getLatest($itemType, $Limit)
{
    global $GroupItems;

    return getUsersItems("Latest", "Path", $Limit, null, null, null, $itemType, $GroupItems);
}

function getNextUp($Limit, $startIndex = 0)
{
    global $user_id;

    $path = "/Shows/NextUp?UserID=" . $user_id .
        "&Fields=Path&Limit=" . $Limit . "&StartIndex=" . $startIndex;

    return apiCall($path);
}

function getItems($parentID, $StartIndex, $Limit, $type = null, $recursive = null, 
    $genres = null, $nameStartsWith = null, $ratings = null, $tags = null, $years = null, 
    $personIDs = null, $studioIDs = null, $sortBy = 'SortName', $excludeItemTypes = null)
{
    return getUsersItems(null, "Path,ChildCount", $Limit, $parentID, null, $sortBy, $type, 
        null, null, $recursive, $StartIndex, $excludeItemTypes, 
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

function getFilters($parentID = null, $itemTypes = null, $Recursive = null) {
    global $user_id;

    $path = ITEMSPATH . "Filters?UserID=" . $user_id;

    $path .= $parentID ? "&ParentID=" . $parentID : "";
    $path .= $itemTypes ? "&IncludeItemTypes=" . implode(",", $itemTypes) : "";
    $path .= !is_null($Recursive) ? "&Recursive=" . strbool($Recursive) : "";
    
    return apiCall($path);
}

function getImageURL($id, $height = null, $width = null, $imageType = null, $unplayedCount = null, 
    $AddPlayedIndicator = null, $tag = null, $quality = null, $itemsOrUsers = null,
    $maxHeight = null, $maxWidth = null)
{
    global $api_url; 

    $itemsOrUsers = $itemsOrUsers ?? 'Items';
    $imageType = $imageType ?? ImageType::PRIMARY;

    $AddPlayedIndicator = ($AddPlayedIndicator ? 'true' : null);
   
    return $api_url . "/" . $itemsOrUsers . "/" . $id . "/Images/" . $imageType . "?" 
        . http_build_query(compact('height', 'width', 'maxHeight', 'maxWidth', 'quality', 'tag', 'unplayedCount', 'AddPlayedIndicator'));
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