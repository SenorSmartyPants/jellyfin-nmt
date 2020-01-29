<?php
include 'data.php';

$SeriesId = $_GET["SeriesId"];
$SeasonId = $_GET["SeasonId"];
$IndexNumber = $_GET["IndexNumber"];

//If seriesID is passed in then we don't know the season ID
if ($SeriesId) {
    $season = firstSeasonFromSeries($SeriesId);
    $SeasonId = $season->Id;
}
$DetailURL = "Season.php?id=" . $SeasonId . "&episode=" . $IndexNumber;

//301 redirect does not change window.location in NMT gaya browser
//this can cause images not to resolve if url is set dynamically in Javascript (using relative paths)
//using html refresh intead, which works in this case on gaya
//don't enclose url in single quotes, which break on gaya
?>
<meta http-equiv="REFRESH" content="0; url=<?= $DetailURL ?>" />