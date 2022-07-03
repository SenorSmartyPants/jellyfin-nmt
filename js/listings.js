var title = 1;
var subtitle = 1;
var iActiveItem = 1;
var PopupsEnabled = true;

function bind() {
    if (title == 1) title = document.getElementById('title').firstChild;
    if (subtitle == 1) subtitle = document.getElementById('subtitle').firstChild;
}

function show(x) {
    bind();
    title.nodeValue = asMenuTitle[x];
    subtitle.nodeValue = asMenuSubtitle[x];

    if (PopupsEnabled) {
        showOverlay(x);
    }
    iActiveItem = x;
}

function hide(x) {
    bind();
    title.nodeValue = "\xa0";
    subtitle.nodeValue = "\xa0";
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

function openLinkURL(url) {
    location.assign(url);
}

function initpage(enablePopups) {
    PopupsEnabled = enablePopups;
    initMenu();
    return false;
}