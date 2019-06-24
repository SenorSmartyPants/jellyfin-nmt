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

function setIndexStyle($indexStyle = null, $indexCount = null)
{
    global $default_listing_style, $ImageType;
    global $moviesTableAlign, $moviesTableCellpadding, $moviesTableCellspacing;
    global $thumbnailsWidth, $thumbnailsHeight, $popupWidth, $popupHeight;
    global $Limit, $nbThumbnailsPerPage, $nbThumbnailsPerLine;
    global $hoverFrame, $cssFile;

    $indexStyle = $indexStyle ?: $default_listing_style;

    if ($indexStyle == IndexStyleEnum::PosterPopupDynamic) {
        if (is_null($indexCount) || $indexCount > 12) {
            $indexStyle = IndexStyleEnum::PosterPopup9x3;
        } else {
            $indexStyle = IndexStyleEnum::PosterPopup6x2;
        }
    }

    switch ($indexStyle) {
        case IndexStyleEnum::PosterPopup6x2:
            $popupWidth = 218;
            $popupHeight = 323;

            $hoverFrame = "pictures/wall/hover-frame2.png";
            $cssFile = "css/6x2PosterIndex.css";
        case IndexStyleEnum::Poster6x2:
            $thumbnailsWidth = 176;
            $thumbnailsHeight = 261;
            
            $Limit = 12;
            $nbThumbnailsPerPage = 12;
            $nbThumbnailsPerLine = 6;
            $ImageType = "Primary";
            $moviesTableAlign = "left";
            $moviesTableCellspacing = 4;
            break;
    
        case IndexStyleEnum::PosterPopup9x3:
        default:
            $popupWidth = 160;
            $popupHeight = 237;

            $hoverFrame = "pictures/wall/hover-frame.png";
            $cssFile = "css/9x3PosterIndex.css";
        case IndexStyleEnum::Poster9x3:
            $thumbnailsWidth = 117;
            $thumbnailsHeight = 174;

            $Limit = 27;
            $nbThumbnailsPerPage = 27;
            $nbThumbnailsPerLine = 9;
            $ImageType = "Primary";
            $moviesTableAlign = "left";
            $moviesTableCellspacing = 4;
            break;

        case IndexStyleEnum::Poster12x4:
            $thumbnailsWidth = 87;
            $thumbnailsHeight = 130;

            $Limit = 48;
            $nbThumbnailsPerPage = 48;
            $nbThumbnailsPerLine = 12;
            $ImageType = "Primary";
            $moviesTableAlign = "left";
            $moviesTableCellspacing = 4;
            break;

        case IndexStyleEnum::TVBannerPopup4x2:
            $popupWidth = 557;
            $popupHeight = 103;

            $hoverFrame = "pictures/wall/hover-frame-banner8.png";
            $cssFile = "css/8TVBanners.css";

            $thumbnailsWidth = 461;
            $thumbnailsHeight = 85;
            
            $Limit = 8;
            $nbThumbnailsPerPage = 8;
            $nbThumbnailsPerLine = 2;
            $ImageType = "Banner";
            $moviesTableAlign = "center";           
            break;

        case IndexStyleEnum::TVBannerPopup6x2:
            $popupWidth = 587;
            $popupHeight = 108;

            $hoverFrame = "pictures/wall/hover-frame-banner12.png";
            $cssFile = "css/12TVBanners.css";

            $thumbnailsWidth = 482;
            $thumbnailsHeight = 89;
            
            $Limit = 12;
            $nbThumbnailsPerPage = 12;
            $nbThumbnailsPerLine = 2;
            $ImageType = "Banner";
            $moviesTableAlign = "center";
            break;

        case IndexStyleEnum::TVBannerPopup7x2:
            $popupWidth = 659;
            $popupHeight = 121;

            $hoverFrame = "pictures/wall/hover-frame-banner14.png";
            $cssFile = "css/14TVBanners.css";

            $thumbnailsWidth = 411;
            $thumbnailsHeight = 76;
            
            $Limit = 14;
            $nbThumbnailsPerPage = 14;
            $nbThumbnailsPerLine = 2;
            $ImageType = "Banner";
            $moviesTableAlign = "center";
            break;             
    }
}
?>