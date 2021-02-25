//functions from ppp3.1 YAMJ skin

var fMenu = false;
var iActiveItem = 1;
var fInit = false;

var sActiveCat = 'Other';
var iActiveCat = 0;
var iMaxCats = 9;
var iMaxGenres = 9;
var iLinkMinCat = 5;
var iLinkMinGen = 5;
var iActiveGen = 0;
var iGenres = 0;


//static options to get it running

  var asCatNames = new Array();
  var asFilters = new Object();
  var asFilterNames = new Object();
  
  
	    asCatNames.push("Other");
	    asFilters['Other'] = new Array();
	    asFilterNames['Other']  = new Array();
	    
	      asFilters['Other'].push("Other_All_1");
	      asFilterNames['Other'].push("All");
	    
	      asFilters['Other'].push("Other_New_1");
	      asFilterNames['Other'].push("New");
	    
	      asFilters['Other'].push("Other_New-TV_1");
	      asFilterNames['Other'].push("New-TV");
	    
	      asFilters['Other'].push("Other_Movies_1");
	      asFilterNames['Other'].push("Movies");
	    
	      asFilters['Other'].push("Other_Extras_1");
	      asFilterNames['Other'].push("Extras");
	    
	      asFilters['Other'].push("Other_TV Shows_1");
	      asFilterNames['Other'].push("TV Shows");
	    
	      asFilters['Other'].push("Other_HD-720_1");
	      asFilterNames['Other'].push("HD-720");
	    
	      asFilters['Other'].push("Other_HD-1080_1");
	      asFilterNames['Other'].push("HD-1080");
	    
	      asFilters['Other'].push("Other_Top250_1");
	      asFilterNames['Other'].push("Top250");
	    
	      asFilters['Other'].push("Other_Unwatched_1");
	      asFilterNames['Other'].push("Unwatched");
	    
	      asFilters['Other'].push("Other_Rating_1");
	      asFilterNames['Other'].push("Rating");
	    
	      asFilters['Other'].push("Other_Sets_1");
	      asFilterNames['Other'].push("Sets");
	    
	    asCatNames.push("Genres");
	    asFilters['Genres'] = new Array();
	    asFilterNames['Genres']  = new Array();
	    
	      asFilters['Genres'].push("Genres_Action_1");
	      asFilterNames['Genres'].push("Action");
	    
	      asFilters['Genres'].push("Genres_Adult_1");
	      asFilterNames['Genres'].push("Adult");
	    
	      asFilters['Genres'].push("Genres_Animation_1");
	      asFilterNames['Genres'].push("Animation");
	    
	      asFilters['Genres'].push("Genres_Comedy_1");
	      asFilterNames['Genres'].push("Comedy");
	    
	      asFilters['Genres'].push("Genres_Documentary_1");
	      asFilterNames['Genres'].push("Documentary");
	    
	      asFilters['Genres'].push("Genres_Drama_1");
	      asFilterNames['Genres'].push("Drama");
	    
	      asFilters['Genres'].push("Genres_Family_1");
	      asFilterNames['Genres'].push("Family");
	    
	      asFilters['Genres'].push("Genres_Fantasy_1");
	      asFilterNames['Genres'].push("Fantasy");
	    
	      asFilters['Genres'].push("Genres_Home and Garden_1");
	      asFilterNames['Genres'].push("Home and Garden");
	    
	      asFilters['Genres'].push("Genres_Mini-Series_1");
	      asFilterNames['Genres'].push("Mini-Series");
	    
	      asFilters['Genres'].push("Genres_News_1");
	      asFilterNames['Genres'].push("News");
	    
	      asFilters['Genres'].push("Genres_Other_1");
	      asFilterNames['Genres'].push("Other");
	    
	      asFilters['Genres'].push("Genres_Reality_1");
	      asFilterNames['Genres'].push("Reality");
	    
	      asFilters['Genres'].push("Genres_Sci-Fi_1");
	      asFilterNames['Genres'].push("Sci-Fi");
	    
	      asFilters['Genres'].push("Genres_Special Interest_1");
	      asFilterNames['Genres'].push("Special Interest");
	    
	      asFilters['Genres'].push("Genres_Suspense_1");
	      asFilterNames['Genres'].push("Suspense");
	    
	      asFilters['Genres'].push("Genres_TV Movie_1");
	      asFilterNames['Genres'].push("TV Movie");
	    
	      asFilters['Genres'].push("Genres_Talk Show_1");
	      asFilterNames['Genres'].push("Talk Show");
	    
	      asFilters['Genres'].push("Genres_Thriller_1");
	      asFilterNames['Genres'].push("Thriller");
	    
	    asCatNames.push("Title");
	    asFilters['Title'] = new Array();
	    asFilterNames['Title']  = new Array();
	    
	      asFilters['Title'].push("Title_09_1");
	      asFilterNames['Title'].push("09");
	    
	      asFilters['Title'].push("Title_A_1");
	      asFilterNames['Title'].push("A");
	    
	      asFilters['Title'].push("Title_B_1");
	      asFilterNames['Title'].push("B");
	    
	      asFilters['Title'].push("Title_C_1");
	      asFilterNames['Title'].push("C");
	    
	      asFilters['Title'].push("Title_D_1");
	      asFilterNames['Title'].push("D");
	    
	      asFilters['Title'].push("Title_E_1");
	      asFilterNames['Title'].push("E");
	    
	      asFilters['Title'].push("Title_F_1");
	      asFilterNames['Title'].push("F");
	    
	      asFilters['Title'].push("Title_G_1");
	      asFilterNames['Title'].push("G");
	    
	      asFilters['Title'].push("Title_H_1");
	      asFilterNames['Title'].push("H");
	    
	      asFilters['Title'].push("Title_I_1");
	      asFilterNames['Title'].push("I");
	    
	      asFilters['Title'].push("Title_J_1");
	      asFilterNames['Title'].push("J");
	    
	      asFilters['Title'].push("Title_K_1");
	      asFilterNames['Title'].push("K");
	    
	      asFilters['Title'].push("Title_L_1");
	      asFilterNames['Title'].push("L");
	    
	      asFilters['Title'].push("Title_M_1");
	      asFilterNames['Title'].push("M");
	    
	      asFilters['Title'].push("Title_N_1");
	      asFilterNames['Title'].push("N");
	    
	      asFilters['Title'].push("Title_O_1");
	      asFilterNames['Title'].push("O");
	    
	      asFilters['Title'].push("Title_P_1");
	      asFilterNames['Title'].push("P");
	    
	      asFilters['Title'].push("Title_Q_1");
	      asFilterNames['Title'].push("Q");
	    
	      asFilters['Title'].push("Title_R_1");
	      asFilterNames['Title'].push("R");
	    
	      asFilters['Title'].push("Title_S_1");
	      asFilterNames['Title'].push("S");
	    
	      asFilters['Title'].push("Title_T_1");
	      asFilterNames['Title'].push("T");
	    
	      asFilters['Title'].push("Title_U_1");
	      asFilterNames['Title'].push("U");
	    
	      asFilters['Title'].push("Title_V_1");
	      asFilterNames['Title'].push("V");
	    
	      asFilters['Title'].push("Title_W_1");
	      asFilterNames['Title'].push("W");
	    
	      asFilters['Title'].push("Title_Y_1");
	      asFilterNames['Title'].push("Y");
	    
	      asFilters['Title'].push("Title_Z_1");
	      asFilterNames['Title'].push("Z");
	    
	    asCatNames.push("Certification");
	    asFilters['Certification'] = new Array();
	    asFilterNames['Certification']  = new Array();
	    
	      asFilters['Certification'].push("Certification_TV-G_1");
	      asFilterNames['Certification'].push("TV-G");
	    
	      asFilters['Certification'].push("Certification_PG_1");
	      asFilterNames['Certification'].push("PG");
	    
	      asFilters['Certification'].push("Certification_TV-PG_1");
	      asFilterNames['Certification'].push("TV-PG");
	    
	      asFilters['Certification'].push("Certification_PG-13_1");
	      asFilterNames['Certification'].push("PG-13");
	    
	      asFilters['Certification'].push("Certification_TV-14_1");
	      asFilterNames['Certification'].push("TV-14");
	    
	      asFilters['Certification'].push("Certification_R_1");
	      asFilterNames['Certification'].push("R");
	    
	      asFilters['Certification'].push("Certification_Not Rated_1");
	      asFilterNames['Certification'].push("Not Rated");
	    
	      asFilters['Certification'].push("Certification_14_1");
	      asFilterNames['Certification'].push("14");
	    
	      asFilters['Certification'].push("Certification_15_1");
	      asFilterNames['Certification'].push("15");
	    
	      asFilters['Certification'].push("Certification_M_1");
	      asFilterNames['Certification'].push("M");
	    
	      asFilters['Certification'].push("Certification_TV-MA_1");
	      asFilterNames['Certification'].push("TV-MA");
	    
	      asFilters['Certification'].push("Certification_16_1");
	      asFilterNames['Certification'].push("16");
	    
	      asFilters['Certification'].push("Certification_12_1");
	      asFilterNames['Certification'].push("12");
	    
	      asFilters['Certification'].push("Certification_16+_1");
	      asFilterNames['Certification'].push("16+");
	    
	      asFilters['Certification'].push("Certification_M$2F16_1");
	      asFilterNames['Certification'].push("M/16");
	    
	      asFilters['Certification'].push("Certification_N-13_1");
	      asFilterNames['Certification'].push("N-13");
	    
	      asFilters['Certification'].push("Certification_12A_1");
	      asFilterNames['Certification'].push("12A");
	    
	      asFilters['Certification'].push("Certification_TV-Y_1");
	      asFilterNames['Certification'].push("TV-Y");
	    
	      asFilters['Certification'].push("Certification_M$2F12_1");
	      asFilterNames['Certification'].push("M/12");
	    
	      asFilters['Certification'].push("Certification_18_1");
	      asFilterNames['Certification'].push("18");
	    
	      asFilters['Certification'].push("Certification_NR_1");
	      asFilterNames['Certification'].push("NR");
	    
	      asFilters['Certification'].push("Certification_14A_1");
	      asFilterNames['Certification'].push("14A");
	    
	      asFilters['Certification'].push("Certification_U_1");
	      asFilterNames['Certification'].push("U");
	    
	      asFilters['Certification'].push("Certification_C_1");
	      asFilterNames['Certification'].push("C");
	    
	      asFilters['Certification'].push("Certification_13_1");
	      asFilterNames['Certification'].push("13");
	    
	      asFilters['Certification'].push("Certification_18+_1");
	      asFilterNames['Certification'].push("18+");
	    
	      asFilters['Certification'].push("Certification_AL_1");
	      asFilterNames['Certification'].push("AL");
	    
	      asFilters['Certification'].push("Certification_12+_1");
	      asFilterNames['Certification'].push("12+");
	    
	    asCatNames.push("Year");
	    asFilters['Year'] = new Array();
	    asFilterNames['Year']  = new Array();
	    
	      asFilters['Year'].push("Year_1920-29_1");
	      asFilterNames['Year'].push("1920-29");
	    
	      asFilters['Year'].push("Year_1930-39_1");
	      asFilterNames['Year'].push("1930-39");
	    
	      asFilters['Year'].push("Year_1940-49_1");
	      asFilterNames['Year'].push("1940-49");
	    
	      asFilters['Year'].push("Year_1950-59_1");
	      asFilterNames['Year'].push("1950-59");
	    
	      asFilters['Year'].push("Year_1960-69_1");
	      asFilterNames['Year'].push("1960-69");
	    
	      asFilters['Year'].push("Year_1970-79_1");
	      asFilterNames['Year'].push("1970-79");
	    
	      asFilters['Year'].push("Year_1980-89_1");
	      asFilterNames['Year'].push("1980-89");
	    
	      asFilters['Year'].push("Year_1990-99_1");
	      asFilterNames['Year'].push("1990-99");
	    
	      asFilters['Year'].push("Year_2000-09_1");
	      asFilterNames['Year'].push("2000-09");
	    
	      asFilters['Year'].push("Year_2010-19_1");
	      asFilterNames['Year'].push("2010-19");
	    
	      asFilters['Year'].push("Year_Last Year_1");
	      asFilterNames['Year'].push("Last Year");
	    
	      asFilters['Year'].push("Year_This Year_1");
	      asFilterNames['Year'].push("This Year");
	    
  
  var aiCatLength = new Object();
  var iCats = asCatNames.length;
  for (var t=0; t < iCats; t++)
  {
    aiCatLength[asCatNames[t]] = asFilters[asCatNames[t]].length;
  }

//</script>end options

function showMenu()
{
    hide(iActiveItem);
    //filter.css showMenu
    document.styleSheets[3].cssRules[0].style.visibility="visible";
    document.getElementById('body').setAttribute('background', 'images/filter/filter_background.gif');
    window.setTimeout("setFocus('catLink5')", 1);
    fMenu = true;

}
function hideMenu()
{
    //filter.css showMenu
    document.styleSheets[3].cssRules[0].style.visibility="hidden";
    //TODO: do I want to swap backgrounds, or alter menu.png to not have dark transparency
    //document.getElementById('body').setAttribute('background', 'pictures/'+ sBackground);
    document.getElementById('body').removeAttribute('background');
    fMenu = false;
    //TODO: setting focus back to active menu item...?
    //setFocus('x'+iActiveItem);
}

function toggleMenu()
{
  if (!fMenu)
    showMenu();
  else
    hideMenu();
}
function toggleMenuLinks()
{
  if (!fMenu)
    document.getElementById('body').removeAttribute('class');
  else
    document.getElementById('body').setAttribute('class', 'menu');
}

function setFocus(id)
{
  document.getElementById(id).focus();
}

function showCatLinks()
{
  var i = 0;

	if (asCatNames.length > 4 && iActiveCat + 4 >= iMaxCats)
    i = iActiveCat - 4;

  for (var t = 1; t <= iMaxCats; t++)
  {
    var sCat = ' ';

		// beim Zeiger iLinkMinCat Kategorien auf Inhalt prüfen 
    if (t >= iLinkMinCat && i < asCatNames.length)
    {
      if (asCatNames[i] != 'undefined')
      {
      	// Kategorie nur noch füllen, wenn sie Inhalt hat
        if (aiCatLength[asCatNames[i]] > 0)
        {
        	sCat = '     ' + asCatNames[i];
        }
        i++;
      }
    }
		document.getElementById('catSpan' + t).firstChild.nodeValue = sCat;
  }
  showGenres();
}

function showGenres()
{
  // Zähler für
  var i = 0;
  // Maxwert für Schleife
  var iMaxGen = iMaxGenres;
  // Anzahl Genres in dieser Kategorie
  iGenres = aiCatLength[sActiveCat];
  if (iGenres == 0)
   return false;


	// Falls mehr als 4 Genres vorhanden sind, heißt dass, dass man oben rausscrollen muss
  if ( iGenres > 4 && iActiveGen + 4 >= iMaxGen)
    i = iActiveGen - 4;

  for (var t = 1; t <= iMaxGen; t++)
  {

    var sGen = '';
    if (t >= iLinkMinGen && i < iGenres)
    {
      if (asFilterNames[sActiveCat][i] != 'undefined')
      {
        sGen ='     ' + asFilterNames[sActiveCat][i];
        i++;
      }
    }

    document.getElementById('genSpan' + t).firstChild.nodeValue = sGen;
    if (i -1 == iActiveGen)
    {
      document.getElementById('genLink5').setAttribute('href', asFilters[sActiveCat][i-1] + '.html');
    }
  }
}
function catDown()
{
  // letzte Kategorie
  if (iActiveCat + 1 >= asCatNames.length)
  {
    iActiveCat = -1;
    iLinkMinCat = 6;
  }

  
  iActiveCat = iActiveCat + 1;

  if (iLinkMinCat - 1 > 0 )
  {
    iLinkMinCat = iLinkMinCat - 1;
  }

  sActiveCat = asCatNames[iActiveCat];
  initGenres();
  showCatLinks();

}
function catUp()
{
  // erste Kategorie
  if (iActiveCat == 0 )
  {
    iActiveCat = asCatNames.length;
    var iTemp = 5 - asCatNames.length;
    iLinkMinCat = 1;
    if (iTemp > 0)
    	iLinkMinCat =  iTemp;
    else
    	iLinkMinCat = 1;
  }

  if (iActiveCat + 1 - 5 <= 0 )
  {
    iLinkMinCat = iLinkMinCat + 1;
  }

  iActiveCat = iActiveCat - 1;

  sActiveCat = asCatNames[iActiveCat];
  initGenres();
  showCatLinks();

}
function initGenres()
{
  iActiveGen = 0;
  iLinkMinGen = 5;
}
function genDown()
{

  // letzte Kategorie
  if (iActiveGen + 1 >= iGenres )
  {
    iActiveGen = -1;
    // um iLinkMinGen = 5 zu bekommen, muss es hier auf 6 gesetzt werden
    // iLinkMinGen = 5 ist wichtig für die Schleife bei showGenres()
    // beim ersten Durchlauf ( und genau da wollen wir hin ) muss iLinkMinGen = 5 sein
    iLinkMinGen = 6;
  }
  iActiveGen = iActiveGen + 1;
	
  if (iLinkMinGen - 1 > 0 )
  {
    iLinkMinGen = iLinkMinGen - 1;
  }

  showGenres();
}
function genUp()
{
  // erste Kategorie
  if (iActiveGen == 0 )
  {
    iActiveGen = aiCatLength[sActiveCat];
    var iTemp = 5 - iActiveGen;
    
    if (iTemp > 0)
    	iLinkMinGen =  iTemp;
    else
    	iLinkMinGen = 1;
  }

  if (iActiveGen + 1 - 5 <= 0 )
  {
    iLinkMinGen = iLinkMinGen + 1;
  }

  iActiveGen = iActiveGen - 1;

  showGenres();
}

function initMenu()
{
  if ((iLinkMinCat + asCatNames.length) < iMaxCats)
    iMaxCats = iLinkMinCat + asCatNames.length;
    

  for (var t = 0; t < asCatNames.length; t++)
  {
  	if (asCatNames[t] == sActiveCat)
  	{
  		iActiveCat = t;
  		if (5 - t >= 1)
  			iLinkMinCat = 5 - iActiveCat;
			else
    		iLinkMinCat = 1
  		break;
  	} 
  }

  showCatLinks();
  showGenres();
};