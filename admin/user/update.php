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
    <title>Update User - Admin Dashboard</title>
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

    <?php
    $sqlQuery = "SELECT id, name, email FROM users";
    $resultSet = mysqli_query($dbConnect, $sqlQuery);
    $datas = mysqli_fetch_all($resultSet, MYSQLI_ASSOC);
    ?>
    <h1>Update User</h1>
    <h3> <?php echo isset($error) ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : ""; ?></h3>
    <div class="select-question">
        <label for="selectUser">Select User:</label>
        <select id="selectUser" name="selectUser" onchange="getUserData();">
            <option value="">Select User</option>
            <?php
            if (!empty($datas) && count($datas) > 0) {
                foreach ($datas as $key => $data) {
                    ?>
                    <option value="<?php echo $data['id']; ?>" <?php if (isset($_GET['updateUserId']) && $_GET['updateUserId'] == $data['id']) {
                        echo "selected";
                    } ?>><?php echo htmlspecialchars($data['name'] . "(" . $data['email'] . ")", ENT_QUOTES, 'UTF-8'); ?></option>
                <?php }
            } ?>
        </select>
    </div>
    <form action="update.php" method="post" class="form-user">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" value="<?php echo isset($_GET['updateUserId']) ? $_GET['updateUserId'] : ""; ?>"
               name="updateUserId">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username"
                     value="<?php echo !empty($result) ? htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8') : ""; ?>" required/>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
                 <input type="email" id="email" name="email" value="<?php echo !empty($result) ? htmlspecialchars($result['email'], ENT_QUOTES, 'UTF-8') : ""; ?>"
                   required/>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
                 <input type="password" id="password" name="password" value="" placeholder="Leave blank to keep current password"/>
        </div>
        <div class="form-group">
            <label for="userType">User Type</label>
            <select id="userType" name="userType">
                <option value="">Select User Type</option>
                <option value="admin" <?php echo !empty($result) && $result['type'] == "admin" ? "selected" : ""; ?>>
                    Admin
                </option>
                <option value="user" <?php echo !empty($result) && $result['type'] == "user" ? "selected" : ""; ?>>
                    User
                </option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" value="Update User" name="updateNewUser"/>
        </div>
    </form>
</main>

<footer>
    <p>© <span id="currentYear"></span> Football Quiz</p>
</footer>
<script src="/js/admin-script.js"></script>
<script>
    function getUserData() {
        selectElement = document.querySelector('#selectUser');
        output = selectElement.value;
        window.location.href = "update.php?updateUserId=" + output + "";
    }
</script>
<script>
    function logout() {
        window.location.href = "/admin/index.php?logoutAdmin=true&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>";
    }
</script>
</body>
</html>
