<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = true;

class IndexPage extends ListingsPage
{
    private $resume;
    private $rewatching;

    public function __construct($title)
    {
        parent::__construct($title, false);        
        //check if there are resume items
        $this->resume = (getResume(1)->TotalRecordCount) > 0;
        //check if there are rewatching items
        $this->rewatching = false; //(getNextUp(1,0,true)->TotalRecordCount) > 0;
        $this->items = getUsersViews()->Items;
    }

    public function render()
    {
        $this->setupIndexStyle();
        setNumPagesAndIndexCount(count($this->items));
        parent::render();
    }

    private function setupIndexStyle()
    {
        $this->indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup);
        $this->indexStyle->ImageType = ImageType::PRIMARY;
        
        //960x540
        //(1096 - (n+1)*cellspacing) / n = w
        //or 
        //(1096 - (2n)*cellpadding) / n = w
        //larger thumbnails need more padding I think
        
        //thumbnail/.8 = popup dimensions
        //can be whatever I want!
        if (count($this->items) <= 6) {
            //3x2
            $this->indexStyle->thumbnailsWidth = 341;
            $this->indexStyle->thumbnailsHeight = 191;
            $this->indexStyle->popupWidth = 426;
            $this->indexStyle->popupHeight = 238;
            $this->indexStyle->Limit = 6;
            $this->indexStyle->nbThumbnailsPerLine = 3;
        } else {
            //4x3
            $this->indexStyle->thumbnailsWidth = 254;
            $this->indexStyle->thumbnailsHeight = 143;
            $this->indexStyle->popupWidth = 318;
            $this->indexStyle->popupHeight = 179;
            $this->indexStyle->Limit = 12;
            $this->indexStyle->nbThumbnailsPerLine = 4; 
        }
        $this->indexStyle->moviesTableCellspacing = 16;
        $this->indexStyle->offsetY = 156;
    }

    private function printHomeSection($sectionname, $nameAttr)
    {
        $customPrefs = $this->displayPreferences->CustomPrefs;

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
        return $nameAttr;
    }

    public function printContent()
    {   
        $nameAttr = ' name="1"';
        for ($i=0; $i < 7; $i++) { 
            $sectionname = 'homesection' . $i;
            $nameAttr = $this->printHomeSection($sectionname, $nameAttr);
        }
    }
}

$pageObj = new IndexPage('Home');
$pageObj->render();
?>