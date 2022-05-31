<?php
require_once __DIR__.'/boot.php';


if (strcasecmp($_POST['type'], 'signup') == 0) {

    $validArr = validateData(
        $_POST['type'], 
        $_POST['username'], 
        $_POST['password'], 
        $_POST['email'], 
        $_POST['repeatPassword'], 
        $_POST['checkPrivacyPolicy']
    );
    displayErrors($validArr);

    $stmt = pdo();
    $stmtRes = $stmt->prepare("SELECT * FROM users WHERE username = :username");
    $stmtRes->execute(['username' => $_POST['username']]);

    if ($stmtRes->rowCount() > 0) {
        printMessage(array('Error' => "This username is busy."), 400);
    }

    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = gen_AccesToken();

    $stmt->prepare("INSERT INTO users SET username = :username, email = :email, pass_hash = :passHash, token = :token")->execute([
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'passHash' => $passHash,
        'token' => $token,
    ]);

    $_SESSION['token'] = $token;
    printMessage(array('Username' => $_POST['username'], 'Token' => $token));
        
} elseif (strcasecmp($_POST['type'], 'login') == 0) {

        $validArr = validateData(
            $_POST['type'],
            $_POST['username'],
            $_POST['password'],
        );

        displayErrors($validArr);

        $stmt = pdo()->prepare("SELECT * FROM users WHERE username = :username");
        $result = $stmt->execute(['username' => $_POST['username']]);

        if (!$result) {
            printMessage(array('Error' => "An error has occurred on the server, we are already working on it."), 500);
        }

        if ($stmt->rowCount() == 0) {
            printMessage(array('Error' => "The user does not exist"), 400);
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($_POST['password'], $user['pass_hash'])) {

            $token = gen_AccesToken();
            $_SESSION['token'] = $token;

            $stmt = pdo()->prepare("UPDATE users SET token=:token WHERE username = :username");
            $result = $stmt->execute([
                "token" => $token,
                "username" => $user['username']
            ]);
            if ($result) {
                printMessage(array('Username' => $user['username'], 'Token' => $token));
            } else {
                printMessage(array('Error' => "An error has occurred on the server, we are already working on it."), 500);
            }
            

        } else {
            printMessage(array('Error' => "Invalid password"), 400);
        }

} elseif (strcasecmp($_POST['type'], 'logout') == 0) {

    if (check_token() || isset($_POST['token'])) {

        $stmt = pdo()->prepare("UPDATE users SET token=:token WHERE token=:logOutToken");
        $stmt->execute([
            'token' => "",
            'logOutToken' => $_POST['token'] ?? ($_SESSION['token'] ?? "")
        ]);

        $_SESSION = [];
        printMessage("");

    } else {

        printMessage(array('Error' => "The token must be present"), 400);

    }

} elseif (strcasecmp($_POST['type'], 'createnote') == 0) {
    
    if (check_token() || isset($_POST['token'])) {
        
        $stmt = pdo()->prepare("SELECT id FROM users WHERE token=:token");
        $stmt->execute(['token' => $_POST['token'] ?? ($_SESSION['token'] ?? "")]);
        $userId = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$userId) {
            printMessage(array('Error' => "User not found"), 400);
        }
        if (!isset($_POST['text']) || strlen($_POST['text']) < 10) {
            printMessage(array('Error' => 'The "text" field should not be empty, or more than 10 characters'), 400);
        }

        $stmt = pdo()->prepare("INSERT INTO `usersnotes` (`id`, `userid`, `name`, `text`) VALUES (NULL, :userid, :nameNotes, :textNotes)");
        $result = $stmt->execute([
            'userid' => $userId['id'],
            'nameNotes' => (strlen($_POST['name'] ?? "") == 0) ? "Note" : $_POST['name'],
            'textNotes' => $_POST['text']
        ]);
        if($result) {
            printMessage("");
        } else {
            printMessage(array('Error' => "An error has occurred on the server, we are already working on it."), 500);
        }
    } else {
        printMessage(array('Error' => "You are not logged in"), 400);
    }

} elseif (strcasecmp($_POST['type'], 'deletenote') == 0) {

    if (check_token() || isset($_POST['token'])) {
        
        $stmt = pdo()->prepare("SELECT id FROM users WHERE token=:token");
        $stmt->execute(['token' => $_POST['token'] ?? ($_SESSION['token'] ?? "")]);
        $userId = $stmt-> fetch(PDO::FETCH_ASSOC);

        $stmt = pdo()->prepare("SELECT * FROM usersnotes WHERE id = :id");
        $result = $stmt->execute(['id' => $_POST['id']]);

        if (!$result) {
            printMessage(array('Error' => 'Note not found'), 404);
        }

        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        if (strcasecmp($note['userid'], $userId['id']) !== 0) {
            printMessage(array('Error' => "Access denied"), 400);
        }

        $stmt = pdo()->prepare("DELETE FROM usersnotes WHERE id = :id AND userid = :userid");
        $result = $stmt->execute(['id' => $note['id'], 'userid' => $userId['id']]);

        if ($result) {
            printMessage("Successful deletion of a note");
        } else {
            printMessage(array('Error' => "An error has occurred on the server, we are already working on it."), 500);
        }
    } else {
        printMessage(array('Error' => "You are not logged in"), 400);
    }

} else {

    printMessage(array('Error' => "Unknown request"), 400);

}


/**
 * Validate a data for other things (log in or suign up)
 * @param string $type of request (login or signup)
 * @param string $username
 * @param string $password
 * @param string $email maybe null
 * @param string $repeatPass
 * @param string $checkPP its flag for cheking Privacy Polis
 * @return array $validArr with bool values displays exactly where the error is
 */
function validateData($type, $username, $password, $email = null, $repeatPass = null, $checkPP = null)
{
    $returnArray = array();

    $usernamePattern = '/^\w+$/';
    $emailPattern = '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/';

    switch ($type) {
        case 'signup':

            $resultPreg = preg_match($usernamePattern, $username);

            if ($resultPreg === 1 && strlen($username) <= 18) {
                $returnArray['username'] = true;
            } elseif ($resultPreg === 0 || strlen($username) > 18) {
                $returnArray['username'] = false;
            } else {
               $returnArray['username'] = 500; 
            }

            $resultPreg = preg_match($emailPattern, $email);

            if ($resultPreg === 1) {
                $returnArray['email'] = true;
            } elseif ($resultPreg === 0) {
                $returnArray['email'] = false;
            } else {
               $returnArray['email'] = 500; 
            }

            if (strlen($password) <= 15) {
                $returnArray['password'] = true;
            } else {
                $returnArray['password'] = false;
            }

            if (strcasecmp($password, $repeatPass) === 0) {
                $returnArray['repeatPassword'] = true;
            } else {
                $returnArray['repeatPassword'] = false;
            }
            break;
        case 'login':
                
            $resultPreg = preg_match($usernamePattern, $username);

            if ($resultPreg === 1 && strlen($username) <= 18) {
                $returnArray['username'] = true;
            } elseif ($resultPreg === 0 || strlen($username) > 18) {
                $returnArray['username'] = false;
            } else {
               $returnArray['username'] = 500; 
            }

            
            if (strlen($password) <= 15) {
                $returnArray['password'] = true;
            } else {
                $returnArray['password'] = false;
            }

            break;
        default:
            printMessage(array( 'Error' => 'Not a valid response type'), 422);
            break;
    }
    return $returnArray;
}

/**
 * Displays errors that occurred during validation
 * @param array $validArr with bool values displays exactly where the error is
 * @return void
 */
function displayErrors($validArr) {
    $notValid = array_keys($validArr, false);
    $serverErr = array_keys($validArr, 500, true);
    if (!empty($notValid) || !empty($serverErr)) {
        $errText = "";
        if ((!empty($notValid)) && empty($serverErr)) {
            
            foreach ($notValid as $value) {
                $errText .= "Incorrect {$value}. ";
            }
            printMessage(array('Error' => $errText), 400);
        } 

        foreach ($serverErr as $value) {
            $errText .= "Failed to verify {$value} correctness we are already working on a server error. ";
        }
        printMessage(array('Error' => $errText), 500);
    }
}

/**
 * Generates a pseudo-random sting for token
 * @return string $token a pseudo-random string
 */
function gen_AccesToken() {
	$token = md5(microtime() . 'dF2f5g' . time());
	return $token;
}
/**
 * Display a message (or error)
 * @param string $message display message to user
 * @param int $code set status code
 * @return void
 */
function printMessage($message, $code = 200)
{
    http_response_code($code);

    if (is_array($message)) {
        echo json_encode($message);
    } else {
        echo json_encode(array('Message' => $message));
    }

    die;
}