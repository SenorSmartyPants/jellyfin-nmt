<?
function traktGetterIMG()
{ ?>
    <img width="1" height="1" id="getter" src="#"/>
<?
}

function audioCodec($audioStream)
{
    switch (strtolower($audioStream->Codec)) {
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
            $channelFile = "../1x1.png";
    }
    return '<img align="top" src="/New/Jukebox/pictures/flags/' . $codecFile . '"/><img align="top" src="/New/Jukebox/pictures/flags/' . $channelFile . '"/>&nbsp;&nbsp';
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
    return '<img src="/New/Jukebox/pictures/flags/container_' . $url . '"/>&nbsp;&nbsp;';
}

function videoOutput($videoStream)
{
    //TODO testing
    $url = "output_" . strtolower(explode(" ",$videoStream->DisplayTitle)[0]) . ".png";
    return '<img src="/New/Jukebox/pictures/flags/' .  $url . '"/>&nbsp;&nbsp;';
}
/*
<xsl:template name="videoOutput">
<xsl:variable name="tmp">
  <xsl:choose>
    <xsl:when test="contains(videoOutput,'720p')">output_720p.png</xsl:when>
    <xsl:when test="contains(videoOutput,'1080p')">output_1080p.png</xsl:when>
    <xsl:when test="contains(videoOutput,'1080')">output_1080i.png</xsl:when>
    <xsl:when test="contains(videoOutput,'NTSC') or contains(videoOutput,'24p')">output_ntsc.png</xsl:when>
    <xsl:when test="contains(videoOutput,'PAL')">output_pal.png</xsl:when>
    <xsl:when test="contains(videoOutput,'480i')">output_480i.png</xsl:when>
    <xsl:when test="contains(videoOutput,'480p')">output_480p.png</xsl:when>
    <xsl:when test="contains(videoOutput,'576i')">output_576i.png</xsl:when>
    <xsl:when test="contains(videoOutput,'SDTV')">output_sdtv.png</xsl:when>
    <xsl:otherwise>unknownvideo.png</xsl:otherwise>
  </xsl:choose>
</xsl:variable>
<img><xsl:attribute name="src"><xsl:value-of select="concat('pictures/flags/', $tmp)" /></xsl:attribute></img>
</xsl:template>
*/
?>