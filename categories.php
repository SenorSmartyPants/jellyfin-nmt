<?php
include_once 'utils/javascript.php';
include_once 'utils.php';
include_once 'page.php';

class CategoriesPage extends Page
{
    protected $filters;
    protected $titleLetters;
    protected $itemTypes;
    protected $topParentId;
    protected $topParentName;
    protected $includeTags = true;

    public function __construct($itemTypes = array(ItemType::MOVIE, ItemType::SERIES, ItemType::BOXSET), $topParentId = null, $topParentName = null)
    {
        parent::__construct('Categories');  
        $this->itemTypes = $itemTypes;
        //test for empty string, convert to null
        $this->topParentId = $topParentId === '' ? null : $topParentId;
        $this->topParentName = $topParentName === '' ? null : $topParentName;

    /*
        performance notes
        Movie item type is slow(~2000ms). parentId is much faster (~500ms)
        Series item type is fast (~50-150ms). parentId is slow (~1200-1500ms)
        BoxSet item type returns no filters. parentId = ~250-400ms
        Playlist item type returns no filters. under 100ms with 1 playlist
        MusicVideo, no metadata for my library

        Conclusion: parentId for everything, except Series/tvshows, then item type
    */

        if (empty($topParentId) || 
            (!empty($itemTypes) && ($itemTypes[0] === ItemType::SERIES || count($itemTypes) > 1))) {
            $this->filters = getFilters(null, $itemTypes, true);
        } else {
            $this->filters = getFilters($topParentId, null, true);
        }

        $this->titleLetters = range("A","Z");
        array_unshift($this->titleLetters,"#");
    }

    public function printContent()
    {
        $this->printCategories();
    }

    protected function printCategories() 
    {
        $this->printCategory('Genres', 'Genres', $this->filters->Genres);
        $this->printCategory('Title', 'NameStartsWith', $this->titleLetters);
        $this->printCategory('Ratings', 'OfficialRatings', $this->filters->OfficialRatings);
        $this->printCategory('Years', 'Years', $this->filters->Years);
        if ($this->includeTags) {
            $this->printCategory('Tags', 'Tags', $this->filters->Tags);
        }
    }

    protected function printCategory($heading, $categoryName, $items) 
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
            <th width="120" valign="top"><?= $heading ?></th>
            <td class="secondaryText">
<?
            for ($i=0; $i < count($items); $i++) { 
                $url = categoryBrowseURL($categoryName, $items[$i]);
?>
                <a href="<?= $url ?>" <? if ($heading == "Genres" && $i == 0) { echo " name=1 "; } ?>><?= $items[$i] ?></a> <? if ($i < count($items) - 1) { echo " / "; } ?>
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
    private $catName;
    private $collectionType;
    private $baseurl;

    public function __construct($itemTypes = array(ItemType::MOVIE, ItemType::SERIES, ItemType::BOXSET), $topParentId = null, $topParentName = null)
    {
        parent::__construct($itemTypes, $topParentId, $topParentName);  
        $this->includeTags = false;
        if (isset($this->itemTypes) && count($this->itemTypes) == 1) {
            $this->collectionType = mapItemTypeToCollectionType($this->itemTypes[0]);
        } else {
            $this->collectionType = null;
        }
    }

    protected function getCatBrowseURLCallback($searchTerm)
    {
        return str_replace($this->baseurl, '', categoryBrowseURL($this->catName, $searchTerm, $this->collectionType, $this->topParentId, $this->topParentName));
    }

    protected function printCategory($heading, $categoryName, $items) 
    {
        if (!empty($items)) {
            $this->catName = $categoryName;
            $urls = array_map(array( $this, 'getCatBrowseURLCallback' ), $items);
?>
        asFilterNames['<?= $heading ?>'] = <?= getJSArray($items) ?>;
        asFilters['<?= $heading ?>'] = <?= getJSArray($urls, true) ?>;            

<?
        }    
    }

    private function getCategoriesJSArrayString()
    {
        $Categories = array();

        $Categories[] = 'Filters';
        $Categories[] = 'Features';
        if ($this->collectionType == CollectionType::TVSHOWS) {
            $Categories[] = 'Status';
        }

        if (!empty($this->filters->Genres)) {
            $Categories[] = 'Genres';
        }
        $Categories[] = 'Title';
        if (!empty($this->filters->OfficialRatings)) {
            $Categories[] = 'Ratings';
        }
        if (!empty($this->filters->Years)) {
            $Categories[] = 'Years';
        }
        if ($this->includeTags && !empty($this->filters->Tags)) {
            $Categories[] = 'Tags';
        }
        $Categories[] = 'Sort By';
        $Categories[] = 'Sort Order';
        return getJSArray($Categories);
    }

    public function render()
    {
        header('Content-type: text/javascript');
        $this->baseurl = categoryBrowseURL(null, null, $this->collectionType, $this->topParentId, $this->topParentName);
?>
        var asCatNames = <?= $this->getCategoriesJSArrayString() ?>;
        var asFilters = new Object();
        var asFilterNames = new Object();   

        asFilterNames['Filters'] = ["Favorites", "Unplayed", "Played", "Clear"];
        asFilters['Filters'] = ["&name=Favorites&categoryName=Filters&searchTerm=IsFavorite", 
                                "&name=Unplayed&categoryName=Filters&searchTerm=IsUnplayed", 
                                "&name=Played&categoryName=Filters&searchTerm=IsPlayed", 
                                "&name=&categoryName=&searchTerm=&sortBy=&sortOrder=&collapseBoxSetItems="];

<?      
        if ($this->collectionType == CollectionType::TVSHOWS) {
            $this->printCategory('Status', 'seriesStatus', ['Continuing', 'Ended']);
        }
?>
        asFilterNames['Features'] = ["Extras", "Subtitles", "Trailer", "Theme\xa0Song", "Theme\xa0Video"];
        asFilters['Features'] = ["&name=Extras&categoryName=hasSpecialFeature&searchTerm=true",
                                "&name=Subtitles&categoryName=hasSubtitles&searchTerm=true", 
                                "&name=Trailer&categoryName=hasTrailer&searchTerm=true",
                                "&name=Theme Song&categoryName=hasThemeSong&searchTerm=true",
                                "&name=Theme Video&categoryName=hasThemeVideo&searchTerm=true"];

<?
        $this->printContent();
?>
        asFilterNames['Sort By'] = ["Name",
                                "Community\xa0Rating",
<? if ($this->collectionType != CollectionType::TVSHOWS) { echo "\t\t\t\t\t\t\t\t\"Critic\\xa0Rating\",\n"; } ?>
                                "Date\xa0Added",
                                "Date\xa0Played",
                                "Parental\xa0Rating",
<? if ($this->collectionType != CollectionType::TVSHOWS) { echo "\t\t\t\t\t\t\t\t\"Play\\xa0Count\",\n"; } ?>
                                "Release\xa0Date"<? if ($this->collectionType != CollectionType::TVSHOWS) { echo ',"Runtime"'; } ?>];

        asFilters['Sort By'] = ["&sortBy=SortName&collapseBoxSetItems=",
                                "&sortBy=CommunityRating&collapseBoxSetItems=false",
<? if ($this->collectionType != CollectionType::TVSHOWS) { echo "\t\t\t\t\t\t\t\t\"&sortBy=CriticRating&collapseBoxSetItems=false\",\n"; } ?>
                                "&sortBy=DateCreated&collapseBoxSetItems=false",
                                "&sortBy=DatePlayed&collapseBoxSetItems=false",
                                "&sortBy=OfficialRating&collapseBoxSetItems=false",
<? if ($this->collectionType != CollectionType::TVSHOWS) { echo "\t\t\t\t\t\t\t\t\"&sortBy=PlayCount&collapseBoxSetItems=false\",\n"; } ?>
                                "&sortBy=PremiereDate&collapseBoxSetItems=false"<? if ($this->collectionType != CollectionType::TVSHOWS) { echo ',"&sortBy=Runtime&collapseBoxSetItems=false"'; } ?>]; 

        asFilterNames['Sort Order'] = ["Ascending", "Descending"];
        asFilters['Sort Order'] = ["&sortOrder=Ascending", 
                                "&sortOrder=Descending"];

        var sActiveCat = asCatNames[0];        
<?
    }
}
?>