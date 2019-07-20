<?php
$theme_css = "dark.css";

$default_listing_style = IndexStyleEnum::PosterPopupDynamic;

//TODO: don't require this info to be duplicated.
//$folder_collection_listing_style['CollectionFolder/tvshows'] = IndexStyleEnum::TVBannerPopup7x2;
//$folder_collection_listing_style['Series/'] = IndexStyleEnum::PosterPopup9x3;

$include_jellyfin_logo_when_backdrop_present = false;

function overrideIndexStyle($folderType, $collectionType)
{
    global $indexStyle;

    switch ($folderType . '/' . $collectionType) {
        case 'CollectionFolder/tvshows':
            $indexStyle = new IndexStyle(IndexStyleEnum::TVBannerPopup7x2);
            break;

        case 'Series/':
            $indexStyle = new IndexStyle(IndexStyleEnum::PosterPopup9x3);
            //override style options
            $indexStyle->moviesTableAlign = "center";
            $indexStyle->moviesTableVAlign = "bottom";
            $indexStyle->Limit = 9;
            $indexStyle->nbThumbnailsPerLine = 9;
            break;

        default:
            $indexStyle = new IndexStyle();
            break;
    }    
}

//NMT player path
$NMT_path = "/media/Videos/"; //server based path to share to NMT
$NMT_playerpath = "file:///opt/sybhttpd/localhost.drives/NETWORK_SHARE/storage/Videos/";  //NMT path to the share
?>