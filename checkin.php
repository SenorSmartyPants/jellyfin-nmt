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

// get current users from trakt-proxy code
// this will get eliminated later
global $user_ids, $current_users;
$users = array();
foreach ($current_users as $username) {
    $users[] = $user_ids[$username];
}

startReponse();

$auth = new Authentication();

if (!$auth->IsAuthenticated($users))
{
    $auth->login2($users);
}
session_write_close();

$itemId = $_GET['id'];
$duration = $_GET['duration'];

$report = new PlaybackReporting($_SESSION['ID'], $itemId, $duration);

if (isset($_GET["JS"])) 
{
    echo "callback('outputTest2','Trakt Checkin callback');";
}
//close reponse before starting play, let play run in the background
closeResponse();

if ($_GET['action'] == 'stop') {
    $report->Stop();
} else {
    $report->Start();
}

?>