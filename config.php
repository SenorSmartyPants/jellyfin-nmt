<?php
include_once 'enums.php';

date_default_timezone_set('America/Chicago');

$theme_css = "dark.css";

//LOCALIZATION!!!
//Collection type names. Users can localize for non-english
//only used on page title for filtered views.
//example 'TV Shows - M'
$collectiontypeNames = array(CollectionType::TVSHOWS=>'TV Shows', CollectionType::MOVIES=>'Movies', CollectionType::BOXSETS=>'Collections',
    CollectionType::PLAYLISTS=>'Playlists', CollectionType::MUSICVIDEOS=>'Music Videos');



//Default TVIDs
$tvid_page_index = 'HOME';
$tvid_page_categories = 'INFO';
$tvid_page_pgup = 'PGUP';
$tvid_page_pgdn = 'PGDN';
//to map a button to javascript:window.history.back();
$tvid_page_back = '';

$tvid_filter_menu = 'TAB';

$tvid_season_play = 'PLAY';
$tvid_season_info = 'INFO';
$tvid_season_pgup = 'PGUP';
$tvid_season_pgdn = 'PGDN';
$tvid_season_itemdetails = 'RED';

$tvid_itemdetails_play = 'PLAY';
$tvid_itemdetails_more = 'RED';


if(file_exists('config_local.php')){
    // Include the file
    include('config_local.php');
}
?>