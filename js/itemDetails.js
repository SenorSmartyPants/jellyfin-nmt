var elRuntime;
var elVideo;
var elAudio;
var elSubtitle;
var elEndsAt;

function init() {
    elRuntime = getFirstChild('Runtime');
    elEndsAt = getFirstChild('endsAt');
    elVideo = getFirstChild('Video');
    elAudio = getFirstChild('Audio');
    elSubtitle = getFirstChild('Subtitle');
}

function updateMediaInfoDisplay(index) {
    setNodeValue(elRuntime, asItemRuntimeDesc[index]);
    setNodeValue(elEndsAt, asItemEndsAtDesc[index]);
    setNodeValue(elVideo, asItemVideoDesc[index]);
    setNodeValue(elAudio, asItemAudioDesc[index]);
    setNodeValue(elSubtitle, asItemSubtitleDesc[index]);
}