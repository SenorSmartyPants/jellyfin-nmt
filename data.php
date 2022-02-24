<?php
include_once 'enums.php';
include_once 'secrets.php';

const ITEMSPATH = '/Items/';
const USERSPATH = '/Users/';
const VIDEOSPATH = '/Videos/';

$apiCallCount = 0;

const CLIENTNAME = 'Jellyfin-NMT';
const CLIENTVERSION = '0.4.5';
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

class UserItemsParams
{
    public $Fields = null;
    public $Genres = null;
    public $GroupItems = null;
    public $ExcludeItemTypes = null;
    public $IncludeItemTypes = null;
    public $IsFavorite = null;
    public $IsPlayed = null;
    public $Limit = null;
    public $NameStartsWith = null;
    public $OfficialRatings = null;
    public $ParentID = null;
    public $ParentIndexNumber = null;    
    public $PersonIDs = null;
    public $Recursive = null;
    public $SortBy = null;
    public $StartIndex = null;
    public $StudioIDs = null;
    public $Tags = null;
    public $Years = null;
}

class ImageParams
{
    public $height = null;
    public $width = null;
    public $maxHeight = null;
    public $maxWidth = null;
    public $quality = null;
    public $tag = null;
    public $unplayedCount = null;
    public $AddPlayedIndicator = null;

    function __construct($height = null, $width = null, $tag = null) {
        $this->height = $height;
        $this->width = $width;
        $this->tag = $tag;     
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

function strboolNull($value)
{
    if (is_null($value)) {
        return null;
    } else {
        return strbool($value);
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
    if ($debug || isset($_GET['debug'])) {
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
    $params = new UserItemsParams();
    $params->Fields = 'Path';
    $params->Limit = 1;
    $params->ParentID = $seasonId;
    $params->ParentIndexNumber = $seasonNumber;

    $all_episodes = getUsersItems($params);

    //return first
    return $all_episodes->Items[0];
}

function firstEpisodeFromSeries($seriesId)
{
    $params = new UserItemsParams();
    $params->Fields = 'Path';
    $params->Limit = 1;
    $params->ParentID = $seriesId;
    $params->IncludeItemTypes = ItemType::EPISODE;
    $params->Recursive = true;

    $all_episodes = getUsersItems($params);

    return $all_episodes->Items[0];
}

function firstSeasonFromSeries($seriesId)
{
    $params = new UserItemsParams();
    $params->Limit = 1;
    $params->ParentID = $seriesId;
    $params->IncludeItemTypes = ItemType::SEASON;

    $seasons = getUsersItems($params);

    return $seasons->Items[0];
}

function latestSeasonFromSeries($seriesId)
{
    $params = new UserItemsParams();
    $params->ParentID = $seriesId;
    $params->IncludeItemTypes = ItemType::SEASON;

    $seasons = getUsersItems($params);

    return $seasons->Items[$seasons->TotalRecordCount-1];
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

function getUsersItems(UserItemsParams $params, $suffix = null)
{
    global $user_id;

    $path = USERSPATH . $user_id . ITEMSPATH . $suffix . '?';

    $params->GroupItems = strboolNull($params->GroupItems);
    $params->IsFavorite = strboolNull($params->IsFavorite);
    $params->IsPlayed = strboolNull($params->IsPlayed);
    $params->Recursive = strboolNull($params->Recursive);

    $querystring = http_build_query($params);

    return apiCall($path . $querystring);
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

    $params = new UserItemsParams();
    $params->Fields = 'Path';
    $params->Limit = $Limit;
    $params->IncludeItemTypes = $itemType;
    $params->GroupItems = $GroupItems;    

    return getUsersItems($params, 'Latest');
}

function getResume($Limit, $StartIndex = 0)
{
    $params = new UserItemsParams();
    $params->Fields = 'Path';
    $params->Limit = $Limit;
    $params->StartIndex = $StartIndex;

    return getUsersItems($params, 'Resume');
}

function getNextUp($Limit, $startIndex = 0, $rewatching = null)
{
    global $user_id;

    $params = array(
        'UserID' => $user_id,
        'Fields' => 'Path',
        'Limit' => $Limit,
        'StartIndex' => $startIndex,
        'Rewatching' => $rewatching
    );

    $path = "/Shows/NextUp?" . http_build_query($params);

    return apiCall($path);
}

function getItems(UserItemsParams $params)
{
    //set defaults
    $params->Fields = $params->Fields ?? 'Path,ChildCount';

    return getUsersItems($params);
}

function getItem($Id) {
    $params = new UserItemsParams();
    return getUsersItems($params, $Id);
}

function getItemExtras($Id, $ExtrasType)
{
    global $user_id;

    $params = new UserItemsParams();
    if ($ExtrasType == ExtrasType::ADDITIONALPARTS) {
        $path = VIDEOSPATH . $Id . '/' . ExtrasType::ADDITIONALPARTS . '?UserID=' . $user_id;
        //returns an Items block with counts and index
        //just return Items so it's the same as other calls
        return apiCall($path)->Items;
    } else {
        return getUsersItems($params, $Id . '/' . $ExtrasType);
    }
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

function getImageURL($id, ImageParams $imageProperties, $imageType = null, $itemsOrUsers = null)
{
    global $api_url; 

    $itemsOrUsers = $itemsOrUsers ?? 'Items';
    $imageType = $imageType ?? ImageType::PRIMARY;

    $imageProperties->AddPlayedIndicator = ($imageProperties->AddPlayedIndicator ? 'true' : null);
   
    return $api_url . "/" . $itemsOrUsers . "/" . $id . "/Images/" . $imageType . "?" 
        . http_build_query($imageProperties);
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