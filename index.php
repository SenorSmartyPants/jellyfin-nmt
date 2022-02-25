<?php
include_once 'listings.php';

$useSeasonNameForMenuItems = true;

class IndexPage extends ListingsPage
{
    private $resume;
    private $rewatching;
    private $homeSections;

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
        $this->getHomeSections();
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
    }

    private function getHomeSections()
    {
        $this->homeSections = array();
        $nameAttr = ' name="1"';
        $prefs = $this->displayPreferences->CustomPrefs;
        for ($i=0; $i < 7; $i++) { 
            $sectionname = $prefs->{'homesection' . $i};
            $sectionHTML = $this->getHomeSection($sectionname, $nameAttr);
            if (!is_null($sectionHTML)) 
            {
                $this->homeSections[$sectionname] = $sectionHTML;
            }
            if ($sectionname == 'librarybuttons' || $sectionname == 'smalllibrarytiles')
            {
                //calc offset based on number of lines before my media grid
                //each line text is 27px
                $this->indexStyle->offsetY = 27 * (count($this->homeSections) - 1) + $this->indexStyle->moviesTableCellspacing 
                    + floor(($this->indexStyle->popupHeight - $this->indexStyle->thumbnailsHeight) / 2); 
            }
        }
    }

    private function getHomeSection($sectionname, &$nameAttr)
    {
        $sectionHTML = null;

        switch ($sectionname) {
            case 'resume':
                if ($this->resume) {
                    $sectionHTML = '<a href="continueWatching.php"' . $nameAttr . '>Continue Watching ></a><br clear="all"/>';
                    $nameAttr = null;
                }                     
                break;

            case 'nextup':
                $sectionHTML = '<a href="nextUp.php"' . $nameAttr . '>Next Up ></a><br clear="all"/>';
                $nameAttr = null;
                break;

            case 'rewatching':
                if ($this->rewatching) {
                    $sectionHTML =  '<a href="nextUp.php?rewatching"' . $nameAttr . '>Rewatching ></a><br clear="all"/>';
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
                        
                        $sectionHTML .= sprintf('<a href="latest.php?%s" %s > %s %s ></a>&nbsp;', 
                            http_build_query($cbp), $nameAttr, $cbp->name, $view->Name);
                        $nameAttr = null;     
                    }    
                }
                if (!is_null($sectionHTML))
                {
                    $sectionHTML .= '<br clear="all"/>';
                }
                break;
                
            case 'librarybuttons':
            case 'smalllibrarytiles':
                $sectionHTML = '**PLACEHOLDER**';
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
                $sectionHTML = 'New section to support: ' . $sectionname . '<br/>';
                break;
        }
        return $sectionHTML;
    }

    public function printContent()
    {
        // room for 4 lines of text above current size of thumbnails
        foreach ($this->homeSections as $sectionname => $homeSection) {
            if ($sectionname == 'librarybuttons' || $sectionname == 'smalllibrarytiles')
            {
                $this->printPosterTable($this->items);
            } else {
                echo $homeSection;
            }
        }
    }
}

$pageObj = new IndexPage('Home');
$pageObj->render();
?>