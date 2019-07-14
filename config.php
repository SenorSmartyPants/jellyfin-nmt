<?php
$theme_css = "dark.css";

$default_listing_style = IndexStyleEnum::PosterPopupDynamic;
$folder_collection_listing_style['CollectionFolder/tvshows'] = IndexStyleEnum::TVBannerPopup7x2;
$folder_collection_listing_style['Series/'] = IndexStyleEnum::PosterPopup9x3;

$include_jellyfin_logo_when_backdrop_present = false;

//NMT player path
$NMT_path = "/media/Videos/"; //server based path to share to NMT
$NMT_playerpath = "file:///opt/sybhttpd/localhost.drives/NETWORK_SHARE/storage/Videos/";  //NMT path to the share
?>