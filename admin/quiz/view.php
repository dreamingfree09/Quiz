<?php include('../../class/action.php'); ?>
<?php if (isset($_SESSION['userid']) && $_SESSION['usertype'] == "admin") {
} else {
    header("location: ../login.php");
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>View Quizzes - Admin Dashboard</title>
    <link rel="stylesheet" href="../../css/admin-style.css"/>
</head>
<body>
<div class="sidebar">
    <a href="/admin" class="home-btn">Home</a>

    <!-- Quiz Admin Dropdown -->
    <div class="dropdown">
        <button class="dropdown-btn">QUIZ</button>
        <div class="dropdown-content">
            <a href="/admin/quiz/create.php">Create Quiz</a>
            <a href="/admin/quiz/view.php">View Quizzes</a>
            <a href="/admin/quiz/update.php">Update Quiz</a>
            <a href="/admin/quiz/delete.php">Delete Quiz</a>
        </div>
    </div>

    <!-- User Admin Dropdown -->
    <div class="dropdown">
        <button class="dropdown-btn">USER</button>
        <div class="dropdown-content">
            <a href="/admin/user/create.php">Create User</a>
            <a href="/admin/user/view.php">View User</a>
            <a href="/admin/user/update.php">Update Users</a>
            <a href="/admin/user/delete.php">Delete User</a>
        </div>
    </div>

    <!-- Scoreboard Admin Dropdown -->
    <div class="dropdown">
        <button class="dropdown-btn">SCOREBOARD</button>
        <div class="dropdown-content">
            <a href="/admin/scoreboard/view.php">View Scoreboard</a>
        </div>
    </div>

    <div class="dropdown">
        <button type="button" class="dropdown-btn" onclick="logout();">Logout</button>

    </div>

</div>


<main>
    <h1>View Quiz Questions</h1>
    <table class="quiz-table">
        <thead>
        <tr>
            <th>Question</th>
            <th>Options</th>
            <th>Correct Answer</th>
        </tr>
        </thead>
        <?php
        $sqlQuery = "SELECT * FROM quizzes";
        $resultSet = mysqli_query($dbConnect, $sqlQuery);
        $datas = mysqli_fetch_all($resultSet, MYSQLI_ASSOC);
        ?>
        <tbody>
        <?php
        if (!empty($datas) && count($datas) > 0) {
            foreach ($datas as $key => $data) { ?>

                <tr>
                    <td><?php echo htmlspecialchars($data['question'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php
                    $optionString = "";
                    $correctAnswer = "";
                    if (isset($data['options']) && $data['options'] != "") {
                        $options = explode(',', $data['options']);
                        $keys = ['A', 'B', 'C', 'D'];
                        if (!empty($options) && count($options) > 0) {
                            foreach ($options as $key => $option) {
                                if ($optionString == "") {
                                    $optionString = "" . $keys[$key] . ") " . $option . "";
                                } else {
                                    $optionString = $optionString . ",  " . $keys[$key] . ") " . $option . "";
                                }
                            }

                            $correctAnswer = $keys[$data['correctAnswer']] . ") " . $options[$data['correctAnswer']];
                        }
                    }

                    ?>
                    <td><?php echo htmlspecialchars($optionString, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($correctAnswer, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php }
        } ?>
        </tbody>
    </table>
</main>

<footer>
    <p>© <span id="currentYear"></span> Football Quiz</p>
</footer>
<script src="/js/admin-script.js"></script>
<script>
    function logout() {
        window.location.href = "/admin/index.php?logoutAdmin=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>";
    }
</script>
</body>
</html>
