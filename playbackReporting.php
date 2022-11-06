<?php
require_once 'auth.php';
require_once 'data.php';

abstract class PlayState
{
    const PLAYING = 'Playing';
    const STOPPED = 'Stopped';
    const PAUSED = 'Paused';
}

class PlayingMedia
{
    public $itemId;
    public $Duration;
    public $PlayState;
    public $PositionInSeconds;
    public $StartedTime;
    public $LastPositionUpdate;
    public $skipSeconds;
    public $trimSeconds;

    public function __construct($itemId, $duration, $skipSeconds, $trimSeconds)
    {
        $this->itemId = $itemId;
        $this->Duration = $duration;
        $this->skipSeconds = $skipSeconds;
        $this->trimSeconds = $trimSeconds;
    }
}

class PositionAndPlayed
{
    public $PositionInSeconds;
    public $Played;
}

class PlaybackReporting
{
    private const PROGRESSUPDATEFREQUENCY = 60;
    private const PLAYSTATEDIR = 'playstate/';
    private const JSONEXT = '.json';

    private const MINRESUME = 0.05;
    private const MAXRESUME = 0.90;

    private $playing;
    private $sessionId;

    public function __construct($sessionId, $itemId, $duration, $skipSeconds = 0, $trimSeconds = 0)
    {
        $this->sessionId = $sessionId;
        $this->playing = new PlayingMedia($itemId, $duration, $skipSeconds, $trimSeconds);
    }

    private function getPlaybackPayload($eventName = null)
    {
        $playback = array(
            'CanSeek' => true,
            'ItemId' => $this->playing->itemId,
            'PlayMethod' => 'DirectPlay'
        );

        if ($eventName) {
            $playback['EventName'] = $eventName;
        }

        if ($this->playing->PositionInSeconds) {
            //ticks = ten millionth of a second
            $ticks = $this->playing->PositionInSeconds * 10000 * 1000;
            $playback['PositionTicks'] = $ticks;
        }

        return $playback;
    }

    private function updatePlaystate($PositionInSeconds, $playState = PlayState::PLAYING)
    {
        $this->playing->PositionInSeconds = $PositionInSeconds;
        $this->playing->PlayState = $playState;
        $this->playing->LastPositionUpdate = time();

        //save state to file
        file_put_contents(self::PLAYSTATEDIR . $this->sessionId . self::JSONEXT, json_encode($this->playing));
    }

    private function loadPlaystate()
    {
        $this->playing = json_decode(file_get_contents(self::PLAYSTATEDIR . $this->sessionId . self::JSONEXT));
    }

    public function deletePlaystate()
    {
        $filepath = self::PLAYSTATEDIR . $this->sessionId . self::JSONEXT;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    private function calculateCurrentPosition()
    {
        $this->playing->PositionInSeconds = time() - $this->playing->StartedTime;
        $this->playing->LastPositionUpdate = time();

        return $this->playing->PositionInSeconds;
    }

    private function Progress($PositionInSeconds, $eventName = 'TimeUpdate')
    {
        self::apiJSON(
            '/Sessions/Playing/Progress',
            self::getPlaybackPayload($eventName),
            $PositionInSeconds
        );
    }

    private function handleProgess()
    {
        //this is the periodic update so JF won't time out - run once a PROGRESSUPDATEFREQUENCY seconds

        ignore_user_abort(true);
        set_time_limit(0);

        do {
            sleep(self::PROGRESSUPDATEFREQUENCY);
            //reload PlayState from disk
            self::loadPlaystate();
            if ($this->playing->PlayState == PlayState::PLAYING) {
                if (self::calculateCurrentPosition() >= $this->playing->Duration) {
                    //video not stopped, but after the end of the video, send stop message to JF
                    self::Stop();
                } else {
                    //send keep alive progress message
                    self::Progress(self::calculateCurrentPosition());
                }
            }
        } while ($this->playing->PlayState == PlayState::PLAYING);
    }

    public function Start($PositionInSeconds = 0)
    {
        $this->playing->PositionInSeconds = $PositionInSeconds;
        $this->playing->PlayState = PlayState::PLAYING;
        $this->playing->StartedTime = time() - $PositionInSeconds;

        self::apiJSON(
            '/Sessions/Playing',
            self::getPlaybackPayload(),
            $this->playing->PositionInSeconds
        );

        //keep sending progress updates until duration or stop from another thread
        self::handleProgess();
    }

    public function Stop(): PositionAndPlayed
    {
        //get updated position from session
        self::loadPlaystate();

        $auth = new Authentication();
        $auth->verifySession(true);

        if ($this->playing->PlayState == PlayState::PLAYING) {
            $this->playing->PositionInSeconds = self::calculateCurrentPosition();
            //add trim seconds to current position
            $trimmedPosition = $this->playing->PositionInSeconds + $this->playing->trimSeconds;
            //if result is >90% resume
            if ($trimmedPosition / $this->playing->Duration >= PlaybackReporting::MAXRESUME) {
                //set current position to be trimmed position
                $this->playing->PositionInSeconds = $trimmedPosition;
            }

            if (($this->playing->PositionInSeconds - $this->playing->skipSeconds) <= ($this->playing->Duration * PlaybackReporting::MINRESUME)) {
                //in first 5% of video (excluding skipSeconds), don't save resume position
                //report position with skipSeconds removed
                $this->playing->PositionInSeconds -= $this->playing->skipSeconds;
            }

            self::apiJSON(
                '/Sessions/Playing/Stopped',
                self::getPlaybackPayload(),
                $this->playing->PositionInSeconds,
                PlayState::STOPPED
            );
        }

        $retval = new PositionAndPlayed();

        //look up played state for this item
        $playedItem = getItem($this->playing->itemId);
        $retval->Played = $playedItem->UserData->Played;

        //report skipSeconds to NMT if at beginning of video
        //+1 because position will be set to 1 if in first 5%
        if ($this->playing->PositionInSeconds <= $this->playing->skipSeconds + 1) {
            $retval->PositionInSeconds = $this->playing->skipSeconds;
        } else {
            $retval->PositionInSeconds = $this->playing->PositionInSeconds;
        }

        return $retval;
    }

    private function apiJSON($apiendpoint, $payload, $PositionInSeconds, $playState = PlayState::PLAYING)
    {
        apiCallPost($apiendpoint, $payload);

        self::updatePlaystate($PositionInSeconds, $playState);
    }
}
