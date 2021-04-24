<?
include_once 'utils/javascript.php';
include_once 'utils/arrayCallbacks.php';

class CheckinJS
{
    public static function render($items, $selectedIndex = null)
    {
        CheckinJS::PrintIncludes();
        CheckinJS::printCheckinArrays($items, $selectedIndex);
    }

    public static function getCallback(SkipAndTrim $skipTrim, $videoIndex = null)
    {
        if (isset($videoIndex)) {
            $callbackJS = "iEpisodeId = $videoIndex; ";
        }
        $callbackJS .= "checkin(asItemId[iEpisodeId], asItemDuration[iEpisodeId], asItemPosition[iEpisodeId], $skipTrim->skipSeconds, $skipTrim->trimSeconds);";
        return $callbackJS;
    }

    private static function printCheckinArrays($items, $selectedIndex)
    {
        $selectedIndex = $selectedIndex ?? 1;
?>
        <script type="text/javascript">
            var iEpisodeId = <?= $selectedIndex ?>;

            //checkin variables
            var asItemId = <?= getJSArray(array_column($items, 'Id'), true, '0') ?>;
            var asItemDuration = <?= getJSArray(array_map('getRuntimeSeconds', $items), false, '0') ?>;
            var asItemPosition = <?= getJSArray(array_map('getStartPosition', $items), false, '0') ?>;
        </script>
    <?
    }

    private static function PrintIncludes()
    {
    ?>
        <script type="text/javascript" src="js/empty.js" id="checkinjs"></script>
        <script type="text/javascript">
            function checkin(itemId, duration, position, skip, trim) {
                position = ResumeOrRestart(position, skip);
                var url = "checkin.php?id=" + itemId + "&duration=" + duration + "&position=" + position + "&skip=" + skip + "&trim=" + trim;
                document.getElementById("checkinjs").setAttribute('src', url + "&JS=true");
            }

            function stop() {
                document.getElementById("checkinjs").setAttribute('src', 'checkin.php?action=stop&JS=true');
            }

            function callback(id, inlineMsg) {
                document.getElementById("checkinjs").setAttribute('src', "js/empty.js");
            }

            function updatePosition(position) {
                asItemPosition[iEpisodeId] = position;
            }

            function ResumeOrRestart(iResume, iStart) {
                if (iResume == iStart) return iStart;
                if (confirm("Report restarting?")) {
                    return iStart;
                } else {
                    return iResume;
                }
            }
        </script>
<?
    }
}
?>
