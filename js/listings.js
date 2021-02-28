var title = 1;
var subtitle = 1;
var iActiveItem = 1;
var PopupsEnabled = true;

function bind() {
    if (title == 1) title = document.getElementById('title');
    if (subtitle == 1) subtitle = document.getElementById('subtitle');
}

function show(x) {
    bind();
    title.firstChild.nodeValue = document.getElementById('title' + x).firstChild.nodeValue;
    var subX = document.getElementById('subtitle' + x).firstChild;
    if (subX) {
        subtitle.firstChild.nodeValue = subX.nodeValue;
    }

    if (PopupsEnabled) {
        showOverlay(x);
    }
    iActiveItem = x;
}

function hide(x) {
    bind();
    title.firstChild.nodeValue = "\xa0";
    subtitle.firstChild.nodeValue = "\xa0";
    if (PopupsEnabled) {
        hideOverlay(x);
    }
}

function showOverlay(x) {
    hideOverlay(iActiveItem);
    document.styleSheets[0].cssRules[(x - 1) * 2].style.visibility = "visible";
    document.styleSheets[0].cssRules[(x - 1) * 2 + 1].style.visibility = "visible";
}
function hideOverlay(x) {
    document.styleSheets[0].cssRules[(x - 1) * 2].style.visibility = "hidden";
    document.styleSheets[0].cssRules[(x - 1) * 2 + 1].style.visibility = "hidden";
}

function openLink(x) {
    location.assign(document.getElementById(x).href);
}

function initpage(enablePopups) {
    PopupsEnabled = enablePopups;
    initMenu();
    return false;
}