function setVOD(elem, Evod) {
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

var sId2ndLinkPrefix = 'a2_e_';
var sIdTvPrefix = 't_e_';

function formatEpisodeListItem(iElId, iEpisodeIndex) {
    //cache elements
    var elDesc = getFirstChild(sIdSpanPrefix + iElId);
    var elLink = document.getElementById(sIdLinkPrefix + iElId);
    var el2ndLink = document.getElementById(sId2ndLinkPrefix + iElId);
    var elTvID = document.getElementById(sIdTvPrefix + iElId);

    if (iEpisodeIndex < (iEpisodesLength + 1)) {
        elDesc.nodeValue = episodeListItemDesc(iEpisodeIndex);

        elLink.setAttribute("href", "#playepisode" + iElId);
        elLink.setAttribute("onmouseover", 'showEpisode(' + iEpisodeIndex + ')');
        
        el2ndLink.setAttribute("href", asEpisodeUrl[iEpisodeIndex]);
        setVOD(el2ndLink, asEpisodeVod[iElId]);

        elTvID.setAttribute("TVID", asEpisodeNo[iEpisodeIndex]);
    } else {
        //clear entry
        elDesc.nodeValue = " ";
        elLink.setAttribute("href", " ");
        elLink.setAttribute("onmouseover", " ");
        elTvID.setAttribute("TVID", " ");
    }    
}

function toggletab() {
    var startingIndex = Math.floor((iPage - 1) * iEpisodesPerPage);
    for (var i = 1; i <= iEpisodesPerPage; i++) {
        var iEpisodeIndex = startingIndex + i;
        formatEpisodeListItem(i, iEpisodeIndex)
    }
    document.getElementById('pageCount').firstChild.nodeValue = ' ' + iPage + ' / ' + iEpPages + ' (' + iEpisodesLength + ')';
                            
    //wait for new page to draw, then set focus 
    window.setTimeout("showNfocus()", 1);
}