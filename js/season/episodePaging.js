function createAttr(elem, name, value) {
    var attr = document.createAttribute(name)
    attr.nodeValue = value;
    elem.setAttributeNode(attr);
}

function setVOD(Eid, Evod) {
    var elem = document.getElementById(Eid);

    elem.removeAttribute('vod');
    elem.removeAttribute('zcd');

    if (Evod == 'playlist') {
        createAttr(elem, 'vod', 'playlist');
    } else if (Evod == 'zcd') {
        createAttr(elem, 'zcd', '2');
    } else if (Evod == 'vod') {
        createAttr(elem, 'vod', '');
    }
}

function formatEpisodeNumber(noNew) {
    var epnum = asEpisodeNo[noNew];
    if (epnum < 10) {
        epnum = '0' + epnum;
    }
    if (iMainSeason != asSeasonNo[noNew]) {
        epnum = 'S' + epnum;
    }
    if (asEpisodeNoEnd[noNew]) {
        epnum += '-' + asEpisodeNoEnd[noNew];
    }
    return epnum;
}

function toggletab() {
    var sId2ndLinkPrefix = 'a2_e_';
    var iLoop = iEpisodesPerPage;
    var sIdSpanPrefix = 's_e_';
    var sIdTvPrefix = 't_e_';
    
    for (var i = 1; i <= iLoop; i++) {
        var iElIdNew = i;
        var sLinkIdNew = sIdLinkPrefix + iElIdNew;
        var s2ndLinkIdNew = sId2ndLinkPrefix + iElIdNew;
        var sSpanIdNew = sIdSpanPrefix + iElIdNew;
        var sTvIdNew = sIdTvPrefix + iElIdNew;

        var elLinkNew = document.getElementById(sLinkIdNew);
        var el2ndLinkNew = document.getElementById(s2ndLinkIdNew);
        var elSpanNew = document.getElementById(sSpanIdNew);
        var elTvIDNew = document.getElementById(sTvIdNew);
        var noNew = Math.floor(((iPage - 1) * iLoop) + i);
        if (noNew < (iEpisodesLength + 1)) {
            if (fTVplaylist) {
                elLinkNew.setAttribute("href", asEpisodePlaylist[noNew]);
            } else {
                var sUrlNew = asEpisodeUrl[noNew];
                elLinkNew.setAttribute("href", "#playepisode" + i);
                el2ndLinkNew.setAttribute("href", sUrlNew);
                setVOD(s2ndLinkIdNew, asEpisodeVod[i]);
            }
            var sMouseOverValueNew = 'showEpisode(' + noNew + ')';
            elLinkNew.setAttribute("onmouseover", sMouseOverValueNew);

            var iEpisodeNoNew = formatEpisodeNumber(noNew);

            var sWatched = '';
            if (asEpisodeWatched[noNew]) {
                sWatched = "* ";
            }
            var sSpanValueNew = iEpisodeNoNew + '. ' + sWatched + asEpisodeTitleShort[noNew];

            elSpanNew.firstChild.nodeValue = sSpanValueNew;
            elTvIDNew.setAttribute("TVID", asEpisodeNo[noNew]);
        } else {
            elLinkNew.setAttribute("href", " ");
            elLinkNew.setAttribute("onmouseover", " ");
            elSpanNew.firstChild.nodeValue = " ";
            elTvIDNew.setAttribute("TVID", " ");
        }

    }
    document.getElementById('pageCount').firstChild.nodeValue = ' ' + iPage + ' / ' + iEpPages + ' (' + iEpisodesLength + ')';
                            
    //wait for new page to draw, then set focus 
    window.setTimeout("showNfocus()", 1);
}