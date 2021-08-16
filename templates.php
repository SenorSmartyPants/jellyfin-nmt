<?
function audioCodec($audioStream)
{
    $debugOutput = '';
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
        default:
            # channelLayout empty or doesn't match known layouts
            # use count to guess
            if ($audioStream->Channels == 6) {
                $channelFile = "audch_51.png";
            } else {
                $debugOutput = '<!-- Channels = ' . $audioStream->Channels . ' -->';
                $channelFile = "../1x1.png";
            }
    }
    return (($codecFile == "unknownaudio.png") ? $audioStream->Codec : null) 
    . '<img align="top" src="images/flags/' . $codecFile 
    . '"/><img align="top" src="images/flags/' . $channelFile . '"/>'
    . $debugOutput;
}

function container($containerID)
{
    $containerID = strtolower($containerID);
    switch ($containerID) {
        case 'asf':
        case 'avi':
        case 'bin':
        case 'dat':
        case 'divx':
        case 'dvd':
        case 'img':
        case 'iso':
        case 'm1v':
        case 'm2p':
        case 'm2t':
        case 'm2v':
        case 'm4v':
        case 'mdf':
        case 'mov':
        case 'mts':
        case 'nrg':
        case 'qt':
        case 'rar':
        case 'rm':
        case 'rmp4':
        case 'tp':
        case 'trp':
        case 'ts':
        case 'vob':
        case 'm2ts':
        case 'mkv':
        case 'mp4':
        case 'mpg':
        case 'wmv':
            $url = $containerID . ".png";
            break;
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
    return (($url == "unknown.png") ? $containerID : null) . '<img src="images/flags/container_' . $url . '"/>';
}

function videoOutput($videoStream)
{
    //Don't use Display Title if Title is set
    if ($videoStream->Title) {
        //build resolution string
        $output = strval($videoStream->Height) . ($videoStream->IsInterlaced ? 'i' : 'p');
    } else {
        $output = strtolower(explode(" ", $videoStream->DisplayTitle)[0]);
    }

    if ($output === 'sd') {
        $url = "sdtv.png";
    } else {
        $url = $output . ".png";
    }
    return '<img src="images/flags/output_' .  $url . '"/>';
}

function officialRating($rating)
{
    switch ($rating) {
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
            $url = $rating;
    }
    return strlen($rating) > 0 ? '<img src="images/certificates/' .  $url . '"/>' : '';
}
?>