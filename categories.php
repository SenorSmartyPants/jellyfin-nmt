<?php

include 'listings.php';

$filters = getFilters(null, "movie,series,boxset", true);

/* TODO: decades?
//make a comma delimited list of years in the decade
$decades = array();
foreach ($filters->Years as $year) {
    # code...
    $decade = intval(floor($year / 10) * 10);
    if (!in_array($decade, $decades)) {
        array_push($decades,$decade);
    }
}
*/
printHeadEtc();

printNavbarAndCategories("Categories", $filters);

printTitleTable();

printFooter();

function printNavbarAndCategories($title, $filters)
{
    ?>
    <table border="0" cellpadding="0" cellspacing="0" align="left"><tr valign="top"><td height="598">
    <?php  
    printNavbar($title);

    printCategories($filters);
?>
    </td></tr></table>
<?php    
}

function printCategories($filters) {   
    printCategory("Genres", $filters->Genres);
    $titleLetters = range("A","Z");
    array_unshift($titleLetters,"#");
    printCategory("Title", $titleLetters);
    printCategory("Tags", $filters->Tags);
    printCategory("Ratings", $filters->OfficialRatings);
    printCategory("Years", $filters->Years);
}

function printCategory($name, $items) {
    global $jukebox_url;
    if (count($items) > 0) {
?>
    <table class="categories" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="10" height="10" background="<?= $jukebox_url ?>pictures/wall/cat-tl.png"></td>
        <td height="10" colspan="2" background="<?= $jukebox_url ?>pictures/wall/cat-t.png"></td>
        <td width="10" height="10" background="<?= $jukebox_url ?>pictures/wall/cat-tr.png"></td>
    </tr>
    <tr>
        <td background="<?= $jukebox_url ?>pictures/wall/cat-l.png"></td>
        <th width="120" valign="top"><?= $name ?></th>
        <td class="txt">
<?
        for ($i=0; $i < count($items); $i++) { 
?>
            <a href="browse.php?CollectionType=search&Name=<?= urlencode($items[$i]) ?>&<?= $name . "=" . urlencode($items[$i]) ?>" <? if ($name == "Genres" && $i == 0) { echo " name=1 "; } ?>><?= $items[$i] ?></a> <? if ($i < count($items) - 1) { echo " / "; } ?>
<?
        }
?>
        </td>
        <td background="<?= $jukebox_url ?>pictures/wall/cat-r.png"></td>
    </tr>
    <tr>
        <td width="10" height="10" background="<?= $jukebox_url ?>pictures/wall/cat-bl.png"></td>
        <td height="10" colspan="2" background="<?= $jukebox_url ?>pictures/wall/cat-b.png"></td>
        <td width="10" height="10" background="<?= $jukebox_url ?>pictures/wall/cat-br.png"></td>
    </tr>
    </table>
    
    <table border="0" cellspacing="0" cellpadding="0"><tr><td height="10"></td></tr></table>
<?
    }
}
?>