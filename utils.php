<?

function getBackdropIDandTag($item)
{
    $retval = new stdClass();
    if (count($item->BackdropImageTags) > 0) {
        $retval->Id = $item->Id;
        $retval->Tag = $item->BackdropImageTags[0];
    } elseif ($item->ParentBackdropImageTags && count($item->ParentBackdropImageTags) > 0) {
        $retval->Id = $item->ParentBackdropItemId;
        $retval->Tag = $item->ParentBackdropImageTags[0];
    } else {
        $retval->Id = null;
        $retval->Tag = null;
    }
    return $retval;
}

function translatePathToNMT($path)
{
    global $NMT_path,$NMT_playerpath;
    return str_replace($NMT_path,$NMT_playerpath,$path);
}

?>