<?php
//exports next up episodes and screenshots to USB drive
//For Roku

include_once 'data.php';

const FOLDERJPG = 'folder.jpg';

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
} elseif ($id) {
    $item = getItem($id);
    //is this a video? export it
    if ($item->MediaType == "Video") {
        //export
        exportCommands($item);
    }
} elseif ($parentID) {
    if ($_GET["unwatched"]) {
        $IsPlayed  = false;
    }

    $params = new UserItemsParams();
    $params->Fields = 'Path';
    $params->Limit = 200;
    $params->ParentID = $parentID;
    $params->SortBy = 'SortName';
    $params->IncludeItemTypes = ItemType::EPISODE;
    $params->IsPlayed = $IsPlayed;
    $params->Recursive = true;

    $itemsAndCount = getUsersItems($params);
} else {
    //no parameters - display input form
?>
    <form method="get">
        <table>
            <tr>
                <td>Drive</td>
                <td><select name="drive">
                        <option>D:</option>
                        <option>E:</option>
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
    return rtrim(str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '', $path), ".");
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
                $imageProps = new ImageParams();
                $imageProps->maxWidth = 1000;
                $imageProps->tag = $item->ParentThumbImageTag;
                $thumbnailURL = getImageURL($item->ParentThumbItemId, $imageProps, $FolderImageType);
                downloadCommand($thumbnailURL, $dirname, FOLDERJPG);
            } elseif ($item->ParentBackdropImageTags[0]) {
                $imageProps = new ImageParams();
                $imageProps->maxWidth = 1000;
                $imageProps->tag = $item->ParentBackdropImageTags[0];
                $thumbnailURL = getImageURL($item->ParentBackdropItemId, $imageProps, ImageType::BACKDROP);
                downloadCommand($thumbnailURL, $dirname, FOLDERJPG);
            }
        } elseif ($FolderImageType == ImageType::PRIMARY && $item->SeriesPrimaryImageTag) {
            $imageProps = new ImageParams();
            $imageProps->maxWidth = 1000;
            $imageProps->tag = $item->SeriesPrimaryImageTag;
            $thumbnailURL = getImageURL($item->SeriesId, $imageProps, $FolderImageType);
            downloadCommand($thumbnailURL, $dirname, FOLDERJPG);
        }
    }

    //video image
    if ($FolderImageType != "None") {
        $ImageType = ImageType::PRIMARY;
        if ($item->Type == ItemType::MOVIE) {
            if ($item->ImageTags->Thumb) {
                $ImageType = ImageType::THUMB;
            } else {
                $ImageType = ImageType::BACKDROP;
            }
        }

        if ($item->ImageTags->Primary) {
            $imageProps = new ImageParams();
            $imageProps->maxWidth = 1920;
            $imageProps->tag = $item->ImageTags->$ImageType;
            $thumbnailURL = getImageURL($item->Id, $imageProps, $ImageType);
            $thumbnailfilename = $basefilename . '.jpg';

            downloadCommand($thumbnailURL, $dirname, $thumbnailfilename);
        }
    }
    #endregion

    //video file
    echo sprintf('If (! (Test-Path $dest)) {Copy-Item -LiteralPath "%s" -Destination $dest}' . "\n", translatePath($item->Path));
}
