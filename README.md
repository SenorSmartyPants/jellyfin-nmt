# Jellyfin-NMT

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=SenorSmartyPants_jellyfin-nmt&metric=alert_status)](https://sonarcloud.io/dashboard?id=SenorSmartyPants_jellyfin-nmt)

![Next Up Screen shot](../assets/NextUp.png)

## Running YAMJ is no longer necessary!

Jellyfin-NMT uses the Jellyfin API to generate HTML compatible with the Popcorn hour Network Media Tank (NMT) player. The HTML generated is based on [YAMJ 2.0](https://github.com/YAMJ/yamj-v2) and its' skins, specifically [SabishGT](http://www.gt-projects.net/news.php).

Videos are direct played via mounted filesystem on NMT. Playback status is reported to JF. Start and Stop events are reported to JF. There's no way that I'm aware of to capture seeks or pauses on A-100 and sync those to JF.

## Setup

- Create an API key in Jellyfin
- Create a secrets.php file based on this template, save it in the root of the application folder.

```php
<?php
$api_url = "http://localhost:8096";
$api_key = "APIKEY";

//NMT player path
$NMT_path = "/storage/media/Videos/"; //server based path to share to NMT
$NMT_playerpath = "file:///opt/sybhttpd/localhost.drives/NETWORK_SHARE/storage/media/Videos/";  //NMT path to the share

?>
```

- Update secrets.php with APIKEY.
- Update secrets.php $api_url if you are running Jellyfin on a different machine than the one you are running JF-NMT
- put all files in a directory served by a webserver with php
- Browse to index.php to start. Use that page as a starting point on your NMT.

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


