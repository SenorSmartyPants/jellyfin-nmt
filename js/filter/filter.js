//functions from ppp3.1 YAMJ skin

var fMenu = false;
var fInit = false;

var iActiveCat = 0;
var iMaxCats = 9;
var iMaxGenres = 9;
var iLinkMinCat = 5;
var iLinkMinGen = 5;
var iActiveGen = 0;
var iGenres = 0;


  
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
    window.setTimeout("setFocus('catLink5')", 1);
    fMenu = true;

}
function hideMenu()
{
    //filter.css showMenu
    document.styleSheets[3].cssRules[0].style.visibility="hidden";
    fMenu = false;
    setFocus(iActiveItem);
    show(iActiveItem);
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

	if (iCats > 4 && iActiveCat + 4 >= iMaxCats)
    i = iActiveCat - 4;

  for (var t = 1; t <= iMaxCats; t++)
  {
    var sCat = ' ';

		// beim Zeiger iLinkMinCat Kategorien auf Inhalt prüfen 
    if (t >= iLinkMinCat && i < iCats)
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
      document.getElementById('genLink5').setAttribute('href', baseURL + asFilters[sActiveCat][i-1]);
    }
  }
}
function catDown()
{
  // letzte Kategorie
  if (iActiveCat + 1 >= iCats)
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
    iActiveCat = iCats;
    var iTemp = 5 - iCats;
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
  if ((iLinkMinCat + iCats) < iMaxCats)
    iMaxCats = iLinkMinCat + iCats;
    

  for (var t = 0; t < iCats; t++)
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