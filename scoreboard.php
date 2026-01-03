<?php include('class/action.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Scoreboard</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Football Quiz scoreboard"/>
    <link rel="stylesheet" href="/css/home-style.css"/>
</head>
<body>
<header>
    <h1>Football Quiz Scoreboard</h1>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="quiz.php">Take the Quiz</a></li>
            <li>
                <a href="scoreboard.php">View the Scoreboard</a>
            </li>
            <?php if (isset($_SESSION['userid']) && $_SESSION['usertype'] == "user") { ?>
                <li><a href="scoreboard.php?logoutUser=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>">LogOut</a></li>
            <?php } else { ?>
                <li><a href="login.php">Log In</a></li>
                <li><a href="register.php">Register</a></li>
            <?php } ?>
        </ul>
    </nav>
</header>
<main>
    <table class="scoreboard-table">
        <thead>
        <tr>
            <th>User</th>
            <th>Correct Answer</th>
            <th>Wrong Answer</th>
            <th>Incomplete Answer</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sqlQuery = "SELECT res.id, us.name, res.correct_answer,res.wrong_answer,res.incomplete_answer,res.result_date  FROM `user_quiz_result` AS res INNER JOIN users AS us ON res.user_id = us.id";
        $resultSet = mysqli_query($dbConnect, $sqlQuery);
        $datas = mysqli_fetch_all($resultSet, MYSQLI_ASSOC);
        ?>
        <?php
        if (!empty($datas) && count($datas) > 0) {
            foreach ($datas as $key => $data) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['correct_answer'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['wrong_answer'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['incomplete_answer'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['result_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php }
        } ?>
        <!-- Add more rows for other users -->
        </tbody>
    </table>
</main>
<footer>
    <p>© <span id="currentYear"></span> Football Quiz</p>
</footer>
<script src="/js/Home-script.js"></script>
</body>
</html>
