# Jellyfin-NMT

![Next Up Screen shot](../assets/NextUp.png)

## Running YAMJ is no longer necessary!

Jellyfin-NMT uses the Jellyfin API to generate HTML compatible with the Popcorn hour Network Media Tank (NMT) player. The HTML generated is based on [YAMJ 2.0](https://github.com/YAMJ/yamj-v2) and its' skins, specifically [SabishGT](http://www.gt-projects.net/news.php).

Videos are not played through Jellyfin and so are not marked as watched. I use a trakt proxy to mark videos as watched. JF then syncs with trakt. This is disabled by default, but can be changed with the CHECKIN constant.

## Setup

- Create an API key in Jellyfin
- Look up the id of the user you will use at http://localhost:8096/emby/Users/?api_key=APIKEY
- Create a secrets.php file based on this template, save it in the root of the application folder.

```php
<?php
//url to YAMJ generated files
$jukebox_url = "http://192.168.1.1/New/Jukebox/";

$api_url = "http://localhost:8096";
$api_key = "APIKEY";

$user_id = "USERID";

?>
```

- Update secrets.php with APIKEY and USERID.
- Browse to home.php to start. Use that page as a starting point on your NMT.

## Screenshots

### YAMJ TV Season. Push RED button on remote to go to Item Details Season
![Default YAMJ style TV Season Page](../assets/Season_YAMJ.png)

### ItemDetails for TV Season
![ItemDetails style TV Season Page](../assets/Season.png)

### TV Series with seasons. Press RED to cycle thru different subitems
![ItemDetails style TV Series Page](../assets/Series.png)

### TV Series with people.
![ItemDetails style TV Series Page - people](../assets/Series_people.png)

### TV Series with more like this.
![ItemDetails style TV Series Page - more like this](../assets/Series_more.png)

### TV Episode details.
![ItemDetails style TV Episode Page](../assets/Episode.png)

### Movie.
![ItemDetails style Movie Page](../assets/Movie.png)

### Actor.
![ItemDetails style Actor Page](../assets/Actor.png)


## CSS Stylesheet Notes
- [Limited CSS support](http://files.syabas.com/networkedmediatank/www.networkedmediatank.com/download/docs/NMT_stylesheet_20080118.htm)
- [NMT development WIKI](http://www.networkedmediatank.com/wiki/index.php/Main_Page)
- Applying multiple classes to style doesn't work on NMT
    ```css
    .abc, .xyz { margin-left: 20px; }
    ```
- Inheritance seems weird, or just doesn't work.
  - Color from body was not inheriting down to TD elements. Must set for more specific tags.
  - Redefining an attribute (like color) for the same item does not work. First value is kept.
    - example: color will be #8e8e8e.
    ```css
        .indexname { color: #8e8e8e; }
        .indexname { color: #dddddd; }
    ```


