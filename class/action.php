<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    // SameSite=Lax keeps typical navigation working while reducing CSRF risk.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
require 'db_connect.php';

function redirect_and_exit(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(?string $token): bool
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $token === null) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Ensure token exists for templates that include this file.
csrf_token();

function is_admin(): bool
{
    return isset($_SESSION['userid'], $_SESSION['usertype']) && $_SESSION['usertype'] === 'admin';
}

function is_user(): bool
{
    return isset($_SESSION['userid'], $_SESSION['usertype']) && $_SESSION['usertype'] === 'user';
}

function get_post_string(string $key): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function get_get_int(string $key): ?int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value <= 0) {
        return null;
    }
    return (int)$value;
}

function get_post_int(string $key): ?int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value <= 0) {
        return null;
    }
    return (int)$value;
}

function verify_password_and_upgrade(mysqli $dbConnect, int $userId, string $inputPassword, string $storedPassword): bool
{
    $info = password_get_info($storedPassword);
    if (!empty($info['algo'])) {
        return password_verify($inputPassword, $storedPassword);
    }

    // Legacy plaintext compatibility: if it matches, upgrade it to a hash.
    if (hash_equals($storedPassword, $inputPassword)) {
        $newHash = password_hash($inputPassword, PASSWORD_DEFAULT);
        if ($newHash !== false) {
            $stmt = $dbConnect->prepare('UPDATE users SET password = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('si', $newHash, $userId);
                $stmt->execute();
                $stmt->close();
            }
        }
        return true;
    }

    return false;
}


// Login Admin User and Set the Session for Authentication
if (isset($_POST['loginButton'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    $email = get_post_string('email');
    $password = get_post_string('password');

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Login Failed';
    } else {
        $stmt = $dbConnect->prepare("SELECT id, name, email, password, type FROM users WHERE email = ? AND status = 1 AND type = 'admin' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $userDetails = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($userDetails && verify_password_and_upgrade($dbConnect, (int)$userDetails['id'], $password, (string)$userDetails['password'])) {
                session_regenerate_id(true);
                $_SESSION['userid'] = (int)$userDetails['id'];
                $_SESSION['usertype'] = $userDetails['type'];
                $_SESSION['name'] = $userDetails['name'];
                $_SESSION['email'] = $userDetails['email'];
                redirect_and_exit('../admin');
            }
        }

        $error = 'Login Failed';
    }

    }

}

// Create Quiz and also check if quiz Question already present in database
if (isset($_POST['createQuizButton'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $question = get_post_string('question');
        $optionA = get_post_string('optionA');
        $optionB = get_post_string('optionB');
        $optionC = get_post_string('optionC');
        $optionD = get_post_string('optionD');
        $correctAnswer = filter_input(INPUT_POST, 'correctAnswer', FILTER_VALIDATE_INT);
        if ($question === '' || $optionA === '' || $optionB === '' || $optionC === '' || $optionD === '' || $correctAnswer === false || $correctAnswer === null || $correctAnswer < 0 || $correctAnswer > 3) {
            $error = 'Invalid input';
        } else {
            $stmt = $dbConnect->prepare('SELECT id FROM quizzes WHERE question = ? LIMIT 1');
            $stmt->bind_param('s', $question);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!empty($existing)) {
                $error = 'Question Already add in quizzes';
            } else {
                $options = $optionA . ',' . $optionB . ',' . $optionC . ',' . $optionD;
                $insert = $dbConnect->prepare('INSERT INTO quizzes (question, options, correctAnswer) VALUES (?, ?, ?)');
                $correctAnswerStr = (string)$correctAnswer;
                $insert->bind_param('sss', $question, $options, $correctAnswerStr);
                $quizSaved = $insert->execute();
                $insert->close();
                $error = $quizSaved ? 'Saved Successfully' : 'something went wrong';
            }
        }
    }
    }
}

// Get Quiz from database for update and also check if quiz Question present in database or not
if (isset($_GET['updateQuestionId'])) {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $questionId = get_get_int('updateQuestionId');
        if ($questionId === null) {
            $error = 'Question Not Found';
        } else {
            $stmt = $dbConnect->prepare('SELECT * FROM quizzes WHERE id = ?');
            $stmt->bind_param('i', $questionId);
            $stmt->execute();
            $resultSet = $stmt->get_result();
            $result = $resultSet ? $resultSet->fetch_assoc() : null;
            $stmt->close();
            if (empty($result)) {
                $error = 'Question Not Found';
            }
        }
    }
}


// Update Quiz
if (isset($_POST['updateQuizButton'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $questionId = get_post_int('updateQuestionId');
        $question = get_post_string('question');
        $optionA = get_post_string('optionA');
        $optionB = get_post_string('optionB');
        $optionC = get_post_string('optionC');
        $optionD = get_post_string('optionD');
        $correctAnswer = filter_input(INPUT_POST, 'correctAnswer', FILTER_VALIDATE_INT);

        if ($questionId === null || $question === '' || $optionA === '' || $optionB === '' || $optionC === '' || $optionD === '' || $correctAnswer === false || $correctAnswer === null || $correctAnswer < 0 || $correctAnswer > 3) {
            $error = 'Invalid input';
        } else {
            $stmt = $dbConnect->prepare('SELECT id FROM quizzes WHERE question = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $question, $questionId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!empty($existing)) {
                $error = 'Question Already add in quizzes';
            } else {
                $options = $optionA . ',' . $optionB . ',' . $optionC . ',' . $optionD;
                $correctAnswerStr = (string)$correctAnswer;
                $update = $dbConnect->prepare('UPDATE quizzes SET question = ?, options = ?, correctAnswer = ? WHERE id = ?');
                $update->bind_param('sssi', $question, $options, $correctAnswerStr, $questionId);
                $isUpdated = $update->execute();
                $update->close();
                $error = $isUpdated ? 'Updated Successfully' : 'something went wrong';
            }
        }
    }
    }
}

// Delete quiz from database
if (isset($_GET['deleteQuestionId'])) {
    if (!csrf_verify(isset($_GET['csrf']) ? (string)$_GET['csrf'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $questionId = get_get_int('deleteQuestionId');
        if ($questionId === null) {
            $error = 'Invalid request';
        } else {
            $stmt = $dbConnect->prepare('DELETE FROM quizzes WHERE id = ?');
            $stmt->bind_param('i', $questionId);
            $deleted = $stmt->execute();
            $stmt->close();
            if ($deleted) {
                $error = 'Record deleted successfully';
                redirect_and_exit('delete.php');
            }
            $error = 'Error deleting record';
        }
    }
    }
}

// Create new user
if (isset($_POST['createNewUser'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $username = get_post_string('username');
        $email = get_post_string('email');
        $password = get_post_string('password');
        $userType = get_post_string('userType');

        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || ($userType !== 'admin' && $userType !== 'user')) {
            $error = 'Invalid input';
        } else {
            $stmt = $dbConnect->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!empty($existing)) {
                $error = 'Email Already Exist.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                if ($passwordHash === false) {
                    $error = 'something went wrong';
                } else {
                    $insert = $dbConnect->prepare('INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, ?)');
                    $insert->bind_param('ssss', $username, $email, $passwordHash, $userType);
                    $userSaved = $insert->execute();
                    $insert->close();
                    $error = $userSaved ? 'Saved User Successfully' : 'something went wrong';
                }
            }
        }
    }
    }
}


// Get User and also check if user present in database or not
if (isset($_GET['updateUserId'])) {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $userId = get_get_int('updateUserId');
        if ($userId === null) {
            $error = 'User Not Found';
        } else {
            $stmt = $dbConnect->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $resultSet = $stmt->get_result();
            $result = $resultSet ? $resultSet->fetch_assoc() : null;
            $stmt->close();
            if (empty($result)) {
                $error = 'User Not Found';
            }
        }
    }
}


// Update User and also check if User is present in database or not
if (isset($_POST['updateNewUser'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $userId = get_post_int('updateUserId');
        $username = get_post_string('username');
        $email = get_post_string('email');
        $password = get_post_string('password');
        $userType = get_post_string('userType');

        if ($userId === null || $username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || ($userType !== 'admin' && $userType !== 'user')) {
            $error = 'Invalid input';
        } else {
            $stmt = $dbConnect->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $email, $userId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!empty($existing)) {
                $error = 'User email Already Exist';
            } else {
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    if ($passwordHash === false) {
                        $error = 'something went wrong';
                    } else {
                        $update = $dbConnect->prepare('UPDATE users SET name = ?, email = ?, password = ?, type = ? WHERE id = ?');
                        $update->bind_param('ssssi', $username, $email, $passwordHash, $userType, $userId);
                        $isUpdated = $update->execute();
                        $update->close();
                        $error = $isUpdated ? 'Updated Successfully' : 'something went wrong';
                    }
                } else {
                    $update = $dbConnect->prepare('UPDATE users SET name = ?, email = ?, type = ? WHERE id = ?');
                    $update->bind_param('sssi', $username, $email, $userType, $userId);
                    $isUpdated = $update->execute();
                    $update->close();
                    $error = $isUpdated ? 'Updated Successfully' : 'something went wrong';
                }
            }
        }
    }
    }
}

// Delete user from database
if (isset($_GET['deleteUserId'])) {
    if (!csrf_verify(isset($_GET['csrf']) ? (string)$_GET['csrf'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $userId = get_get_int('deleteUserId');
        if ($userId === null) {
            $error = 'Invalid request';
        } else {
            $stmt = $dbConnect->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $deleted = $stmt->execute();
            $stmt->close();
            if ($deleted) {
                $error = 'Record deleted successfully';
                redirect_and_exit('delete.php');
            }
            $error = 'Error deleting record';
        }
    }
    }
}


// Register User and also login as well automatically
if (isset($_POST['registerUser'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    $name = get_post_string('username');
    $email = get_post_string('email');
    $password = get_post_string('password');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'something went wrong';
    } else {
        $stmt = $dbConnect->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!empty($existing)) {
            $error = 'Email Already Exist.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                $error = 'something went wrong';
            } else {
                $insert = $dbConnect->prepare("INSERT INTO users (name, email, password, type, status) VALUES (?, ?, ?, 'user', 1)");
                $insert->bind_param('sss', $name, $email, $passwordHash);
                $quizSaved = $insert->execute();
                $newId = $insert->insert_id;
                $insert->close();

                if ($quizSaved) {
                    session_regenerate_id(true);
                    $_SESSION['userid'] = (int)$newId;
                    $_SESSION['usertype'] = 'user';
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    redirect_and_exit('quiz.php');
                }

                $error = 'something went wrong';
            }
        }
    }
    }
}

// Login User and set the Authentication Session for Login
if (isset($_POST['loginUserButton'])) {
    if (!csrf_verify(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
        $error = 'Invalid request';
    } else {
    $email = get_post_string('username');
    $password = get_post_string('password');

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Login Faild';
    } else {
        $stmt = $dbConnect->prepare("SELECT id, name, email, password, type FROM users WHERE email = ? AND status = 1 AND type = 'user' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $userDetails = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($userDetails && verify_password_and_upgrade($dbConnect, (int)$userDetails['id'], $password, (string)$userDetails['password'])) {
                session_regenerate_id(true);
                $_SESSION['userid'] = (int)$userDetails['id'];
                $_SESSION['usertype'] = $userDetails['type'];
                $_SESSION['name'] = $userDetails['name'];
                $_SESSION['email'] = $userDetails['email'];
                redirect_and_exit('quiz.php');
            }
        }

        $error = 'Login Faild';
    }

    }

}

//Delete User Quiz from database
if (isset($_GET['deleteResultId'])) {
    if (!csrf_verify(isset($_GET['csrf']) ? (string)$_GET['csrf'] : null)) {
        $error = 'Invalid request';
    } else {
    if (!is_admin()) {
        $error = 'Unauthorized';
    } else {
        $resultId = get_get_int('deleteResultId');
        if ($resultId === null) {
            $error = 'Invalid request';
        } else {
            $stmt = $dbConnect->prepare('DELETE FROM user_quiz_result WHERE id = ?');
            $stmt->bind_param('i', $resultId);
            $deleted = $stmt->execute();
            $stmt->close();
            if ($deleted) {
                $error = 'Record deleted successfully';
                redirect_and_exit('view.php');
            }
            $error = 'Error deleting record';
        }
    }
    }
}
// Logout Admin
if (isset($_GET['logoutAdmin'])) {
    if (!csrf_verify(isset($_GET['csrf']) ? (string)$_GET['csrf'] : null)) {
        $error = 'Invalid request';
    } else {
    session_destroy();
    redirect_and_exit('login.php');
    }
}

// Logout User
if (isset($_GET['logoutUser'])) {
    if (!csrf_verify(isset($_GET['csrf']) ? (string)$_GET['csrf'] : null)) {
        $error = 'Invalid request';
    } else {
    session_destroy();
    redirect_and_exit('login.php');
    }
}


?>
