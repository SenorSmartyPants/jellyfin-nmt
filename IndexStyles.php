<?php

abstract class IndexStyleEnum
{
    const PosterPopup9x3 = 0;
    const PosterPopup6x2 = 1;
    const PosterPopupDynamic = 2; //will be 6x2 if count(index) >= 12
    const Poster9x3 = 3;
    const Poster6x2 = 4;
    const Poster12x4 = 5;
    const TVBannerPopup4x2 = 6;
    const TVBannerPopup6x2 = 7;
    const TVBannerPopup7x2 = 8;
}

class IndexStyle {
    private $styleEnum;
    private $indexCount;

    public $hoverFrame;

    public $ImageType;

    public $popupWidth;
    public $popupHeight;

    public $thumbnailsWidth;
    public $thumbnailsHeight;

    public $Limit;
    public $nbThumbnailsPerLine;

    public $moviesTableAlign;
    public $moviesTableVAlign;
    public $moviesTableCellspacing;

    private function setDataLimits($styleEnum = null)
    {
        global $default_listing_style;
    
        $styleEnum = $styleEnum ?? $default_listing_style;
    
        switch ($styleEnum) {
            case IndexStyleEnum::PosterPopup6x2:
            case IndexStyleEnum::Poster6x2:
            case IndexStyleEnum::TVBannerPopup6x2:
                $this->Limit = 12;
                break;
        
            case IndexStyleEnum::PosterPopup9x3:
            case IndexStyleEnum::Poster9x3:
            default:
                $this->Limit = 27;
                break;
    
            case IndexStyleEnum::Poster12x4:
                $this->Limit = 48;
                break;
    
            case IndexStyleEnum::TVBannerPopup4x2:
                $this->Limit = 8;      
                break;
    
            case IndexStyleEnum::TVBannerPopup7x2:
                $this->Limit = 14;
                break;             
        }
    }

    public function __construct($styleEnum = null)
    {
        //set defaults
        $this->ImageType = "Primary";

        $this->setDataLimits($styleEnum);

        $this->moviesTableVAlign = "top";
        $this->moviesTableAlign = "left";

        $this->setIndexStyle($styleEnum);
    }


    
    public function cssFile()
    {    
        $retval = null;
        switch ($this->styleEnum) {
            case IndexStyleEnum::PosterPopup6x2:
            case IndexStyleEnum::PosterPopup9x3:
                $retval = "css/grid.css.php?number=" . $this->indexCount . 
                    "&style=" . $this->styleEnum . 
                    "&align=" . $this->moviesTableAlign . 
                    "&vAlign=" . $this->moviesTableVAlign;
                break;
    
            case IndexStyleEnum::TVBannerPopup4x2:
                $retval = "css/8TVBanners.css";        
                break;
    
            case IndexStyleEnum::TVBannerPopup6x2:
                $retval = "css/12TVBanners.css";
                break;
    
            case IndexStyleEnum::TVBannerPopup7x2:
                $retval = "css/14TVBanners.css";
                break;             
        }
        return $retval;
    }

    function setIndexCount($indexCount){
        $this->indexCount = $indexCount;

        if ($this->styleEnum == IndexStyleEnum::PosterPopupDynamic) {
            if (is_null($indexCount) || $indexCount > 12) {
                $this->styleEnum = IndexStyleEnum::PosterPopup9x3;
            } else {
                $this->styleEnum = IndexStyleEnum::PosterPopup6x2;
            }
            $this->setIndexStyle($this->styleEnum);
        }
    }

    private function setIndexStyle($styleEnum = null)
    {
        global $default_listing_style;
        global $indexStyle;
        
        $indexStyle = $this;
    
        $this->styleEnum = $styleEnum ?? $default_listing_style;
    
        switch ($this->styleEnum) {
            case IndexStyleEnum::PosterPopup6x2:
                $this->popupWidth = 218;
                $this->popupHeight = 323;
    
                $this->hoverFrame = "pictures/wall/hover-frame2.png";
            case IndexStyleEnum::Poster6x2:
                $this->thumbnailsWidth = 176;
                $this->thumbnailsHeight = 261;
                
                $this->Limit = 12;
                $this->nbThumbnailsPerLine = 6;
                $this->ImageType = "Primary";
                $this->moviesTableAlign = "center";
                $this->moviesTableVAlign = "middle";
                $this->moviesTableCellspacing = 4;
                break;
        
            case IndexStyleEnum::PosterPopup9x3:
            default:
                $this->popupWidth = 160;
                $this->popupHeight = 237;
    
                $this->hoverFrame = "pictures/wall/hover-frame.png";
            case IndexStyleEnum::Poster9x3:
                $this->thumbnailsWidth = 117;
                $this->thumbnailsHeight = 174;
    
                $this->nbThumbnailsPerLine = 9;
                $this->ImageType = "Primary";
                $this->moviesTableCellspacing = 4;
                break;
    
            case IndexStyleEnum::Poster12x4:
                $this->thumbnailsWidth = 87;
                $this->thumbnailsHeight = 130;
    
                $this->nbThumbnailsPerLine = 12;
                $this->ImageType = "Primary";
                $this->moviesTableCellspacing = 4;
                break;
    
            case IndexStyleEnum::TVBannerPopup4x2:
                $this->popupWidth = 557;
                $this->popupHeight = 103;
    
                $this->hoverFrame = "pictures/wall/hover-frame-banner8.png";
                $this->cssFile = "css/8TVBanners.css";
    
                $this->thumbnailsWidth = 461;
                $this->thumbnailsHeight = 85;
                
                $this->nbThumbnailsPerLine = 2;
                $this->ImageType = "Banner";
                $this->moviesTableAlign = "center";           
                break;
    
            case IndexStyleEnum::TVBannerPopup6x2:
                $this->popupWidth = 587;
                $this->popupHeight = 108;
    
                $this->hoverFrame = "pictures/wall/hover-frame-banner12.png";
                $this->cssFile = "css/12TVBanners.css";
    
                $this->thumbnailsWidth = 482;
                $this->thumbnailsHeight = 89;
                
                $this->nbThumbnailsPerLine = 2;
                $this->ImageType = "Banner";
                $this->moviesTableAlign = "center";
                break;
    
            case IndexStyleEnum::TVBannerPopup7x2:
                $this->popupWidth = 659;
                $this->popupHeight = 121;
    
                $this->hoverFrame = "pictures/wall/hover-frame-banner14.png";
                $this->cssFile = "css/14TVBanners.css";
    
                $this->thumbnailsWidth = 411;
                $this->thumbnailsHeight = 76;
                
                $this->nbThumbnailsPerLine = 2;
                $this->ImageType = "Banner";
                $this->moviesTableAlign = "center";
                break;             
        }
    }

}
?>