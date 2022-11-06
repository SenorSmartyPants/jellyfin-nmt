<?php
require_once 'secrets.php';
require_once 'data.php';
require_once 'playbackReporting.php';

class Authentication
{
    public $user_id;
    public $userIDs;

    private const USERIDS = 'userIDs';

    private function setUserID($ID)
    {
        global $user_id;
        $user_id = $ID;
        $this->user_id = $ID;
    }

    public function __construct()
    {
        // Start the session
        session_start();
        $this->setUserID($_SESSION[self::USERIDS][0]);
        $this->userIDs = $_SESSION[self::USERIDS];
    }

    public function IsAuthenticated()
    {
        return (isset($_SESSION['accessToken']));
    }

    public function logout()
    {
        //clear playstate for this session
        $pr = new PlaybackReporting($_SESSION['ID'], 0, 0);
        $pr->deletePlaystate();

        $this->setUserID(null);

        if ($this->IsAuthenticated()) {
            apiCallPost(
                '/Sessions/Logout/',
                array('non' => 'empty')
            );
        }

    }

    public function login($userID, $username)
    {
        // end any other sessions that may be active
        self::logout();

        $post = array(
            'Username' => $username
        );

        $result = apiCallPost('/Users/AuthenticateByName', $post);

        $_SESSION['accessToken'] = $result->AccessToken;
        $_SESSION['ID'] = $result->SessionInfo->Id;
        $this->setUserID($userID);
    }

    public function login2($userIDs, $userNames)
    {
        foreach ($userIDs as $index => $user) {
            if ($index == 0) {
                self::login($user, $userNames[0]);
            } else {
                self::addUserToSession($_SESSION['ID'], $user);
            }
        }
        $_SESSION[self::USERIDS] = $userIDs;
        $this->userIDs = $userIDs;
    }

    //verify NMT and JF sessions contain the same users
    public function verifySession($fixSession = false)
    {
        # get JF sessions
        $sessionID = $_SESSION['ID'];
        $JFSessions = apiCall("/Sessions?");
        # find NMT session match
        $JFSessions = array_filter($JFSessions, function ($s) use ($sessionID) {
            return $s->Id == $sessionID;
        });
        $JFSession = array_values($JFSessions)[0];

        #make list of JF session userIDs
        $JFUserIDs[] = $JFSession->UserId;
        foreach ($JFSession->AdditionalUsers as $user) {
            $JFUserIDs[] = $user->UserId;
        }

        # verify user and additional users
        $userListsAreEqual = $JFUserIDs === $_SESSION[self::USERIDS];

        if (!$userListsAreEqual && $fixSession) {
            # add additional NMT users if not present in JF
            $missingUsers = array_diff($_SESSION[self::USERIDS], $JFUserIDs);
            foreach ($missingUsers as $user) {
                self::addUserToSession($sessionID, $user);
            }
            $userListsAreEqual = true;
        }

        return $userListsAreEqual;
    }

    private function addUserToSession($sessionID, $userID)
    {
        //response is empty, 200 if successful
        apiCallPost(
            '/Sessions/' . $sessionID . '/User/' . $userID,
            array('non' => 'empty'));
    }
}
