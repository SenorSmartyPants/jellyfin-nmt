<?php
include_once 'page.php';

class SeasonRedirectPage extends Page
{

    public function __construct()
    {
        parent::__construct('');     
    }

    public function render()
    {
        $SeriesId = htmlspecialchars($_GET["SeriesId"]);
        $SeasonId = htmlspecialchars($_GET["SeasonId"]);
        $IndexNumber = htmlspecialchars($_GET["IndexNumber"]);
        
        //If seriesID is passed in then we don't know the season ID
        if ($SeriesId) {
            $SeasonType = htmlspecialchars($_GET["SeasonType"]);
            if ($SeasonType == 'first') {
                $season = firstSeasonFromSeries($SeriesId);
            }
            if ($SeasonType == 'latest') {
                $season = latestSeasonFromSeries($SeriesId);
            }

            $SeasonId = $season->Id;
        }
        $DetailURL = "Season.php?id=" . $SeasonId . "&episode=" . $IndexNumber;

        //301 redirect does not change window.location in NMT gaya browser
        //this can cause images not to resolve if url is set dynamically in Javascript (using relative paths)
        //using html refresh intead, which works in this case on gaya
        //don't enclose url in single quotes, which break on gaya

        echo '<meta http-equiv="REFRESH" content="0; url=' . $DetailURL . '" />';
    }
}

$page = new SeasonRedirectPage();
$page->render();
?>