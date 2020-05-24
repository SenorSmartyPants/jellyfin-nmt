<?php
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
    public $LastPositionUpdate;

    public function __construct($itemId, $duration) {
        $this->itemId = $itemId;
        $this->Duration = $duration;       
    }
}

class PlaybackReporting  
{
    private const PROGRESSUPDATEFREQUENCY = 60;
    private $playing;
    private $sessionId;

    public function __construct($sessionId, $itemId, $duration) {
        $this->sessionId = $sessionId;
        $this->playing = new PlayingMedia($itemId, $duration);   
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
        file_put_contents("playstate/" . $this->sessionId . ".json", json_encode($this->playing));
    }

    private function loadPlaystate()
    {
        $this->playing = json_decode(file_get_contents("playstate/" . $this->sessionId . ".json"));
    }
    
    public function deletePlaystate()
    {
        unlink("playstate/" . $this->sessionId . ".json");
    }

    private function calculateCurrentPosition()
    {
        $secondsSinceUpdate = time() - $this->playing->LastPositionUpdate;

        $this->playing->PositionInSeconds += $secondsSinceUpdate;
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
            if ($this->playing->PlayState == PlayState::PLAYING) 
            {
                if (self::calculateCurrentPosition() >= $this->playing->Duration)
                {
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

        self::apiJSON(
            '/Sessions/Playing',
            self::getPlaybackPayload(),
            $this->playing->PositionInSeconds
        );

        //keep sending progress updates until duration or stop from another thread
        self::handleProgess();
    }
    
    public function Stop()
    {
        //get updated position from session
        self::loadPlaystate();
        if ($this->playing->PlayState == PlayState::PLAYING) 
        {
            $this->playing->PositionInSeconds = self::calculateCurrentPosition();
            self::apiJSON(
                '/Sessions/Playing/Stopped',
                self::getPlaybackPayload(),
                $this->playing->PositionInSeconds,
                PlayState::STOPPED
            );
        }
    }

    private function apiJSON($apiendpoint, $payload, $PositionInSeconds, $playState = PlayState::PLAYING)
    {
        apiCallPost(
            $apiendpoint,
            json_encode($payload),
            'application/json'
        );
    
        self::updatePlaystate($PositionInSeconds, $playState);    
    }
}
?>