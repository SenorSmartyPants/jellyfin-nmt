<?php
include 'data.php';

$SeasonId = $_GET["SeasonId"];
$ParentIndexNumber = $_GET["ParentIndexNumber"];

$DetailURL = getSeasonURL($SeasonId, $ParentIndexNumber);
header("Location: " . $DetailURL);
die();
?>