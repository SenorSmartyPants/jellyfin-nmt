<?php
include '../IndexStyles.php';
header('Content-type: text/css');

$numPosters = $_GET["number"];
$styleEnum = $_GET["style"];
$align = $_GET["align"];
$vAlign = $_GET["vAlign"];
$indexStyle = new IndexStyle($styleEnum);

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

//define frame as optional?
//additional OffsetX

//minimum parameters needed
/*
$nbThumbnailsPerLine = 3;
$thumbnailsWidth = 341;
$thumbnailsHeight = 191;
$moviesTableCellspacing = 16;
moviesTableAlign
moviesTableVAlign
popupWidth
popupHeight
*/

$thumbWidthPlusCellSpacing = $indexStyle->thumbnailsWidth + $indexStyle->moviesTableCellspacing;
$tableWidth = ($thumbWidthPlusCellSpacing) * 
    ($numPosters >= $indexStyle->nbThumbnailsPerLine ? $indexStyle->nbThumbnailsPerLine : $numPosters)
     + $indexStyle->moviesTableCellspacing;

$thumbHeightPlusCellSpacing = $indexStyle->thumbnailsHeight + $indexStyle->moviesTableCellspacing;
$tableHeight = ($thumbHeightPlusCellSpacing) * 
    (intdiv($numPosters - 1, $indexStyle->nbThumbnailsPerLine) + 1)
    + $indexStyle->moviesTableCellspacing; 
    
//9x3 table is 1093x538
//6x2 table is 1084x534
$containingCellWidth = 1096; //1090 or 1096?
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
$offsetX = max($offsetX, 0);


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
$offsetY = max($offsetY, 0);

//echo "table dimensions = ${tableWidth}x${tableHeight}\n";
//echo "offset = ${offsetX}x${offsetY}\n";

//Height - top of table 56px down

$halfPosterWidth = ($thumbWidthPlusCellSpacing + $indexStyle->moviesTableCellspacing) / 2;
$frameWidth = $indexStyle->popupWidth + $frameDifferenceWidth * 2;
$frameHeight = $indexStyle->popupHeight + $frameDifferenceHeight * 2;

for ($i=0;$i < $numPosters; $i++) {
    $row = intdiv($i, $indexStyle->nbThumbnailsPerLine);

    //width
    $previousPostersGap = ($thumbWidthPlusCellSpacing) * ($i % $indexStyle->nbThumbnailsPerLine);
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