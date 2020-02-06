        // series config vars

        var iPageNew = 1;
        var iEpPagesNew = 1;
        var iEpisodeIdNew = 1;
        var iEpisodesPerPageNew = 15;
        var iLoopNew = iEpisodesPerPageNew;
        var sIdLinkPrefixNew = 'a_e_';
        var sId2ndLinkPrefixNew = 'a2_e_';
        var sIdSpanPrefixNew = 's_e_';
        var sIdTvPrefixNew = 't_e_';
        var fFirstCall = false;
        var focusIdNew = ' ';
        var helpx = 0;
        var helpy = 0;
        var fShowSeasonInfo = false;
        var fmorePages = false;
        var iEpisodesLength = asEpisodePlot.length - 1;
        var url = false;
        var Eid = 'play';
        var Evod = 'vod';
        var rRand = 1;

        //##########################################################
        //## Functions Series ######################################
        //##########################################################

        var showEpisode = function(x) {
                helpy = x;
                url = buildUrl(x)
                if (asEpisodeTitle[helpy].length > 35) {
                    document.getElementById('episodeName').setAttribute("class", "tveptitle24");
                    if (asEpisodeTitle[helpy].length > 38) {
                        document.getElementById('episodeName').setAttribute("class", "tveptitle22");
                        if (asEpisodeTitle[helpy].length > 43) {
                            document.getElementById('episodeName').setAttribute("class", "tveptitle20");
                            if (asEpisodeTitle[helpy].length > 46) {
                                document.getElementById('episodeName').setAttribute("class", "tveptitle18");
                                if (asEpisodeTitle[helpy].length > 53) {
                                    document.getElementById('episodeName').setAttribute("class", "tveptitle16");
                                    asEpisodeTitle[helpy] = asEpisodeTitle[helpy].substring(0, 56) + '...'
                                }
                            }
                        }
                    }
                } else {
                    document.getElementById('episodeName').setAttribute("class", "tveptitle");
                }
                document.getElementById('episodeName').firstChild.nodeValue = asEpisodeTitle[helpy];
                document.getElementById('episodeId').firstChild.nodeValue = asEpisodePlot[helpy];
                document.getElementById('episodeImg').setAttribute("src", asEpisodeImage[helpy]);
                document.getElementById('openEpisode').setAttribute("href", url);


            },

            buildUrl = function(no) {
                return asEpisodeUrl[no];
            };

        indexOf = function(arr, item) {
            for (var i = 0; i < arr.length; i++) {
                if (arr[i] == item) {
                    return i;
                }
            }
            return -1;
        };

        initNew = function() {

                document.getElementById('episodeImg').setAttribute("class", "");
                document.getElementById('episodeImgBack').setAttribute("class", "");
                document.getElementById('episodenInfos').setAttribute("class", "");
                iEpisodeIdNew = 1;
                iPageNew = 1;
                iEpPagesNew = Math.floor(1) + Math.floor((iEpisodesLength - 1) / iEpisodesPerPageNew);
                if (iEpPagesNew > 1) {
                    fmorePages = true;
                    document.getElementById('pageCountNew').firstChild.nodeValue = '1' + ' / ' + iEpPagesNew + ' (' + iEpisodesLength + ')';
                }

                var iEpisodeIdNextUp = indexOf(asEpisodeNo, focusEpisodeNo);
                if (iEpisodeIdNextUp != -1) {
                    iEpisodeIdNew = iEpisodeIdNextUp;
                    var iPageNextUp = 1 + Math.floor((iEpisodeIdNew - 1) / iEpisodesPerPageNew);
                    if (iPageNextUp > 1) {
                        iPageNew = iPageNextUp;
                        toggletab();
                        window.setTimeout("showNfocus()", 1);
                    } else {
                        showNfocus();
                    }
                } else {
                    //show first in list
                    showEpisode(iEpisodeIdNew);
                }
            },

            clickDownNew = function() {
                if ((iEpisodeIdNew / (iPageNew * iEpisodesPerPageNew)) == 1) {
                    if (fmorePages == true) {
                        iPageNew = iPageNew + 1;
                        iEpisodeIdNew = iEpisodeIdNew + 1;
                        toggletab();
                        window.setTimeout("showNfocus()", 1);
                    } else {
                        iEpisodeIdNew = 1;
                        showNfocus();
                    }
                } else {
                    iEpisodeIdNew = iEpisodeIdNew + 1;
                    if (iEpisodeIdNew > iEpisodesLength) {
                        iEpisodeIdNew = 1;
                        if (fmorePages == true) {
                            iPageNew = 1;
                            toggletab();
                            window.setTimeout("showNfocus()", 1);
                        } else
                            showNfocus();
                    } else
                        showNfocus();
                }
            },

            clickUpNew = function() {
                if ((iEpisodeIdNew - ((iPageNew - 1) * iEpisodesPerPageNew)) == 1) {
                    document.getElementById('gtPlay').focus();
                } else {
                    iEpisodeIdNew = iEpisodeIdNew - 1;
                    showNfocus();
                }
            },

            toggleLeft = function() {
                if (fmorePages == true) {
                    if (iPageNew == 1) {
                        iPageNew = iEpPagesNew;
                        iEpisodeIdNew = iEpisodeIdNew + ((iEpPagesNew - 1) * iEpisodesPerPageNew);
                        if (iEpisodeIdNew > iEpisodesLength) {
                            iEpisodeIdNew = iEpisodesLength;
                        }
                    } else {
                        iPageNew = iPageNew - 1;
                        iEpisodeIdNew = iEpisodeIdNew - iEpisodesPerPageNew;
                    }
                    toggletab();
                    window.setTimeout("showNfocus()", 1);
                } else
                    showNfocus();
            },

            toggleRight = function() {
                if (fmorePages == true) {
                    if (iPageNew == iEpPagesNew) {
                        iPageNew = 1;
                        iEpisodeIdNew = iEpisodeIdNew - ((iEpPagesNew - 1) * iEpisodesPerPageNew);
                    } else {
                        iPageNew = iPageNew + 1;
                        iEpisodeIdNew = iEpisodeIdNew + iEpisodesPerPageNew;
                        if (iEpisodeIdNew > iEpisodesLength) iEpisodeIdNew = iEpisodesLength;
                    }
                    toggletab();
                    window.setTimeout("showNfocus()", 1);
                } else
                    showNfocus();
            },

            showNfocus = function() {
                helpy = iEpisodeIdNew - ((iPageNew - 1) * iEpisodesPerPageNew);
                focusIdNew = sIdLinkPrefixNew + helpy;
                document.getElementById(focusIdNew).focus();
                showEpisode(iEpisodeIdNew);
            },

            setFocusNew = function(x) {
                helpx = x;
                iEpisodeIdNew = helpx + ((iPageNew - 1) * iEpisodesPerPageNew);
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