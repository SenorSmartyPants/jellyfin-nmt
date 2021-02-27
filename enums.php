<?
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

abstract class CollectionType
{
    const Movies = 'movies';
    const TvShows = 'tvshows';
    const Music = 'music';
    const MusicVideos = 'musicvideos';
    //const Trailers = 'trailers';
    const HomeVideos = 'homevideos';
    const BoxSets = 'boxsets';
    //const Books = 'books';
    //const Photos = 'photos';
    //const LiveTv = 'livetv';
    //const Playlists = 'playlists';
    const Folders = 'folders';
}
?>