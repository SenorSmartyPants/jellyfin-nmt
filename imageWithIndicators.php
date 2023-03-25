<?php
include_once 'secrets.php';

ini_set("allow_url_fopen", true);

const OFFSETFROMCORNER = 24; // circle center offset
const INDICATORWIDTH = 44;

// https://github.com/google/material-design-icons/blob/master/font/MaterialIcons-Regular.codepoints
const ICONFONT = 'fonts/MaterialIcons-Regular.ttf';


$leftCornerOffset = OFFSETFROMCORNER;

$id = htmlspecialchars($_GET['id']);
$imageType = htmlspecialchars($_GET['imageType']);
$height = intval($_GET['height']);
$width = intval($_GET['width']);
$unplayedCount = intval($_GET['unplayedCount']);
$percentPlayed = intval($_GET['percentPlayed']);
$mediaSourceCount = intval($_GET['mediaSourceCount']);
$specialFeatureCount = intval($_GET['specialFeatureCount']);
$AddPlayedIndicator = boolval($_GET['AddPlayedIndicator']);

//Set the Content Type
header('Content-type: image/png');

// TODO: apache image caching

// get resized poster from JF
$image = imagecreatefromstring(file_get_contents($api_url . "/Items/" . $id . "/Images/" . $imageType . "?height=" . $height . "&width=" . $width));

// Allocate A Color For The Text
$white = imagecolorallocate($image, 255, 255, 255);
// JF blue
$jf_blue = imagecolorallocate($image, 0x00, 0xA4, 0xDC);

if ($AddPlayedIndicator) {
    DrawPlayedIndicator($image, $white);
} elseif ($unplayedCount > 0) {
    DrawUnplayedCountIndicator($image, $white, $unplayedCount);
}

if ($mediaSourceCount > 0) {
    DrawMediaSourceCountIndicator($image, $white, $mediaSourceCount);
}

if ($specialFeatureCount > 0) {
    DrawSpecialFeatureIndicator($image, $white, false);
}

if ($percentPlayed) {
    DrawPercentPlayed($image, $percentPlayed);
}

// Send Image to Browser
imagepng($image);

// Clear Memory
imagedestroy($image);

function DrawCircle($image, $x, $y)
{
    global $jf_blue;

    $text = "\u{ef4a}"; // circle

    $font_size = 36;

    $x -= 24; //x and y are not circle center, but bounding box bottom left
    $y += 24;

    // draw the JF circle
    imagettftext($image, $font_size, 0, $x, $y, $jf_blue, ICONFONT, $text);
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
    } elseif ($count < 100) {
        $x -= 14;
    } else {
        // 3 digits, decrease the font
        $x -= 19;
        $y -= 1;
        $font_size = 16;
    }
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}

// right side
function DrawUnplayedCountIndicator($image, $font_color, int $unplayedCount)
{
    $x = imagesx($image) - OFFSETFROMCORNER;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMCORNER);
}

function DrawPlayedIndicator($image, $font_color)
{
    $x = imagesx($image) - OFFSETFROMCORNER;
    DrawCircle($image, $x, OFFSETFROMCORNER);

    $text = "\u{e5ca}"; // check
    $font_size = 24;
    $x -= 16;
    $y = OFFSETFROMCORNER + 16;
    imagettftext($image, $font_size, 0, $x, $y, $font_color, ICONFONT, $text);
}

// left side
function DrawMediaSourceCountIndicator($image, $font_color, int $unplayedCount)
{
    global $leftCornerOffset;
    $x = $leftCornerOffset;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, OFFSETFROMCORNER);
    // move the offset over incase we have more indicators for the left side
    $leftCornerOffset += INDICATORWIDTH;
}

function DrawSpecialFeatureIndicator($image, $font_color, $draw_circle = true)
{
    global $leftCornerOffset, $jf_blue;
    $x = $leftCornerOffset;
    if ($draw_circle) {
        DrawCircle($image, $x, OFFSETFROMCORNER);
        $x -= 16;
    } else {
        $font_color = $jf_blue;
        $x -= 16;
    }

    $text = "\u{e02c}"; // movie
    $font_size = 24;

    $y = OFFSETFROMCORNER + 16;
    imagettftext($image, $font_size, 0, $x, $y, $font_color, ICONFONT, $text);
    $leftCornerOffset += INDICATORWIDTH;
}

// bottom
function DrawPercentPlayed($image, $percent)
{
    global $jf_blue, $height, $width;

    $indicatorHeight = 8;

    $endX = $width - 1;
    $endY = $height - 1;

    // draw transparent background
    $transblack = imagecolorallocatealpha($image, 0, 0, 0, 48);
    imagefilledrectangle($image, 0, $endY - $indicatorHeight, $endX, $endY, $transblack);

    $foregroundWidth = ($endX * $percent) / 100;
    imagefilledrectangle($image, 0, $endY - $indicatorHeight, $foregroundWidth, $endY, $jf_blue);
}
