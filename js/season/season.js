        // series config vars

        var sIdLinkPrefix = 'a_e_';
        var fShowSeasonInfo = false;
        var iEpisodesLength = asEpisodePlot.length - 1;
        var url = false;
        var elEpisodeName;
        var elEpisodeId;
        var elEpisodeImg;
        var elOpenEpisode;

        //##########################################################
        //## Functions Series ######################################
        //##########################################################

//called from Season.php and season.js
function showEpisode(episodeIndex) {
    elEpisodeName.setAttribute("class", asEpisodeTitleCSS[episodeIndex]);
    elEpisodeName.firstChild.nodeValue = asEpisodeTitle[episodeIndex];
    elEpisodeId.nodeValue = asEpisodePlot[episodeIndex];
    elEpisodeImg.setAttribute("src", asEpisodeImage[episodeIndex]);
    elOpenEpisode.setAttribute("href", asEpisodeUrl[episodeIndex]);
}

    var init = function() {

                //save reference to dynamic elements
                elEpisodeName = document.getElementById('episodeName');
                elEpisodeId = document.getElementById('episodeId').firstChild;
                elEpisodeImg = document.getElementById('episodeImg');
                elOpenEpisode = document.getElementById('openEpisode');
            },

            clickDown = function() {
                iEpisodeId = iEpisodeId + 1;
                if (iEpisodeId > iEpisodesLength) {
                    //go to first episode
                    iEpisodeId = 1;
                    if (fmorePages) {
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
                if (fmorePages) {
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
                if (fmorePages) {
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
                if (fShowSeasonInfo) {
                    showNfocus();
                    fShowSeasonInfo = false;
                } else {
                    elEpisodeName.firstChild.nodeValue = sTitleLong;
                    elEpisodeId.nodeValue = sPlotLong;
                    fShowSeasonInfo = true;
                }
        };