<?php

header('Content-type: text/javascript');

session_start();
echo $_SESSION['listingsData'];
