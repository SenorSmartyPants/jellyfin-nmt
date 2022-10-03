<?php
require_once 'config.php';

$default_listing_style = IndexStyleEnum::PosterPopupDynamic;

$include_jellyfin_logo_when_backdrop_present = false;

if(file_exists('config_listings_local.php')){
    // Include the file
    include('config_listings_local.php');
}

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

        case ItemType::FOLDER . '/': //assuming base folder only show in music video libraries...
        case ItemType::COLLECTIONFOLDER . '/' . CollectionType::MUSICVIDEOS:
            $indexStyle = new IndexStyle(IndexStyleEnum::ThumbPopup4x3AspectRatio);
            $indexStyle->ImageType = ImageType::PRIMARY;
            $indexStyle->offsetY = 28;
            $indexStyle->moviesTableAlign = Alignment::CENTER;
            $indexStyle->moviesTableVAlign = VerticalAlignment::MIDDLE;
            break;

        default:
            $indexStyle = new IndexStyle();
            break;
    }
}
?>