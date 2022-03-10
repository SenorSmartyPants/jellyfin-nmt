<?
abstract class ImageType
{
    const PRIMARY = 'Primary';
    const BANNER = 'Banner';
    const THUMB = 'Thumb';
    const LOGO = 'Logo';
    const BACKDROP = 'Backdrop';
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
    const COLLECTIONFOLDER = 'CollectionFolder';
    const PLAYLIST = 'Playlist';
    const USERVIEW = 'UserView';
    const MUSICVIDEO = 'MusicVideo';
}

abstract class CollectionType
{
    const MOVIES = 'movies';
    const TVSHOWS = 'tvshows';
    const MUSIC = 'music';
    const MUSICVIDEOS = 'musicvideos';
    const HOMEVIDEOS = 'homevideos';
    const BOXSETS = 'boxsets';
    const FOLDERS = 'folders';
    const PLAYLISTS = 'playlists';
}

abstract class ExtrasType
{
    const SPECIALFEATURES = 'SpecialFeatures';
    const LOCALTRAILERS = 'LocalTrailers';
    const INTROS = 'Intros';
    const ADDITIONALPARTS = 'AdditionalParts';
}

$prettySpecialFeatures = [
    'Clip' => 'Featurette',
    'Trailer' => 'Trailer',
    'BehindTheScenes' => 'Behind the Scenes',
    'DeletedScene' => 'Deleted Scene',
    'Interview' => 'Interview',
    'Scene' => 'Scene',
    'Sample' => 'Sample',
    'ThemeSong' => 'Theme Song',
    'ThemeVideo' => 'Theme Video'
];
?>