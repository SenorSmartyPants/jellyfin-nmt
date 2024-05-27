<?php
include_once 'secrets.php';

ini_set("allow_url_fopen", true);

const INDICATORWIDTH = [22, 33, 44];

const CIRCLEOFFSET = [12, 18, 24]; // circle center offset
const CIRCLEFONTSIZE = [18, 27, 36];

const CHECKFONTSIZE = [12, 18, 24];
const CHECKOFFSET = [8, 12, 16];

const COUNTFONTSIZE = [10, 14, 19];
const COUNTXOFFSET = [4, 5.5, 7];
const COUNTXOFFSET3DIGITTWEAK = [0, 1, 5];
const COUNTYOFFSET = [5, 7, 9];
const COUNTYOFFSET3DIGITTWEAK = [1, 2, 1];

const PLAYEDPERCENTHEIGHT = [2, 4, 8];

const SIZESMALL = 0;
const SIZEMEDIUM = 1;
const SIZELARGE = 2;

// https://github.com/google/material-design-icons/blob/master/font/MaterialIcons-Regular.codepoints
const ICONFONT = 'fonts/MaterialIcons-Regular.ttf';


$id = htmlspecialchars($_GET['id']);
$imageType = htmlspecialchars($_GET['imageType']);
$height = intval($_GET['height']);
$width = intval($_GET['width']);
$unplayedCount = intval($_GET['unplayedCount']);
$percentPlayed = intval($_GET['percentPlayed']);
$mediaSourceCount = intval($_GET['mediaSourceCount']);
$specialFeatureCount = intval($_GET['specialFeatureCount']);
$AddPlayedIndicator = boolval($_GET['AddPlayedIndicator']);
$indicatorSize = intval($_GET['indicatorSize']);

$leftCornerOffset = CIRCLEOFFSET[$indicatorSize];

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

// send caching headers
// caching won't survive NMT reboot, if NMT caches at all
header('Pragma: public');
header('Cache-Control: max-age=86400');
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
header('Content-type: image/png');
// Send Image to Browser
imagepng($image);

// Clear Memory
imagedestroy($image);

// x,y == center of circle
function DrawCircle($image, $x, $y)
{
    global $jf_blue, $indicatorSize;

    $text = "\u{ef4a}"; // circle

    $font_size = CIRCLEFONTSIZE[$indicatorSize];

    $x -= CIRCLEOFFSET[$indicatorSize]; //x and y are not circle center, but bounding box bottom left
    $y += CIRCLEOFFSET[$indicatorSize];

    // draw the JF circle
    imagettftext($image, $font_size, 0, $x, $y, $jf_blue, ICONFONT, $text);
}

// x,y == center of circle
function DrawCountIndicator($image, $font_color, int $count, $x, $y)
{
    global $indicatorSize;
    DrawCircle($image, $x, $y);

    // Set Path to Font File
    $font_path = 'fonts/NotoSans-Medium.ttf';

    // Set Text to Be Printed On Image
    $text = $count;
    $font_size = COUNTFONTSIZE[$indicatorSize];
    $y += COUNTYOFFSET[$indicatorSize];

    if ($count < 10) {
        $x -= COUNTXOFFSET[$indicatorSize];
    } elseif ($count < 100) {
        $x -= COUNTXOFFSET[$indicatorSize] * 2;
    } else {
        // 3 digits, decrease the font
        $x -= COUNTXOFFSET[$indicatorSize] * 2 + COUNTXOFFSET3DIGITTWEAK[$indicatorSize];
        $y -= COUNTYOFFSET3DIGITTWEAK[$indicatorSize];
        $font_size = COUNTFONTSIZE[$indicatorSize] - 3;
    }
    imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_path, $text);
}

// right side
function DrawUnplayedCountIndicator($image, $font_color, int $unplayedCount)
{
    global $indicatorSize;
    $x = imagesx($image) - CIRCLEOFFSET[$indicatorSize];
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, CIRCLEOFFSET[$indicatorSize]);
}

function DrawPlayedIndicator($image, $font_color)
{
    global $indicatorSize;
    $x = imagesx($image) - CIRCLEOFFSET[$indicatorSize];
    DrawCircle($image, $x, CIRCLEOFFSET[$indicatorSize]);

    $text = "\u{e5ca}"; // check
    $font_size = CHECKFONTSIZE[$indicatorSize];
    $x -= CHECKOFFSET[$indicatorSize];
    $y = CIRCLEOFFSET[$indicatorSize] + CHECKOFFSET[$indicatorSize];
    imagettftext($image, $font_size, 0, $x, $y, $font_color, ICONFONT, $text);
}

// left side
function DrawMediaSourceCountIndicator($image, $font_color, int $unplayedCount)
{
    global $leftCornerOffset, $indicatorSize;
    $x = $leftCornerOffset;
    DrawCountIndicator($image, $font_color, $unplayedCount, $x, CIRCLEOFFSET[$indicatorSize]);
    // move the offset over incase we have more indicators for the left side
    $leftCornerOffset += INDICATORWIDTH[$indicatorSize];
}

function DrawSpecialFeatureIndicator($image, $font_color, $draw_circle = true)
{
    global $leftCornerOffset, $jf_blue, $indicatorSize;
    $x = $leftCornerOffset - CHECKOFFSET[$indicatorSize];
    if ($draw_circle) {
        DrawCircle($image, $x, CIRCLEOFFSET[$indicatorSize]);
    } else {
        $font_color = $jf_blue;
    }

    $text = "\u{e02c}"; // movie
    $font_size = CHECKFONTSIZE[$indicatorSize];

    $y = CIRCLEOFFSET[$indicatorSize] + CHECKOFFSET[$indicatorSize];
    imagettftext($image, $font_size, 0, $x, $y, $font_color, ICONFONT, $text);
    $leftCornerOffset += INDICATORWIDTH[$indicatorSize];
}

// bottom
function DrawPercentPlayed($image, $percent)
{
    global $jf_blue, $height, $width, $indicatorSize;

    if (0 < $percent && $percent < 100) {
        $indicatorHeight = PLAYEDPERCENTHEIGHT[$indicatorSize];

        $endX = $width ?: imagesx($image) - 1;
        $endY = $height ?: imagesy($image) - 1;

        // draw transparent background
        $transblack = imagecolorallocatealpha($image, 0, 0, 0, 48);
        imagefilledrectangle($image, 0, $endY - $indicatorHeight, $endX, $endY, $transblack);

        $foregroundWidth = ($endX * $percent) / 100;
        imagefilledrectangle($image, 0, $endY - $indicatorHeight, $foregroundWidth, $endY, $jf_blue);
    }
}
