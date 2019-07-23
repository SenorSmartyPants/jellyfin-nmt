<?php
include '../IndexStyles.php';
header('Content-type: text/css');

$numPosters = $_GET["number"];
$styleEnum = $_GET["style"];
$numPerLine = $_GET["numPerLine"];
$thumbnailsWidth = $_GET["thumbnailsWidth"];
$thumbnailsHeight = $_GET["thumbnailsHeight"];
$popupWidth = $_GET["popupWidth"];
$popupHeight = $_GET["popupHeight"];
$moviesTableCellspacing = $_GET["moviesTableCellspacing"];
$baseOffsetX = 0;
$baseOffsetY = intval($_GET["OffsetY"]);
$align = $_GET["align"];
$vAlign = $_GET["vAlign"];

switch ($styleEnum) {
    case IndexStyleEnum::PosterPopup6x2:
        $frameDifferenceWidth = 11;
        $frameDifferenceHeight = 11;
        break;
    
    case IndexStyleEnum::PosterPopup9x3:
        $frameDifferenceWidth = 10;
        $frameDifferenceHeight = 11;
        break;
}

$thumbWidthPlusCellSpacing = $thumbnailsWidth + $moviesTableCellspacing;
$tableWidth = ($thumbWidthPlusCellSpacing) * 
    ($numPosters >= $numPerLine ? $numPerLine : $numPosters)
     + $moviesTableCellspacing;

$thumbHeightPlusCellSpacing = $thumbnailsHeight + $moviesTableCellspacing;
$tableHeight = ($thumbHeightPlusCellSpacing) * 
    (intdiv($numPosters - 1, $numPerLine) + 1)
    + $moviesTableCellspacing; 
    
//9x3 table is 1093x538
//6x2 table is 1084x534
$containingCellWidth = 1096;
$containingCellHeight = 542;
$lowerBound = 620; //612 first try


switch ($align) {
    case 'center':
        $offsetX = floor(($containingCellWidth - $tableWidth) / 2);
        break;

    case 'right':
        $offsetX = $containingCellWidth - $tableWidth;
        break;
    
    default:
        $offsetX = 0;
        break;
}
$offsetX = max($offsetX + $baseOffsetX, 0);


switch ($vAlign) {
    case 'middle':
        $offsetY = floor(($containingCellHeight - $tableHeight) / 2);
        break;

    case 'bottom':
        $offsetY = $containingCellHeight - $tableHeight;
        break;
    
    default:
        $offsetY = 0;
        break;
}
$offsetY = max($offsetY + $baseOffsetY, 0);

//echo "table dimensions = ${tableWidth}x${tableHeight}\n";
//echo "offset = ${offsetX}x${offsetY}\n";

//Height - top of table 56px down

$halfPosterWidth = ($thumbWidthPlusCellSpacing + $moviesTableCellspacing) / 2;
$frameWidth = $popupWidth + $frameDifferenceWidth * 2;
$frameHeight = $popupHeight + $frameDifferenceHeight * 2;

for ($i=0;$i < $numPosters; $i++) {
    $row = intdiv($i, $numPerLine);

    //width
    $previousPostersGap = ($thumbWidthPlusCellSpacing) * ($i % $numPerLine);
    $frameLeft = floor($previousPostersGap + $halfPosterWidth - ($frameWidth/2));
    //add offset
    $frameLeft += $offsetX;
    //bounds checking
    $frameLeft = max($frameLeft, 0);
    $frameLeft = $frameLeft + $frameWidth > $containingCellWidth ? $containingCellWidth - $frameWidth : $frameLeft;

    //height
    $frameTop = $thumbHeightPlusCellSpacing * $row + 1;
    //add offset
    $frameTop += $offsetY;    
    //bounds checking
    $frameTop = $frameTop + $frameHeight > $lowerBound ? $lowerBound - $frameHeight : $frameTop;

    echo "#imgDVD" . ($i + 1) ." { visibility: hidden; position: absolute; top: " . ($frameTop + $frameDifferenceHeight) . "px; left: " . ($frameLeft + $frameDifferenceWidth) . "px; }\n";
    echo "#frmDVD" . ($i + 1) ." { visibility: hidden; position: absolute; top: ${frameTop}px; left: ${frameLeft}px; }\n";
}
?>