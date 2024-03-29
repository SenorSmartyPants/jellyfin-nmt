<?php
$theme_css = "dark.css";

// list of users that want rewatching enabled in next up on NMT
// can't get JF-web setting for this.
$rewatchingUserIDs = array("userIDstring");

$collectiontypeNames = array(
    CollectionType::TVSHOWS => 'TV Shows', CollectionType::MOVIES => 'Movies', CollectionType::BOXSETS => 'Collections',
    CollectionType::PLAYLISTS => 'Playlists', CollectionType::MUSICVIDEOS => 'Music Videos'
);

//TVIDs
$tvid_page_index = 'HOME';
$tvid_page_categories = 'INFO';
$tvid_page_pgup = 'PGUP';
$tvid_page_pgdn = 'PGDN';
//to map a button to javascript:window.history.back();
$tvid_page_back = '';
$tvid_page_browse = 'YELLOW';

$tvid_filter_menu = 'TAB';

$tvid_season_play = 'PLAY';
$tvid_season_info = 'INFO';
$tvid_season_pgup = 'PGUP';
$tvid_season_pgdn = 'PGDN';
$tvid_season_itemdetails = 'RED';
$tvid_season_series = 'BLUE';

$tvid_itemdetails_play = 'PLAY';
$tvid_itemdetails_more = 'RED';
