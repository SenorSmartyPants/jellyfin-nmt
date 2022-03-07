var elRuntime;
var elVideo;
var elAudio;
var elSubtitle;
var elEndsAt;

function getFirstChildOfId(id) {
    var elem = document.getElementById(id);
    if (elem != null) {
        return elem.firstChild;
    }
}

function safeNodeValueSet(elem, value) {
    if (elem != null) {
        elem.nodeValue = value;
    }
}

function init() {
    elRuntime = getFirstChildOfId('Runtime');
    elEndsAt = getFirstChildOfId('endsAt');
    elVideo = getFirstChildOfId('Video');
    elAudio = getFirstChildOfId('Audio');
    elSubtitle = getFirstChildOfId('Subtitle');
}

function updateMediaInfoDisplay(index) {
    safeNodeValueSet(elRuntime, asItemRuntimeDesc[index]);
    safeNodeValueSet(elEndsAt, asItemEndsAtDesc[index]);
    safeNodeValueSet(elVideo, asItemVideoDesc[index]);
    safeNodeValueSet(elAudio, asItemAudioDesc[index]);
    safeNodeValueSet(elSubtitle, asItemSubtitleDesc[index]);
}