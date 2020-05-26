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

$report = new PlaybackReporting($_SESSION['ID'], $itemId, $duration);

if (isset($_GET["JS"])) 
{
    echo "callback('outputTest2','Checkin callback');";
}
//close reponse before starting play, let play run in the background
closeResponse();

if ($_GET['action'] == 'stop') {
    $report->Stop();
} else {
    $report->Start();
}

?>