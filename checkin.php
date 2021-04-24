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

$itemId = $_GET['id'];
$duration = $_GET['duration'];
$position = $_GET['position'];
$skip = $_GET['skip'];
$trim = $_GET['trim'];

if (empty($position)) {
    $position = 0;
}
if (empty($trim)) {
    $trim = 0;
}

$report = new PlaybackReporting($_SESSION['ID'], $itemId, $duration, $skip, $trim);

if (isset($_GET["JS"])) 
{
    echo "callback('outputTest2','Checkin callback');";
}

if ($_GET['action'] == 'stop') {
    $stoppedPosition = $report->Stop();
    if (isset($_GET["JS"])) 
    {
        echo "\nupdatePosition($stoppedPosition);";
    }    
} else {
    //close reponse before starting play, let play run in the background
    closeResponse();
    $report->Start($position);
}

?>