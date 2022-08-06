<?
function audioCodecImageURL($audioStream)
{
    switch (strtolower($audioStream->Codec)) {
        case 'eac3':
            $codecFile = "audcod_dolbyplus.png";
            break;
        case 'ac3':
            $codecFile = "audcod_dolby.png";
            break;
        case 'truehd':
            $codecFile = "audcod_truehd.png";
            break;
        case 'dts-hd':
            $codecFile = "audcod_dtshd.png";
            break;
        case 'dts':
            $codecFile = "audcod_dts.png";
            break;
        case 'mp3':
            $codecFile = "audcod_mp3.png";
            break;
        case 'aac':
            $codecFile = "audcod_aac.png";
            break;
        case 'vorbis':
            $codecFile = "audcod_ogg.png";
            break;
        case 'a_flac':
            $codecFile = "audcod_flac.png";
            break;
        case 'qdm2':
        case 'qdmc':
            $codecFile = "audcod_q.png";
            break;
        case 'microsoft':
        case '162':
        case 'vc-1':
            $codecFile = "codec_ms.png";
            break;
        case 'pcm':
            $codecFile = "audcod_pcm.png";
            break;
        case 'mpa1l2':
        case 'mpeg-1a':
            $codecFile = "audcod_mpa.png";
            break;
        default:
            $codecFile = "unknownaudio.png";
    }

    return (($codecFile == "unknownaudio.png") ? $audioStream->Codec : null) 
    . 'images/flags/' . $codecFile;
}

function audioChannelsImageURL($audioStream)
{
    $debugOutput = '';
    switch ($audioStream->ChannelLayout) {
        case '5.1':
            $channelFile = "audch_51.png";
            break;
        case '7.1':
            $channelFile = "audch_71.png";
            break;
        case 'stereo':
        case '2.0':
            $channelFile = "audch_20.png";
            break;
        case '2.1':
            $channelFile = "audch_21.png";
            break;
        case '6.1':
            $channelFile = "audch_61.png";
            break;
        case '5':
            $channelFile = "audch_50.png";
            break;
        case 'mono':
        case '1.0':
            $channelFile = "audch_10.png";
            break;                            
        default:
            # channelLayout empty or doesn't match known layouts
            # use count to guess
            if ($audioStream->Channels == 6) {
                $channelFile = "audch_51.png";
            } else {
                $debugOutput = '<!--Channels=' . $audioStream->Channels . '-->';
                $channelFile = "../1x1.png";
            }
    }
    return 'images/flags/' . $channelFile . $debugOutput;
}

function containerImageURL($containerID)
{
    $containerID = strtolower($containerID);
    $justAddExtension = ['asf', 'avi', 'bin', 'dat', 'divx', 'dvd', 'img', 'iso', 'm1v', 
        'm2p', 'm2t', 'm2v', 'm4v', 'mdf', 'mov', 'mts', 'nrg', 'qt', 'rar', 'rm', 
        'rmp4', 'tp', 'trp', 'ts', 'vob', 'm2ts', 'mkv', 'mp4', 'mpg', 'wmv'];

    if (in_array($containerID, $justAddExtension)) {
        $url = $containerID . ".png";
    } else {
        switch ($containerID) {
            case 'mpegts':
            case 'bdav':
            case 'bluray':
                $url = "m2ts.png";
                break;
            case 'matroska':
                $url = "mkv.png";
                break;
            case 'mpeg-4':
                $url = "mp4.png";
                break;
            case 'PS':
                $url = "mpg.png";
                break;
            case 'windows media':
                $url = "wmv.png";
            break;
                default:
                $url = "unknown.png";
        }  
    }

    return (($url == "unknown.png") ? $containerID : null) . 'images/flags/container_' . $url;
}

function videoOutputImageURL($videoStream)
{
    $url = getResolutionText($videoStream) . ".png";
    return 'images/flags/output_' .  $url;
}

function videoOutputHeight($videoStream)
{
    return strval($videoStream->Height);
}

// based on https://github.com/Shadowghost/jellyfin/blob/63d943aab92a4b5f69e625a269eb830bcbfb4d22/MediaBrowser.Model/Entities/MediaStream.cs#L582-L613
// tweaked to better (IMHO) handle non standard low definition resolutions < 480
// add 384p 
function getResolutionText($videoStream)
{
    $h = $videoStream->Height;
    $w = $videoStream->Width;
    switch (true)
    {
        // 256x144 (16:9 square pixel format)
        case $w <= 256 && $h <= 144:
            $retval = "144";
            break;
        // 426x240 (16:9 square pixel format)
        case $w <= 426 && $h <= 240:
            $retval = "240";
            break;
        // 640x360 (16:9 square pixel format)
        case $w <= 640 && $h <= 360:
            $retval = "360";
            break;
        // 682x384 (16:9 square pixel format)
        case $w <= 682 && $h <= 384: // Added
            $retval = "384";
            break;
        // 854x480 (16:9 square pixel format)
        case $w <= 854 && $h <= 480:
            $retval = "480";
            break;
        // 960x544 (16:9 square pixel format)
        case $w <= 960 && $h <= 544:
            $retval = "540";
            break;
        // 1024x576 (16:9 square pixel format)
        case $w <= 1024 && $h <= 576:
            $retval = "576";
            break;
        // 1280x720
        case $w <= 1280 && $h <= 962:
            $retval = "720";
            break;
        // 2560x1080 (FHD ultra wide 21:9) using 1440px width to accomodate WQHD
        case $w <= 2560 && $h <= 1440:
            $retval = "1080";
            break;
        default:
            //NMT does not support 4k
            $retval = "4k";
            break;
    }
    return $retval . ($videoStream->IsInterlaced ? "i" : "p");
}

function officialRatingImageURL($item)
{
    switch ($item->OfficialRating) {
        case 'TV-14':
            $url = "tv_14.png";
            break;
        case 'TV-G':
            $url = "tv_g.png";
            break;
        case 'TV-MA':
            $url = "tv_ma.png";
            break;
        case 'TV-PG':
            $url = "tv_pg.png";
            break;
        case 'TV-Y':
            $url = "tv_y.png";
            break;
        case 'TV-Y7':
            $url = "tv_y7.png";
            break;
        case 'TV-Y7FV':
            $url = "tv_y7fv.png";
            break;
        default:
            $url = $item->OfficialRating;
    }
    return strlen($url) > 0 ? 'images/certificates/' .  $url : 'images/1x1.png';
}
?>