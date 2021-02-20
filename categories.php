<?php
include_once 'utils.php';
include_once 'page.php';

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

class CategoriesPage extends Page
{
    public $filters;

    public function __construct()
    {
        parent::__construct('Categories');  
        $this->filters = getFilters(null, "movie,series,boxset", true);     
    }

    public function printContent()
    {
        $this->printCategories();
    }

    private function printCategories() 
    {   
        $this->printCategory("Genres", $this->filters->Genres);
        $titleLetters = range("A","Z");
        array_unshift($titleLetters,"#");
        $this->printCategory("Title", $titleLetters);
        $this->printCategory("Ratings", $this->filters->OfficialRatings);
        $this->printCategory("Years", $this->filters->Years);
        $this->printCategory("Tags", $this->filters->Tags);
    }

    private function printCategory($name, $items) 
    {
        if (count($items) > 0) {
?>
        <table class="categories" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td width="10" height="10" background="images/wall/cat-tl.png"></td>
            <td height="10" colspan="2" background="images/wall/cat-t.png"></td>
            <td width="10" height="10" background="images/wall/cat-tr.png"></td>
        </tr>
        <tr>
            <td background="images/wall/cat-l.png"></td>
            <th width="120" valign="top"><?= $name ?></th>
            <td class="secondaryText">
<?
            for ($i=0; $i < count($items); $i++) { 
                $url = categoryBrowseURL($name, $items[$i]);
?>
                <a href="<?= $url ?>" <? if ($name == "Genres" && $i == 0) { echo " name=1 "; } ?>><?= $items[$i] ?></a> <? if ($i < count($items) - 1) { echo " / "; } ?>
<?
            }
?>
            </td>
            <td background="images/wall/cat-r.png"></td>
        </tr>
        <tr>
            <td width="10" height="10" background="images/wall/cat-bl.png"></td>
            <td height="10" colspan="2" background="images/wall/cat-b.png"></td>
            <td width="10" height="10" background="images/wall/cat-br.png"></td>
        </tr>
        </table>
        
        <table border="0" cellspacing="0" cellpadding="0"><tr><td height="10"></td></tr></table>
<?
        }
    }
}

$page = new CategoriesPage();
$page->render();
?>