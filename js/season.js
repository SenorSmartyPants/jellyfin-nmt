        // series config vars

        var iPage = 1;
        var iEpPages = 1;
        var iEpisodeId = 1;
        var iEpisodesPerPage = 15;
        var sIdLinkPrefix = 'a_e_';
        var fShowSeasonInfo = false;
        var fmorePages = false;
        var iEpisodesLength = asEpisodePlot.length - 1;
        var url = false;
        var elEpisodeName;
        var elEpisodeId;
        var elEpisodeImg;
        var elOpenEpisode;

        //##########################################################
        //## Functions Series ######################################
        //##########################################################

//only called from season.js
function setTitleCSSClass(episodeIndex) {
    var iTitleLength = asEpisodeTitle[episodeIndex].length;
    if (iTitleLength <= 35) {
        elEpisodeName.setAttribute("class", "tveptitle");
    }
    else if (iTitleLength <= 38) {
        elEpisodeName.setAttribute("class", "tveptitle24");
    }
    else if (iTitleLength <= 43) {
        elEpisodeName.setAttribute("class", "tveptitle22");
    }
    else if (iTitleLength <= 46) {
        elEpisodeName.setAttribute("class", "tveptitle20");
    }
    else if (iTitleLength <= 53) {
        elEpisodeName.setAttribute("class", "tveptitle18");
    }
    else {
        elEpisodeName.setAttribute("class", "tveptitle16");
        if (iTitleLength > 56) {
            asEpisodeTitle[episodeIndex] = asEpisodeTitle[episodeIndex].substring(0, 56) + '...';
        }
    }
}

//called from Season.php and season.js
function showEpisode(episodeIndex) {
    setTitleCSSClass(episodeIndex);
    elEpisodeName.firstChild.nodeValue = asEpisodeTitle[episodeIndex];
    elEpisodeId.nodeValue = asEpisodePlot[episodeIndex];
    elEpisodeImg.setAttribute("src", asEpisodeImage[episodeIndex]);
    elOpenEpisode.setAttribute("href", asEpisodeUrl[episodeIndex]);
}

    var init = function() {

                elEpisodeName = document.getElementById('episodeName');
                elEpisodeId = document.getElementById('episodeId').firstChild;
                elEpisodeImg = document.getElementById('episodeImg');
                elOpenEpisode = document.getElementById('openEpisode');

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
                    } else {
                        showNfocus();
                    }
                } else {
                    //show first in list
                    showEpisode(iEpisodeId);
                }
            },

            clickDown = function() {
                iEpisodeId = iEpisodeId + 1;
                if (iEpisodeId > iEpisodesLength) {
                    //go to first episode
                    iEpisodeId = 1;
                    if (fmorePages == true) {
                        //multiple pages, go to first page
                        iPage = 1;
                        toggletab();
                        return;
                    }
                } else if ((iEpisodeId % iEpisodesPerPage) == 1) {
                    //moved to a new page
                    iPage = iPage + 1;
                    toggletab();
                    return; 
                }
                //just move down
                showNfocus();
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
                } else
                    showNfocus();
            },

            showNfocus = function() {
                //index on the current page
                var episodeIndexThisPage = iEpisodeId - ((iPage - 1) * iEpisodesPerPage);
                var focusId = sIdLinkPrefix + episodeIndexThisPage;
                document.getElementById(focusId).focus();
                showEpisode(iEpisodeId);
            },

            //called from Season.php
            //setFocus is called with episode index on current displayed page
            //used when handling TVID to set focus to episode number type (not the parameter)
            //would be better to pass in iEpisodeId? then t_e_X wouldn't need to be updated on paging
            setFocus = function(episodeIndexThisPage) {
                iEpisodeId = episodeIndexThisPage + ((iPage - 1) * iEpisodesPerPage);
                fShowSeasonInfo = false;
                showNfocus();
            },

            showSeasonInfo = function() {
                if (fShowSeasonInfo == true) {
                    showNfocus();
                    fShowSeasonInfo = false;
                } else {
                    elEpisodeName.firstChild.nodeValue = sTitleLong;
                    elEpisodeId.nodeValue = sPlotLong;
                    fShowSeasonInfo = true;
                }
        };