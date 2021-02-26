<?php
require_once 'config.php';

$default_listing_style = IndexStyleEnum::PosterPopupDynamic;

$include_jellyfin_logo_when_backdrop_present = false;

function overrideIndexStyle($folderType, $collectionType)
{
    global $indexStyle;

    switch ($folderType . '/' . $collectionType) {
        // example to change listings style for TV shows
        /*
        case 'CollectionFolder/tvshows':
            $indexStyle = new IndexStyle(IndexStyleEnum::TVBannerPopup7x2);
            break;
        */
        case ItemType::BOXSET . '/':
        case ItemType::SERIES . '/':
            $indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup9x3);
            //override style options
            $indexStyle->moviesTableAlign = Alignment::CENTER;
            $indexStyle->moviesTableVAlign = VerticalAlignment::BOTTOM;
            $indexStyle->Limit = 9;
            break;

        default:
            $indexStyle = new IndexStyle();
            break;
    }    
}
?>