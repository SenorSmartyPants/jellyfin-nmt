<?php

class FilterMenu 
{

    public static function printGenDivs($type)
    {
        //this generates static ouput
        if ($type == 'category') {
            $divId = 'catDiv';
            $divClass = 'category';
            $LinkId = 'catLink';
            $SpanId = 'catSpan';
        } else {
            $divId = 'genDiv';
            $divClass = 'genre';
            $LinkId = 'genLink';
            $SpanId = 'genSpan';
        }

        for ($i=1; $i <= 9; $i++) { 
            echo "\n<div id=\"$divId$i\" class=\"abs $divClass mnuItem mnuLine$i showMenu\">";
            if ($i == 5) {
                echo "<a href=\"#\" id=\"$LinkId$i\" name=\"$LinkId$i\" onkeydownset=\"{$LinkId}Down\" onkeyupset=\"{$LinkId}Up\" ";
                if ($type == 'category') {
                    echo 'onclick="setFocus(\'genLink5\'); return false;" onkeyrightset="genLink5" onkeyleftset="genLink5"';
                } else {
                    echo "onclick=\"openLink('$LinkId$i'); return false;\" onkeyrightset=\"catLink5\" onkeyleftset=\"catLink5\"";
                }
                echo '><img src="images/filter/menu_link.png" ONFOCUSSRC="images/filter/menu_active.png" class="menuImage" border="0" />';
                echo '</a>';
            }
    
            echo "<span id=\"$SpanId$i\" class=\"menuLink" . ($i == 5 ? 'Active' : '') . "\">&#160;</span>";      
            echo "</div>";
        }
    
    }

    public static function printFooter()
    {
    ?>
        <img id="menu" class="abs mnuBack showMenu" src="images/filter/menu.png" />
    <?
        filterMenu::printGenDivs('category');
        filterMenu::printGenDivs('genre');
    ?>              
        <img class="abs mnuBackTop showMenu" src="images/filter/mnu_top.png" />
        <img class="abs mnuBackBot showMenu" src="images/filter/mnu_bottom.png" />
    <?
    }

}
?>