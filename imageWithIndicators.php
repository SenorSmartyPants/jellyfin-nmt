<?php
include_once 'secrets.php';

ini_set("allow_url_fopen", true);

//const OFFSETFROMTOPRIGHTCORNER = 25; // circle center offset
const OFFSETFROMTOPRIGHTCORNER = 38; // circle center offset

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
//global $api_url;
$image = imagecreatefromjpeg($api_url . "/Items/" . $id . "/Images/" . $imageType . "?height=" . $height . "&width=" . $width);
//imagesavealpha($image, true);

// Allocate A Color For The Text
$white = imagecolorallocate($image, 255, 255, 255);

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
    // choose a color for the ellipse - JF blue = CC00A4DC
    $col_ellipse = imagecolorallocatealpha($image, 0x00, 0xA4, 0xDC, 25);

    // draw the JF circle
    imagefilledellipse($image, $x, $y, 40, 40, $col_ellipse);
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
    $y += 10;

    if ($count < 10) {
        $x -= 6;
    } else if ($count < 100) {
        $x -= 13;
    } else {
        // 3 digits, decrease the font
        $x -= 18;
        $y -= 1;
        $font_size = 16;
    }
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}

function DrawUnplayedCountIndicator($image, $font_color, int $unplayedCount)
{
    $x = imagesx($image) - OFFSETFROMTOPRIGHTCORNER;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMTOPRIGHTCORNER);
}

function DrawMediaSourceCountIndicator($image, $font_color, int $unplayedCount)
{
    $x = OFFSETFROMTOPRIGHTCORNER;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMTOPRIGHTCORNER);
}

function DrawPlayedIndicator($image, $font_color)
{
    $x = imagesx($image) - OFFSETFROMTOPRIGHTCORNER;
    DrawCircle($image, $x, OFFSETFROMTOPRIGHTCORNER);

    $font_path = 'fonts/DejaVuSans.ttf';
    $text = "\u{2714}"; // heavy checkmark
    $font_size = 26;
    $x -= 13;
    $y = OFFSETFROMTOPRIGHTCORNER + 13;
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}