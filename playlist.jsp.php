<?php
include_once 'utils.php';
include_once 'page.php';

// Playlist format documented here http://www.networkedmediatank.com/wiki/index.php/Playlist_File

class PlaylistPage extends Page
{
    public $itemIDs;

    public function __construct()
    {
        parent::__construct('');
    }

    protected function printItem($name, $url)
    {
        echo $name . '|0|0|' . $url . "|\n";
    }

    public function render()
    {
        header('Content-type: text/plain');

        foreach ($this->itemIDs as $itemID) {
            $item = getItem($itemID);
            $this->printItem($item->Name, translatePathToNMT($item->Path));
        }
    }
}

$itemIDs = $_GET["itemIDs"];

$pageObj = new PlaylistPage();
$pageObj->itemIDs = explode(',', htmlspecialchars($_GET["itemIDs"]));
$pageObj->render();
