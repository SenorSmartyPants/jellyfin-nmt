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
    protected $filters;
    protected $itemTypes;

    public function __construct($itemTypes = array(ItemType::MOVIE, ItemType::SERIES, ItemType::BOXSET))
    {
        parent::__construct('Categories');  
        $this->itemTypes = $itemTypes;
        $this->filters = getFilters(null, $itemTypes, true);     
    }

    public function printContent()
    {
        $this->printCategories();
    }

    protected function printCategories() 
    {   
        $this->printCategory("Genres", $this->filters->Genres);
        $titleLetters = range("A","Z");
        array_unshift($titleLetters,"#");
        $this->printCategory("Title", $titleLetters);
        $this->printCategory("Ratings", $this->filters->OfficialRatings);
        $this->printCategory("Years", $this->filters->Years);
        $this->printCategory("Tags", $this->filters->Tags);
    }

    protected function printCategory($name, $items) 
    {
        if (!empty($items)) {
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

class CategoriesJSPage extends CategoriesPage
{
    protected function printCategory($name, $items)
    {
        if (!empty($items)) {
            if (count($this->itemTypes) == 1) {
                $collectionType = mapItemTypeToCollectionType($this->itemTypes[0]);
            } else {
                $collectionType = null;
            }

    ?>
            asCatNames.push('<?= $name ?>');
            asFilters['<?= $name ?>'] = new Array();
            asFilterNames['<?= $name ?>']  = new Array();

    <?
            foreach ($items as $item) {
                $url = categoryBrowseURL($name, $item, $collectionType);
    ?>
            asFilters['<?= $name ?>'].push("<?= $url ?>");
            asFilterNames['<?= $name ?>'].push("<?= $item ?>");
    <?
            }
        }    
    }

    public function render()
    {
        header('Content-type: text/javascript');
?>
        var sActiveCat = 'Genres';

        var asCatNames = new Array();
        var asFilters = new Object();
        var asFilterNames = new Object();   
<?      
        $this->printContent();
    }
}
?>