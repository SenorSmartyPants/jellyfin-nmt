<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = true;

$items = getUsersViews()->Items;

/* features needed
Series name only for menuitem title
*/
$indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup);

//960x540
//(1096 - (n+1)*cellspacing) / n = w
//or 
//(1096 - (2n)*cellpadding) / n = w
//larger thumbnails need more padding I think

//thumbnail/.8 = popup dimensions
//can be whatever I want!
if (count($items) <= 6) {
    //3x2
    $indexStyle->thumbnailsWidth = 341;
    $indexStyle->thumbnailsHeight = 191;
    $indexStyle->popupWidth = 426;
    $indexStyle->popupHeight = 238;
    $indexStyle->Limit = 6;
    $indexStyle->nbThumbnailsPerLine = 3;
} else {
    //4x3
    $indexStyle->thumbnailsWidth = 254;
    $indexStyle->thumbnailsHeight = 143;
    $indexStyle->popupWidth = 318;
    $indexStyle->popupHeight = 179;
    $indexStyle->Limit = 12;
    $indexStyle->nbThumbnailsPerLine = 4; 
}
$indexStyle->moviesTableCellspacing = 16;
$indexStyle->offsetY = 156;
$indexStyle->ImageType = ImageType::PRIMARY;

setNumPagesAndIndexCount(count($items));

class IndexPage extends ListingsPage
{
    private $resume;
    //private $rewatching;

    public function __construct($title)
    {
        parent::__construct($title, false);        
        //check if there are resume items
        $this->resume = (getResume(1)->TotalRecordCount) > 0;
        //check if there are rewatching items
        //$this->rewatching = (getNextUp(1,0,true)->TotalRecordCount) > 0;
    }

    public function printContent()
    {
        $nameAttr = ' name="1"';
        $customPrefs = $this->displayPreferences->CustomPrefs;
        for ($i=0; $i < 7; $i++) { 
            $sectionname = 'homesection' . $i;
            switch ($customPrefs->{$sectionname}) {
                case 'resume':
                    if ($this->resume) {
                        echo '<a href="continueWatching.php"' . $nameAttr . '>Continue Watching ></a>&nbsp;';
                        $nameAttr = null;
                    }                     
                    break;

                case 'nextup':
                    echo '<a href="nextUp.php"' . $nameAttr . '>Next Up ></a>';
                    echo '<br clear="all"/>';
                    $nameAttr = null;
                    break;

                case 'rewatching':
                    if ($this->rewatching) {
                        echo '<a href="nextUp.php?rewatching"' . $nameAttr . '>Rewatching ></a>&nbsp;';
                        echo '<br clear="all"/>';
                        $nameAttr = null;
                    }
                    break;                    
                    
                case 'latestmedia':
                    $user = getUser();
                    $latestExcludes = $user->Configuration->LatestItemsExcludes;
                    foreach ($this->items as $view) {
                        if ($view->CollectionType != CollectionType::BOXSETS 
                            && $view->CollectionType != CollectionType::PLAYLISTS
                            && !in_array($view->Id, $latestExcludes))
                        {
                            $cbp = new CategoryBrowseParams();

                            $cbp->name = 'Latest';
                            $cbp->topParentName = $view->Name;
                            $cbp->topParentId = $view->Id;
                            $cbp->collectionType = $view->CollectionType;
                            
                            ?>
                            <a href="latest.php?<?= http_build_query($cbp) ?>" <?= $nameAttr ?>><?= $cbp->name . ' ' . $view->Name ?> ></a>
                            <br clear="all"/>
                            <?
                            $nameAttr = null;     
                        }    
                    }
                    break;
                    
                case 'librarybuttons':
                case 'smalllibrarytiles':
?>
        <a href="categoriesHTML.php">Categories ></a>
        <br clear="all"/>
<?
                    $this->printPosterTable($this->items);
                    $nameAttr = null;
                    break;                      

                # Not supported sections
                case 'resumeaudio':
                case 'activerecordings':
                case 'livetv':
                case 'none':
                    break;

                default:
                    # Catch new sections, just display name
                    echo 'New section to support: ' . $customPrefs->{$sectionname} . '<br/>';
                    break;
            }
        }
    }
}

$pageObj = new IndexPage('Home');
$pageObj->indexStyle = $indexStyle;
$pageObj->items = $items;
$pageObj->render();
?>