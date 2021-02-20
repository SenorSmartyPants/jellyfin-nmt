var sId2ndLinkPrefix = 'a2_e_';
var iLoop = iEpisodesPerPage;
var sIdSpanPrefix = 's_e_';
var sIdTvPrefix = 't_e_';
var Eid = 'play';
var Evod = 'vod';


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
            if (fTVplaylist == true) {
                elLinkNew.setAttribute("href", asEpisodePlaylist[noNew]);
            } else {
                var sUrlNew = asEpisodeUrl[noNew];
                elLinkNew.setAttribute("href", "#playepisode" + i);
                el2ndLinkNew.setAttribute("href", sUrlNew);
                setVOD(s2ndLinkIdNew, asEpisodeVod[i]);
            }
            var sMouseOverValueNew = 'showEpisode(' + noNew + ')';
            elLinkNew.setAttribute("onmouseover", sMouseOverValueNew);

            var iEpisodeNoNew = asEpisodeNo[noNew];
            if (iEpisodeNoNew < 10) {
                iEpisodeNoNew = '0' + iEpisodeNoNew;
            }
            if (iMainSeason != asSeasonNo[noNew]) {
                iEpisodeNoNew = 'S' + iEpisodeNoNew;
            }
            if (asEpisodeNoEnd[noNew]) {
                iEpisodeNoNew += '-' + asEpisodeNoEnd[noNew];
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
    document.getElementById('pageCount').firstChild.nodeValue = ' ' + iPage + ' / ' + iEpPages + ' (' + iEpisodesLength + ')';
                            
    //wait for new page to draw, then set focus 
    window.setTimeout("showNfocus()", 1);
}