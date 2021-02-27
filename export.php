<?php
//exports next up episodes and screenshots to USB drive
//For Roku

include 'data.php';

$drive = $_GET["drive"] ?: "F:";

$nextup = $_GET["nextup"];
$id = htmlspecialchars($_GET["id"]);
$parentID = htmlspecialchars($_GET["parentID"]);
$user_id = htmlspecialchars($_GET["user_id"]); //override system selected user_id

if ($id || $parentID || $nextup) {
    header("Content-Type: text/plain");
    if ($_GET["download"]) {
        header("Content-Disposition: attachment; filename=export.ps1");
    }
}

if ($nextup) {
    $itemsAndCount = getNextUp(50);
} else if ($id) {
    $item = getItem($id);
    //is this a video? export it
    if ($item->MediaType == "Video") {
        //export
        exportCommands($item);
    }
} else if ($parentID) {
    if ($_GET["unwatched"]) {
        $IsPlayed  = false;
    }
    $itemsAndCount = getUsersItems(
        null,
        "Path",
        50,
        $parentID,
        null,
        "SortName",
        ItemType::EPISODE,
        null,
        $IsPlayed,
        true,
        0
    );
} else {
    //no parameters - display input form
?>
    <form method="get">
        <table>
            <tr>
                <td>Drive</td>
                <td><select name="drive">
                        <option>F:</option>
                        <option>G:</option>
                        <option>H:</option>                        
                    </select></td>
            </tr>
            <tr>
                <td>Folder Image Style</td>
                <td><select name="FolderImageType">
                        <option>Primary</option>
                        <option selected>Thumb</option>
                        <option>None</option>
                    </select></td>
            </tr>            
            <tr>
                <td>Force download</td>
                <td><input type="checkbox" name="download" value="true"></td>
            </tr>
            <tr>
                <td>User</td>
                <td>
                    <select name="user_id">
                        <?php
                        $users = getUsersPublic();
                        foreach ($users as $user) {
                        ?>
                            <option value="<?php echo $user->Id; ?>" <?= ($user->Id == $user_id ? "selected" : "") ?>><?php echo $user->Name; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>            
            <tr>
                <td><input type='submit' name="nextup" value="Get Next Up" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Movie/Episode ID</td>
                <td><input type="text" name="id"></td>
            </tr>
            <tr>
                <td>Series/Season</td>
                <td><input type="text" name="parentID"></td>
                <td>Unwatched Only</td>
                <td><input type="checkbox" name="unwatched" value="true" checked="true"></td>
            </tr>
            <tr>
                <td><input type='submit' value="Get Selected videos" /></td>
            </tr>
        </table>
    </form>

<?
}

if ($itemsAndCount) {
    $items = $itemsAndCount->Items;

    foreach ($items as $item) {
        exportCommands($item);
    }
}






function translatePath($path)
{
    $path = str_replace("/storage", "S:", $path);
    return str_replace("/", "\\", $path);
}

function cleanPath($path)
{
    return str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '', $path);
}

function getBaseFileName($item)
{
    if ($item->Type == ItemType::EPISODE) {
        $basefilename = sprintf(
            '%dx%02d %s',
            $item->ParentIndexNumber,
            $item->IndexNumber,
            $item->Name
        );
    } else {
        $basefilename = $item->Name;
    }

    return cleanPath($basefilename);
}

function getDirName($item)
{
    if ($item->Type == ItemType::EPISODE) {
        $dirname = cleanPath($item->SeriesName);
        $dirname .= '\\';
    } else {
        //put movies, etc in the root folder
        $dirname = ''; //$item->Name;
    }
    return $dirname;
}

function downloadCommand($URL, $dirname, $filename)
{
    global $drive;
    //used to grab remote(http) objects, like images
    echo sprintf('If (! (Test-Path "' . $drive . '\%2$s%3$s")) {(New-Object Net.WebClient).DownloadFile("%1$s", "' . $drive . '\%2$s%3$s")}' . "\n", $URL, $dirname, $filename);
}

function exportCommands($item)
{
    global $drive;

    $basefilename = getBaseFileName($item);

    $newfilename = $basefilename . '.' . pathinfo($item->Path, PATHINFO_EXTENSION);
    $dirname = getDirName($item);

    //make dir if needed
    if ($dirname != '') {
        echo sprintf("New-Item -force -ItemType directory -Path \"%s\%s\" \n", $drive, $dirname);
    }

    echo sprintf('$dest = "' . $drive . '\%s%s"' . "\n", $dirname, $newfilename);

    #region images
    //directory image
    if ($dirname != '') {
        $FolderImageType = $_GET["FolderImageType"];
        if ($FolderImageType == ImageType::THUMB) {
            if ($item->ParentThumbImageTag) {
                $thumbnailURL = getImageURL($item->ParentThumbItemId, null, null, $FolderImageType, null, null, $item->ParentThumbImageTag, null, null, null, 1000);
                downloadCommand($thumbnailURL, $dirname, "folder.jpg");
            } else if ($item->ParentBackdropImageTags[0]) {
                $thumbnailURL = getImageURL($item->ParentBackdropItemId, null, null, "Backdrop", null, null, $item->ParentBackdropImageTags[0], null, null, null, 1000);
                downloadCommand($thumbnailURL, $dirname, "folder.jpg");
            }
        } else if ($FolderImageType == ImageType::PRIMARY) {
            if ($item->SeriesPrimaryImageTag) {
                $thumbnailURL = getImageURL($item->SeriesId, null, null, $FolderImageType, null, null, $item->SeriesPrimaryImageTag, null, null, null, 1000);
                downloadCommand($thumbnailURL, $dirname, "folder.jpg");
            }
        }
    }

    //video image
    if ($FolderImageType != "None") {
	    $ImageType = ImageType::PRIMARY;
	    if ($item->Type == ItemType::MOVIE) {
		$ImageType = ImageType::THUMB;
	    }

	    if ($item->ImageTags->Primary) {
		$thumbnailURL = getImageURL($item->Id, null, null, $ImageType, null, null, $item->ImageTags->$ImageType, null, null, null, 1920);
		$thumbnailfilename = $basefilename . '.jpg';

		downloadCommand($thumbnailURL, $dirname, $thumbnailfilename);
	    }
    }
    #endregion

    //video file
    echo sprintf('If (! (Test-Path $dest)) {Copy-Item -LiteralPath "%s" -Destination $dest}' . "\n", translatePath($item->Path));
}

?>
