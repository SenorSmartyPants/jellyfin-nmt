<?php
require_once 'secrets.php';
require_once 'data.php';

class Authentication
{
    public function IsAuthenticated($userIDs)
    {
        session_start();
        return (isset($_SESSION['accessToken']) && $userIDs == $_SESSION['userIDs']);
    }
    
    public function logout()
    {
        //clear playstate for this session
        $pr = new PlaybackReporting($_SESSION['ID'], 0, 0);
        $pr->deletePlaystate();

        apiCallPost(
            '/Sessions/Logout/',
            array('non' => 'empty'));

    }

    public function login($userID)
    {
        // Start the session
        session_start();

        // end any other sessions that may be active
        self::logout();

        $post = array(
            'Pw' => '',
            'Password' => ''
        );
        
        $result = apiCallPost('/Users/' . $userID . '/Authenticate', $post);
    
        $_SESSION['accessToken'] = $result->AccessToken;
        $_SESSION['ID'] = $result->SessionInfo->Id;
    }

    public function login2($userIDs)
    {
        foreach ($userIDs as $index => $user) {
            if ($index == 0) {
                self::login($user);
            } else {
                self::addUserToSession($_SESSION['ID'], $user);
            }
        }
        $_SESSION['userIDs'] = $userIDs;
    }
    
    private function addUserToSession($sessionID, $userID)
    {
        //response is empty, 200 if successful
        apiCallPost(
            '/Sessions/' . $sessionID . '/Users/' . $userID,
            array('non' => 'empty'));
    }
}
?>