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
    <title>Delete User - Admin Dashboard</title>
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
    <h1>Delete User</h1>

    <h3> <?php echo isset($error) ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : ""; ?></h3>

    <?php
    $sqlQuery = "SELECT id, name, email FROM users";
    $resultSet = mysqli_query($dbConnect, $sqlQuery);
    $datas = mysqli_fetch_all($resultSet, MYSQLI_ASSOC);
    ?>
    <div class="delete-quiz">
        <label for="deleteUser">Select User to Delete:</label>
        <select id="deleteUser" name="deleteUser">
            <!-- Options will be dynamically populated -->
            <option value="">Select User</option>
            <?php
            if (!empty($datas) && count($datas) > 0) {
                foreach ($datas as $key => $data) {
                    ?>
                    <option value="<?php echo $data['id']; ?>"><?php echo htmlspecialchars($data['name'] . "(" . $data['email'] . ")", ENT_QUOTES, 'UTF-8'); ?></option>
                <?php }
            } ?>
            <!-- Add other questions -->
        </select>
        <button type="button" onclick="deleteItem();">Delete</button>
    </div>
</main>

<footer>
    <p>© <span id="currentYear"></span> Football Quiz</p>
</footer>
<script src="/js/admin-script.js"></script>
<script>
    function deleteItem() {
        if (confirm("Are you sure you want to delete this row")) {
            selectElement = document.querySelector('#deleteUser');
            output = selectElement.value;
            window.location.href = "delete.php?deleteUserId=" + output + "&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>";
        }
    }
</script>
<script>
    function logout() {
        window.location.href = "/admin/index.php?logoutAdmin=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>";
    }
</script>
</body>
</html>
