        // series config vars

        var sIdLinkPrefix = 'a_e_';
        var fShowingSeasonInfo = false;
        var iEpisodesLength = asEpisodePlot.length - 1;
        var url = false;
        var elEpisodeName;
        var elEpisodeId;
        var elEpisodeImg;
        var elOpenEpisode;
        var elRuntime;
        var elVideoOutputImg;
        var elContainerImg;
        var elAudioCodecImg;
        var elAudioChannelsImg;


        //##########################################################
        //## Functions Series ######################################
        //##########################################################

//called from Season.php and season.js

function init() {
    //save reference to dynamic elements
    //text
    elEpisodeName = getFirstChild('episodeName');
    elEpisodeId = getFirstChild('episodeId');
    elRuntime = getFirstChild('runtime');
    //images
    elEpisodeImg = document.getElementById('episodeImg');
    elVideoOutputImg = document.getElementById('videoOutput');
    elContainerImg = document.getElementById('container');
    elAudioCodecImg = document.getElementById('audioCodec');
    elAudioChannelsImg = document.getElementById('audioChannels');
    //anchor
    elOpenEpisode = document.getElementById('openEpisode');
}

function showEpisode(episodeIndex) {
    //text
    setParentAttr(elEpisodeName, "class", asEpisodeTitleCSS[episodeIndex]);
    setNodeValue(elEpisodeName, asEpisodeTitle[episodeIndex]);
    setNodeValue(elEpisodeId, asEpisodePlot[episodeIndex]);
    setNodeValue(elRuntime, asRuntime[episodeIndex]);
    //images
    elEpisodeImg.setAttribute("src", asEpisodeImage[episodeIndex]);
    elVideoOutputImg.setAttribute("src", asVideoOutput[episodeIndex]);
    elContainerImg.setAttribute("src", asContainer[episodeIndex]);
    elAudioCodecImg.setAttribute("src", asAudioCodec[episodeIndex]);
    elAudioChannelsImg.setAttribute("src", asAudioChannels[episodeIndex]);
    //current episode url - TODO: is this needed?
    elOpenEpisode.setAttribute("href", asEpisodeUrl[episodeIndex]);
}

function showSeasonInfo() {
    if (fShowingSeasonInfo) {
        showNfocus();
        fShowingSeasonInfo = false;
    } else {
        setNodeValue(elEpisodeName, sTitleLong);
        setNodeValue(elEpisodeId, sPlotLong);
        fShowingSeasonInfo = true;
    }
}

    var clickDown = function() {
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
                fShowingSeasonInfo = false;
                showNfocus();
            };