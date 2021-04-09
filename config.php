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
?>