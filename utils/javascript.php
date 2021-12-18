<?
function getJSArray($items, $forceNewline = false, $first = null)
{
    if (isset($first)) {
        array_unshift($items, $first);
    }
    if (!empty($items)) {
        //NMT has 2048 character limit per line of JS code in JS file
        //if more than 100 items, put each item on one line so we don't hit the limit
        if (count($items) > 100 || $forceNewline) {
            $padding = "\n\t\t\t\t";
        } else {
            $padding = null;
        }
        return '["' . implode("\"," . $padding . "\"", array_map('addslashes', $items)) . '"]';
    } else {
        return '[]';
    }
}
?>