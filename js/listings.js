var title = 1;
var subtitle = 1;
var iActiveItem = 0;
var PopupsEnabled = true;

function bind() {
    if (title == 1) title = document.getElementById('title').firstChild;
    if (subtitle == 1) subtitle = document.getElementById('subtitle').firstChild;
}

function show(x) {
    bind();
    var y = (iPage - 1) * iPageSize + x;
    title.nodeValue = asMenuTitle[y];
    subtitle.nodeValue = asMenuSubtitle[y];

    if (PopupsEnabled) {
        showOverlay(x);
    }
    iActiveItem = y;
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
    document.styleSheets[0].cssRules[(x) % iPageSize].style.visibility = "visible";
}
function hideOverlay(x) {
    document.styleSheets[0].cssRules[(x) % iPageSize].style.visibility = "hidden";
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

// dynamic paging functions

function updateSelectedItem(pageChange) {
    iPage = iPage + pageChange;
    if (iPage <= 0) {
        iPage = iNumPages;
    } else if (iPage > iNumPages) {
        iPage = 1;
    }

    // display left item when going to next page
    // display rightmost item when going to prev
    var iNewItem = 0;
    if (pageChange == -1) {
        // going to prev page, either iPageSize, or mod
        if (iPage != iNumPages) {
            iNewItem = iPageSize - 1;
        } else {
            // this is not a full page, put on last item
            iNewItem = (asMenuTitle.length - 1) % iPageSize;
        }
    }

    // using pgup/dn TVID does not change the focus
    // update focus to valid item before updating display
    focus(iNewItem);

    updateDisplayedMenuItems();

    //wait for new page to draw, then show
    //window.setTimeout("show(" + iNewItem + ")", 1);
    show(iNewItem);
}

//almost same as toggletab
function updateDisplayedMenuItems() {
    hide(iActiveItem); // hide the active item before redrawing (when tvid paging, two popups could be active at once)
    var startingIndex = Math.floor((iPage - 1) * iPageSize);
    for (var i = 0; i < iPageSize; i++) {
        formatListItem(i, startingIndex + i);
    }
    updatePagePositionDisplay(iPage);
}

function formatListItem(iElId, iIndex) {
    var elMenu = document.getElementById('menuImg' + iElId);
    var elimgDVD = document.getElementById('imgDVD' + iElId);

    if (iIndex < asMenuTitle.length) {
        elMenu.setAttribute("src", asMenuImage[iIndex]);

        elimgDVD.setAttribute("src", asMenuImage[iIndex]);
        // TODO: test for frame popup

        if (iIndex == asMenuTitle.length - 1 || (iElId == iPageSize - 1 && iNumPages > 1)) {
            createAttr(elMenu.parentNode, 'onkeyrightset', 'pgdnload');
        } else if (iElId == iPageSize - 1) {
            createAttr(elMenu.parentNode, 'onkeyrightset', iElId + 1);
        } else {
            removeAttr(elMenu.parentNode, 'onkeyrightset');
        }
    } else {
        // clear entry
        elMenu.setAttribute("src", 'images/wall/transparent.png');
        elimgDVD.setAttribute("src", '');
        // TODO: test for frame popup
    }
}
