        // series config vars

        var iPage = 1;
        var iEpPages = 1;
        var iEpisodeId = 1;
        var iEpisodesPerPage = 15;
        var sIdLinkPrefix = 'a_e_';
        var focusId = ' ';
        var helpx = 0;
        var helpy = 0;
        var fShowSeasonInfo = false;
        var fmorePages = false;
        var iEpisodesLength = asEpisodePlot.length - 1;
        var url = false;

        //##########################################################
        //## Functions Series ######################################
        //##########################################################

function setTitleCSSClass() {
    var iTitleLength = asEpisodeTitle[helpy].length;
    if (iTitleLength <= 35) {
        document.getElementById('episodeName').setAttribute("class", "tveptitle");
    }
    else if (iTitleLength <= 38) {
        document.getElementById('episodeName').setAttribute("class", "tveptitle24");
    }
    else if (iTitleLength <= 43) {
        document.getElementById('episodeName').setAttribute("class", "tveptitle22");
    }
    else if (iTitleLength <= 46) {
        document.getElementById('episodeName').setAttribute("class", "tveptitle20");
    }
    else if (iTitleLength <= 53) {
        document.getElementById('episodeName').setAttribute("class", "tveptitle18");
    }
    else {
        document.getElementById('episodeName').setAttribute("class", "tveptitle16");
        asEpisodeTitle[helpy] = asEpisodeTitle[helpy].substring(0, 56) + '...';
    }
}

function showEpisode(x) {
    helpy = x;
    setTitleCSSClass();
    document.getElementById('episodeName').firstChild.nodeValue = asEpisodeTitle[helpy];
    document.getElementById('episodeId').firstChild.nodeValue = asEpisodePlot[helpy];
    document.getElementById('episodeImg').setAttribute("src", asEpisodeImage[helpy]);
    document.getElementById('openEpisode').setAttribute("href", asEpisodeUrl[helpy]);
}

    var init = function() {

                document.getElementById('episodeImg').setAttribute("class", "");
                document.getElementById('episodeImgBack').setAttribute("class", "");
                document.getElementById('episodenInfos').setAttribute("class", "");
                iEpisodeId = 1;
                iPage = 1;
                iEpPages = Math.floor(1) + Math.floor((iEpisodesLength - 1) / iEpisodesPerPage);
                if (iEpPages > 1) {
                    fmorePages = true;
                    document.getElementById('pageCount').firstChild.nodeValue = '1' + ' / ' + iEpPages + ' (' + iEpisodesLength + ')';
                }

                var iEpisodeIdNextUp = indexOf(asEpisodeNo, focusEpisodeNo);
                if (iEpisodeIdNextUp != -1) {
                    iEpisodeId = iEpisodeIdNextUp;
                    var iPageNextUp = 1 + Math.floor((iEpisodeId - 1) / iEpisodesPerPage);
                    if (iPageNextUp > 1) {
                        iPage = iPageNextUp;
                        toggletab();
                        window.setTimeout("showNfocus()", 1);
                    } else {
                        showNfocus();
                    }
                } else {
                    //show first in list
                    showEpisode(iEpisodeId);
                }
            },

            clickDown = function() {
                if ((iEpisodeId / (iPage * iEpisodesPerPage)) == 1) {
                    //episode is max # on a page, bottom of the list
                    if (fmorePages == true) {
                        iPage = iPage + 1;
                        iEpisodeId = iEpisodeId + 1;
                        toggletab();
                        window.setTimeout("showNfocus()", 1);
                    } else {
                        //only one page, go to top
                        iEpisodeId = 1;
                        showNfocus();
                    }
                } else {
                    iEpisodeId = iEpisodeId + 1;
                    if (iEpisodeId > iEpisodesLength) {
                        iEpisodeId = 1;
                        if (fmorePages == true) {
                            iPage = 1;
                            toggletab();
                            window.setTimeout("showNfocus()", 1);
                        } else
                            showNfocus();
                    } else
                        showNfocus();
                }
            },

            clickUp = function() {
                if ((iEpisodeId - ((iPage - 1) * iEpisodesPerPage)) == 1) {
                    document.getElementById('gtPlay').focus();
                } else {
                    iEpisodeId = iEpisodeId - 1;
                    showNfocus();
                }
            },

            toggleLeft = function() {
                if (fmorePages == true) {
                    if (iPage == 1) {
                        iPage = iEpPages;
                        iEpisodeId = iEpisodeId + ((iEpPages - 1) * iEpisodesPerPage);
                        if (iEpisodeId > iEpisodesLength) {
                            iEpisodeId = iEpisodesLength;
                        }
                    } else {
                        iPage = iPage - 1;
                        iEpisodeId = iEpisodeId - iEpisodesPerPage;
                    }
                    toggletab();
                    window.setTimeout("showNfocus()", 1);
                } else
                    showNfocus();
            },

            toggleRight = function() {
                if (fmorePages == true) {
                    if (iPage == iEpPages) {
                        iPage = 1;
                        iEpisodeId = iEpisodeId - ((iEpPages - 1) * iEpisodesPerPage);
                    } else {
                        iPage = iPage + 1;
                        iEpisodeId = iEpisodeId + iEpisodesPerPage;
                        if (iEpisodeId > iEpisodesLength) iEpisodeId = iEpisodesLength;
                    }
                    toggletab();
                    window.setTimeout("showNfocus()", 1);
                } else
                    showNfocus();
            },

            showNfocus = function() {
                helpy = iEpisodeId - ((iPage - 1) * iEpisodesPerPage);
                focusId = sIdLinkPrefix + helpy;
                document.getElementById(focusId).focus();
                showEpisode(iEpisodeId);
            },

            setFocus = function(x) {
                helpx = x;
                iEpisodeId = helpx + ((iPage - 1) * iEpisodesPerPage);
                fShowSeasonInfo = false;
                showNfocus();
            },

            showSeasonInfo = function() {
                if (fShowSeasonInfo == true) {
                    showNfocus();
                    fShowSeasonInfo = false;
                } else {
                    document.getElementById('episodeName').firstChild.nodeValue = sTitleLong;
                    document.getElementById('episodeId').firstChild.nodeValue = sPlotLong;
                    fShowSeasonInfo = true;
                }
        };