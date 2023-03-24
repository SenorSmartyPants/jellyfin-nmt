<?php
include_once 'secrets.php';

ini_set("allow_url_fopen", true);

const OFFSETFROMCORNER = 24; // circle center offset

$id = htmlspecialchars($_GET['id']);
$imageType = htmlspecialchars($_GET['imageType']);
$height = intval($_GET['height']);
$width = intval($_GET['width']);
$unplayedCount = intval($_GET['unplayedCount']);
$mediaSourceCount = intval($_GET['mediaSourceCount']);
$AddPlayedIndicator = boolval($_GET['AddPlayedIndicator']);

//Set the Content Type
header('Content-type: image/png');

// TODO: progress bar
// TODO: Special Feature Indicator // same as Played check
// TODO: apache image caching

// get resized poster from JF
$image = imagecreatefromjpeg($api_url . "/Items/" . $id . "/Images/" . $imageType . "?height=" . $height . "&width=" . $width);

// Allocate A Color For The Text
$white = imagecolorallocate($image, 255, 255, 255);
// JF blue
$jf_blue = imagecolorallocate($image, 0x00, 0xA4, 0xDC);

if ($AddPlayedIndicator) {
    DrawPlayedIndicator($image, $white);
} else if ($unplayedCount > 0) {
    DrawUnplayedCountIndicator($image, $white, $unplayedCount);
}

if ($mediaSourceCount > 0) {
    DrawMediaSourceCountIndicator($image, $white, $mediaSourceCount);
}

// Send Image to Browser
imagepng($image);

// Clear Memory
imagedestroy($image);

function DrawCircle($image, $x, $y)
{
    global $jf_blue;
    // https://github.com/google/material-design-icons/blob/master/font/MaterialIcons-Regular.codepoints
    $font_path = 'fonts/MaterialIcons-Regular.ttf';
    $text = "\u{ef4a}"; // circle

    $font_size = 36;

    $x -= 24; //x and y are not circle center, but bounding box bottom left
    $y += 24;

    // draw the JF circle
    imagettftext($image, $font_size, 0, $x, $y, $jf_blue, $font_path, $text);
}

// x,y == center of circle
function DrawCountIndicator($image, $font_color, int $count, $x, $y)
{
    DrawCircle($image, $x, $y);

    // Set Path to Font File
    $font_path = 'fonts/NotoSans-Medium.ttf';

    // Set Text to Be Printed On Image
    $text = $count;
    $font_size = 19;
    $y += 9;

    if ($count < 10) {
        $x -= 7;
    } else if ($count < 100) {
        $x -= 14;
    } else {
        // 3 digits, decrease the font
        $x -= 19;
        $y -= 1;
        $font_size = 16;
    }
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}

function DrawUnplayedCountIndicator($image, $font_color, int $unplayedCount)
{
    $x = imagesx($image) - OFFSETFROMCORNER;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMCORNER);
}

function DrawMediaSourceCountIndicator($image, $font_color, int $unplayedCount)
{
    $x = OFFSETFROMCORNER;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMCORNER);
}

function DrawPlayedIndicator($image, $font_color)
{
    $x = imagesx($image) - OFFSETFROMCORNER;
    DrawCircle($image, $x, OFFSETFROMCORNER);

    // https://github.com/google/material-design-icons/blob/master/font/MaterialIcons-Regular.codepoints
    $font_path = 'fonts/MaterialIcons-Regular.ttf';
    $text = "\u{e5ca}"; // check
    $font_size = 24;
    $x -= 16;
    $y = OFFSETFROMCORNER + 16;
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}