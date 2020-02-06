function setVOD(Eid, Evod) {

    document.getElementById(Eid).removeAttribute('vod');
    document.getElementById(Eid).removeAttribute('zcd');

    if (Evod == 'playlist') {
        var newAttribute = document.createAttribute("vod");
        newAttribute.nodeValue = "playlist";
        document.getElementById(Eid).setAttributeNode(newAttribute);
    } else if (Evod == 'zcd') {
        var newAttribute = document.createAttribute("zcd");
        newAttribute.nodeValue = "2";
        document.getElementById(Eid).setAttributeNode(newAttribute);
    } else if (Evod == 'vod') {
        var newAttribute = document.createAttribute("vod");
        newAttribute.nodeValue = "";
        document.getElementById(Eid).setAttributeNode(newAttribute);
    }
}

function toggletab() {
    for (var i = 1; i <= iLoopNew; i++) {
        var iElIdNew = i;
        var sLinkIdNew = sIdLinkPrefixNew + iElIdNew;
        var s2ndLinkIdNew = sId2ndLinkPrefixNew + iElIdNew;
        var sSpanIdNew = sIdSpanPrefixNew + iElIdNew;
        var sTvIdNew = sIdTvPrefixNew + iElIdNew;

        var elLinkNew = document.getElementById(sLinkIdNew);
        var el2ndLinkNew = document.getElementById(s2ndLinkIdNew);
        var elSpanNew = document.getElementById(sSpanIdNew);
        var elTvIDNew = document.getElementById(sTvIdNew);
        var noNew = Math.floor(((iPageNew - 1) * iLoopNew) + i);
        if (noNew < (iEpisodesLength + 1)) {
            if (fTVplaylist == true) {
                elLinkNew.setAttribute("href", asEpisodePlaylist[noNew]);
            } else {
                var sUrlNew = asEpisodeUrl[noNew];
                elLinkNew.setAttribute("href", "#playepisode" + i);
                el2ndLinkNew.setAttribute("href", sUrlNew);
                setVOD(s2ndLinkIdNew, asEpisodeVod[i]);
            }
            var sMouseOverValueNew = 'return showEpisode(' + noNew + ')';
            elLinkNew.setAttribute("onmouseover", sMouseOverValueNew);
            elLinkNew.setAttribute("season", asSeasonNo[noNew]);
            elLinkNew.setAttribute("episode", asEpisodeNo[noNew]);
            elLinkNew.setAttribute("tvdbid", asEpisodeTVDBID[noNew]);

            var iEpisodeNoNew = asEpisodeNo[noNew];
            if (iEpisodeNoNew < 10) {
                iEpisodeNoNew = '0' + iEpisodeNoNew;
            }
            if (iMainSeason != asSeasonNo[noNew]) {
                iEpisodeNoNew = 'S' + iEpisodeNoNew;
            }

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
    document.getElementById('pageCountNew').firstChild.nodeValue = ' ' + iPageNew + ' / ' + iEpPagesNew + ' (' + iEpisodesLength + ')';
}