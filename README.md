# Jellyfin-NMT

![Next Up Screen shot](../assets/NextUp.png)

This is a work in progress.

Jellyfin-NMT uses the Jellyfin API to generate HTML compatible with the Popcorn hour Network Media Tank (NMT) player. The HTML generated is based on [YAMJ 2.0](https://github.com/YAMJ/yamj-v2) and its' skins, specifically [SabishGT](http://www.gt-projects.net/news.php).

Currently only a few index pages have been implemented, so you still need to generate your YAMJ jukebox like you have been doing for years. My eventual goal is to completely replace the YAMJ generated pages with JS API based ones.

Videos are not played through Jellyfin and so are not marked as watched. I use a modified YAMJ skin and a trakt proxy to mark videos as watched. JF then syncs with trakt.

## Setup

- Create an API key in Jellyfin
- Look up the id of the user you will use at http://localhost:8096/emby/Users/?api_key=APIKEY
- Create a secrets.php file based on this template, save it in the root of the application folder.

```php
<?php
//url to YAMJ generated files
$jukebox_url = "http://192.168.1.1/New/Jukebox/";

$api_url = "http://localhost:8096/emby";
$api_key = "APIKEY";

$user_id = "USERID";

?>
```

- Update secrets.php with APIKEY and USERID.
- Browse to home.php to start. Use that page as a starting point on your NMT.



## CSS Stylesheet Notes
- [Limited CSS support](http://files.syabas.com/networkedmediatank/www.networkedmediatank.com/download/docs/NMT_stylesheet_20080118.htm)
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


