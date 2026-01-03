<?php include('../class/action.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Home Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description"
        content="The Football Quiz is a fun and interactive way to test your knowledge of football." />
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
                    <li><a href="index.php?logoutUser=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>">LogOut</a></li>
                <?php } else { ?>
                    <li><a href="login.php">Log In</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php } ?>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>About the Quiz</h2>
            <p>
                The Football Quiz is a fun and interactive way to test your knowledge
                of football. Our quiz features questions on a wide range of topics,
                including the history of football, famous players, and memorable
                matches.
            </p>
        </section>
        <figure id="imageCarousel" class="carousel">
            <figcaption>Football is my life</figcaption>
        </figure>
        <section>
            <h2>How to Play</h2>
            <p>
                To play the quiz, simply click on the "Take the Quiz" link in the
                navigation bar. You will be presented with a series of multiple-choice
                questions. Choose the answer that you think is correct and click on
                the "Submit" button. At the end of the quiz, you will be shown your
                score and the correct answers to the questions.
            </p>
        </section>
        <section>
            <h2>About Us</h2>
            <p>
                The Football Quiz was created by a team of football enthusiasts who
                wanted to share their love of the game with others. Our goal is to
                provide a fun and engaging way for people to learn more about football
                and test their knowledge.
            </p>
        </section>
    </main>
    <footer>
        <p>© <span id="currentYear"></span> Football Quiz</p>
    </footer>
    <script src="../js/Home-script.js"></script>
</body>

</html>