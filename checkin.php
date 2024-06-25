<?php
require_once 'auth.php';
require_once 'playbackReporting.php';

function startReponse()
{
    // Buffer all upcoming output...
    ob_start();
}

function closeResponse()
{
    session_write_close();

    // Get the size of the output.
    $size = ob_get_length();

    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");

    // Set the content length of the response.
    header("Content-Length: {$size}");

    // Close the connection.
    header("Connection: close");

    // Flush all output.
    ob_end_flush();
    ob_flush();
    flush();
}

startReponse();

$auth = new Authentication();

$itemId = htmlspecialchars($_GET['id']);
if (!ctype_xdigit($itemId)) {
    // only allow hexdec strings
    $itemId = null;
}
$duration = intval($_GET['duration']);
$position = intval($_GET['position']);
$skip = intval($_GET['skip']);
$trim = intval($_GET['trim']);

$report = new PlaybackReporting($_SESSION['ID'], $itemId, $duration, $skip, $trim);

if ($_GET['action'] == 'stop') {
    $positionAndPlayed = $report->Stop();
    if (isset($_GET["JS"])) {
        echo "\nupdatePosition($positionAndPlayed->PositionInSeconds);";
        echo "\nupdatePlayed(" . strbool($positionAndPlayed->Played) . ");";
    }
} else {
    //close reponse before starting play, let play run in the background
    closeResponse();
    $report->Start($position);
}
