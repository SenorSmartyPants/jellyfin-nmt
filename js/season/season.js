        // series config vars

        var sIdLinkPrefix = 'a_e_';
        var sIdSpanPrefix = 's_e_';

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
        var elAspectRatioImg;


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
    elAspectRatioImg = document.getElementById('aspectRatio')
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
    //current episode url - used when "Play" button is pressed, does not check in currently
    elOpenEpisode.setAttribute("href", asEpisodeUrl[episodeIndex]);
    //episode mediainfo images
    showMediainfo(episodeIndex);
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

function setEpisodeListItemText(iElId, iEpisodeIndex) {
    var elDesc = getFirstChild(sIdSpanPrefix + iElId);
    elDesc.nodeValue = episodeListItemDesc(iEpisodeIndex);
}

function updatePlayedUI() {
    setEpisodeListItemText(getIndexCurrentPage(iEpisodeId), iEpisodeId);
}

function getIndexCurrentPage(iId) {
    return iId - ((iPage - 1) * iEpisodesPerPage);
}

function formatEpisodeNumber(noNew) {
    var epnum = asEpisodeNo[noNew];
    if (epnum < 10) {
        epnum = '0' + epnum;
    }
    if (iMainSeason != asSeasonNo[noNew]) {
        epnum = 'Sp';
    }
    if (asEpisodeNoEnd[noNew]) {
        epnum += '-' + asEpisodeNoEnd[noNew];
    }
    return epnum;
}

function episodeListItemDesc(iEpisodeIndex) {
    var sWatched = '';
    if (asEpisodeWatched[iEpisodeIndex]) {
        sWatched = '* ';
    }
    return formatEpisodeNumber(iEpisodeIndex) + '. ' + sWatched + asEpisodeTitleShort[iEpisodeIndex];
}

/**
 * @param  positionChange 1 = move to next, -1 = previous, +iEpisodesPerPage/-iEpisodesPerPage = paging
 */
function updateSelectedItem(positionChange) {
    iEpisodeId = iEpisodeId + positionChange;
    if (Math.abs(positionChange) == 1) {
        if (iEpisodeId == 0) {
            //wrap around to last page if multiple
            //wrap to last item if moving by 1
            iEpisodeId = iEpisodesLength;
        } else if (iEpisodeId == iEpisodesLength + 1) {
            //go to first episode
            iEpisodeId = 1;
        }
    } else {
        //paging, positionChange = +/-iEpisodesPerPage
        //maintain list position between pages
        if (iEpisodeId <= 0 || iEpisodeId > iEpisodesLength) {
            iEpisodeId = iEpisodeId - (iEpPages * positionChange);
            //check if after the end of the list
            if (iEpisodeId <= 0 || iEpisodeId > iEpisodesLength) {
                iEpisodeId = iEpisodesLength;
            }
        }
    }

    if (fmorePages) {
        var iNewPage = Math.floor((iEpisodeId - 1) / iEpisodesPerPage) + 1;
        if (iPage != iNewPage) {
            iPage = iNewPage;
            toggletab();
            return;
        }
    }
    showNfocus();
}

function clickDown() {
    updateSelectedItem(1);
}

function clickUp() {
    updateSelectedItem(-1);
}

function toggleLeft() {
    if (fmorePages) {
        updateSelectedItem(-iEpisodesPerPage);
    } else {
        showNfocus();
    }
}

function toggleRight() {
    if (fmorePages) {
        updateSelectedItem(iEpisodesPerPage);
    } else {
        showNfocus();
    }
}

var         showNfocus = function() {
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