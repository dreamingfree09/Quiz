<?php include('../class/action.php'); ?>
<?php if (isset($_SESSION['userid']) && $_SESSION['usertype'] == "user") {
} else {
    header("location: login.php");
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Football Quiz</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
    <meta name="description" content="Football Quiz for testing your knowledge about football history." />
    <meta name="keywords" content="football, quiz, history" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/home-style.css" />
</head>

<body>
    <header>
        <h1>Football Quiz!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="quiz.php">Take the Quiz</a></li>
                <li>
                    <a href="scoreboard.php">View the Scoreboard</a>
                </li>
                <?php if (isset($_SESSION['userid']) && $_SESSION['usertype'] == "user") { ?>
                    <li><a href="quiz.php?logoutUser=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>">LogOut</a></li>
                <?php } else { ?>
                    <li><a href="login.php">Log In</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php } ?>
            </ul>
        </nav>
    </header>
    <main>
        <section id="quiz-container" class="quiz-container">
            <h2>Football History</h2>
            <button type="button" id="startQuiz">Start Quiz</button>

            <form id="quizForm">
                <div id="quizProgress" currentIndex="0"></div>
                <div id="timer"></div>
                <div class="question-container" id="question-0" hidden></div>
                <button type="button" id="nextQuestion" hidden>Next Question</button>
            </form>
            <div class="quiz-results" id="quiz-results" hidden></div>
        </section>
    </main>
    <footer>
        <p>© <span id="currentYear"></span> Football Quiz</p>
    </footer>
    <script src="../js/quiz.js"></script>
</body>

</html>