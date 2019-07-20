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
    public $styleEnum;
    public $indexCount;

    public $hoverFrame;
    //public $cssFile;

    public $ImageType;

    public $popupWidth;
    public $popupHeight;

    public $thumbnailsWidth;
    public $thumbnailsHeight;

    public $Limit;

    public $nbThumbnailsPerPage;
    public $nbThumbnailsPerLine;

    public $moviesTableAlign;
    public $moviesTableVAlign;
    public $moviesTableCellspacing;


    public function __construct()
    {
        //set defaults
        $this->ImageType = "Primary";

        $this->Limit = 27;

        $this->moviesTableVAlign = "top";
        $this->moviesTableAlign = "left";
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

}

function setDataLimits($styleEnum = null)
{
    global $default_listing_style, $Limit;

    $styleEnum = $styleEnum ?? $default_listing_style;

    switch ($styleEnum) {
        case IndexStyleEnum::PosterPopup6x2:
        case IndexStyleEnum::Poster6x2:
        case IndexStyleEnum::TVBannerPopup6x2:
            $Limit = 12;
            break;
    
        case IndexStyleEnum::PosterPopup9x3:
        case IndexStyleEnum::Poster9x3:
        default:
            $Limit = 27;
            break;

        case IndexStyleEnum::Poster12x4:
            $Limit = 48;
            break;

        case IndexStyleEnum::TVBannerPopup4x2:
            $Limit = 8;      
            break;

        case IndexStyleEnum::TVBannerPopup7x2:
            $Limit = 14;
            break;             
    }
}


function setIndexStyle($styleEnum = null, $indexCount = null)
{
    global $default_listing_style;
    global $indexStyle;
    
    $indexStyle = new IndexStyle();

    $styleEnum = $styleEnum ?? $default_listing_style;

    if ($styleEnum == IndexStyleEnum::PosterPopupDynamic) {
        if (is_null($indexCount) || $indexCount > 12) {
            $styleEnum = IndexStyleEnum::PosterPopup9x3;
        } else {
            $styleEnum = IndexStyleEnum::PosterPopup6x2;
        }
    }

    $indexStyle->styleEnum = $styleEnum;
    $indexStyle->indexCount = $indexCount;

    switch ($styleEnum) {
        case IndexStyleEnum::PosterPopup6x2:
            $indexStyle->popupWidth = 218;
            $indexStyle->popupHeight = 323;

            $indexStyle->hoverFrame = "pictures/wall/hover-frame2.png";
        case IndexStyleEnum::Poster6x2:
            $indexStyle->thumbnailsWidth = 176;
            $indexStyle->thumbnailsHeight = 261;
            
            $indexStyle->nbThumbnailsPerPage = 12;
            $indexStyle->nbThumbnailsPerLine = 6;
            $indexStyle->ImageType = "Primary";
            $indexStyle->moviesTableAlign = "center";
            $indexStyle->moviesTableVAlign = "middle";
            $indexStyle->moviesTableCellspacing = 4;
            break;
    
        case IndexStyleEnum::PosterPopup9x3:
        default:
            $indexStyle->popupWidth = 160;
            $indexStyle->popupHeight = 237;

            $indexStyle->hoverFrame = "pictures/wall/hover-frame.png";
        case IndexStyleEnum::Poster9x3:
            $indexStyle->thumbnailsWidth = 117;
            $indexStyle->thumbnailsHeight = 174;

            $indexStyle->nbThumbnailsPerPage = 27;
            $indexStyle->nbThumbnailsPerLine = 9;
            $indexStyle->ImageType = "Primary";
            $indexStyle->moviesTableCellspacing = 4;
            break;

        case IndexStyleEnum::Poster12x4:
            $indexStyle->thumbnailsWidth = 87;
            $indexStyle->thumbnailsHeight = 130;

            $indexStyle->nbThumbnailsPerPage = 48;
            $indexStyle->nbThumbnailsPerLine = 12;
            $indexStyle->ImageType = "Primary";
            $indexStyle->moviesTableCellspacing = 4;
            break;

        case IndexStyleEnum::TVBannerPopup4x2:
            $indexStyle->popupWidth = 557;
            $indexStyle->popupHeight = 103;

            $indexStyle->hoverFrame = "pictures/wall/hover-frame-banner8.png";
            $indexStyle->cssFile = "css/8TVBanners.css";

            $indexStyle->thumbnailsWidth = 461;
            $indexStyle->thumbnailsHeight = 85;
            
            $indexStyle->nbThumbnailsPerPage = 8;
            $indexStyle->nbThumbnailsPerLine = 2;
            $indexStyle->ImageType = "Banner";
            $indexStyle->moviesTableAlign = "center";           
            break;

        case IndexStyleEnum::TVBannerPopup6x2:
            $indexStyle->popupWidth = 587;
            $indexStyle->popupHeight = 108;

            $indexStyle->hoverFrame = "pictures/wall/hover-frame-banner12.png";
            $indexStyle->cssFile = "css/12TVBanners.css";

            $indexStyle->thumbnailsWidth = 482;
            $indexStyle->thumbnailsHeight = 89;
            
            $indexStyle->nbThumbnailsPerPage = 12;
            $indexStyle->nbThumbnailsPerLine = 2;
            $indexStyle->ImageType = "Banner";
            $indexStyle->moviesTableAlign = "center";
            break;

        case IndexStyleEnum::TVBannerPopup7x2:
            $indexStyle->popupWidth = 659;
            $indexStyle->popupHeight = 121;

            $indexStyle->hoverFrame = "pictures/wall/hover-frame-banner14.png";
            $indexStyle->cssFile = "css/14TVBanners.css";

            $indexStyle->thumbnailsWidth = 411;
            $indexStyle->thumbnailsHeight = 76;
            
            $indexStyle->nbThumbnailsPerPage = 14;
            $indexStyle->nbThumbnailsPerLine = 2;
            $indexStyle->ImageType = "Banner";
            $indexStyle->moviesTableAlign = "center";
            break;             
    }

    return $indexStyle;
}
?>