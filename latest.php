<?php

include 'secrets.php';

$GroupItems = "true";
$Limit = 21;

function seasonPosterExists($seasonId){
    global $api_url, $user_id, $api_key;

    $url = $api_url . "/Items/" . $seasonId . "/Images/?api_key=" . $api_key;

    //seasons usually have a Primary or nothing
    $contents = file_get_contents($url);
    $images = json_decode($contents);

    return (count($images)>0);
}

function firstEpisodeFromSeason($seasonId){
    global $api_url, $user_id, $api_key;

    //all episodes from unwatched season, no season data
    $url = $api_url . "/Users/" . $user_id .
        "/Items/?ParentID=" . $seasonId . "&api_key=" . $api_key;
    
    $contents = file_get_contents($url);
    $all_episodes = json_decode($contents);

    //return first
    return $all_episodes->Items[0];
}

function parseSeries($item) {
    global $api_url, $user_id, $api_key, $jukebox_url;

    //gets unwatched episodes for this series
    $url = $api_url . "/Users/" . $user_id .
        "/Items/Latest?ParentID=" . $item->Id . "&GroupItems=false" . 
        "&api_key=" . $api_key;

    $contents = file_get_contents($url);
    $unwatched = json_decode($contents);

    $first_unwatched = $unwatched[count($unwatched)-1];

    $menuItem = parseEpisode($first_unwatched);
    $menuItem->UnplayedCount = $item->UserData->UnplayedItemCount; 

    return $menuItem;
}

function parseEpisode($item) {
    //find first episode in season, this will be YAMJ filename
    $first_from_season = firstEpisodeFromSeason($item->SeasonId);   

    $menuItem = new stdClass();
    $menuItem->Name = $first_from_season->SeriesName . ' ' . $first_from_season->SeasonName;
    $menuItem->DetailBaseURL = pathinfo($first_from_season->Path)['filename'] . ".html";
    $menuItem->PosterID = (seasonPosterExists($first_from_season->SeasonId)) ? $first_from_season->SeasonId : $first_from_season->SeriesId;
    //or 1 if I want it to show up
    $menuItem->UnplayedCount = null;

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

function printMenuItem($menuItem){
    global $jukebox_url;
    ?>
    <a href="<?=$jukebox_url . $menuItem->DetailBaseURL ?>">
    <img src="http://nanners:8196/emby/Items/<?=$menuItem->PosterID?>/Images/Primary?UnplayedCount=<?=$menuItem->UnplayedCount?>&maxHeight=237&maxWidth=160" /></a><br/>
    <b><?= $menuItem->Name ?></b><br/>
    <?php
}

$url = $api_url . "/Users/" . $user_id .
    "/Items/Latest?&GroupItems=" . $GroupItems . 
    "&Limit=" . $Limit .
    "&api_key=" . $api_key;

$contents = file_get_contents($url);
$latest=json_decode($contents);

foreach($latest as $item) {

    if ($item->IsFolder ) {
        //episode folder is series, not season
        //$posterItemID = printSeries($item);
        printMenuItem(parseSeries($item));
    } elseif ($item->Type =='Movie') {
        printMenuItem(parseMovie($item));
    } else {
        //is episode
        printMenuItem(parseEpisode($item));
    }
}

?>