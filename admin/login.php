<?php include('../class/action.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Page</title>
    <meta name="description" content="Football Quiz login page"/>
    <meta name="keywords" content="football, quiz, login"/>
    <link rel="stylesheet" href="../css/home-style.css"/>
</head>
<body>
<main>
    <div class="login-container">
        <form action="login.php" method="post" class="login-form" role="form">
            <input type="hidden" name="type" value="adminLogin">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
            <h3> <?php echo isset($error) ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : ""; ?></h3>
            <h2>Login to Your Account</h2>
            <div class="form-group">
            <label for="login-email">Email</label>
                <input
                        type="text"
                id="login-email"
                        name="email"
                        required
                aria-label="Email"
                oninput="validateLoginEmail()"
                />
            </div>
            <div class="form-group">
            <label for="login-password">Password</label>
                <input
                        type="password"
                id="login-password"
                        name="password"
                        required
                        aria-label="Password"
                oninput="validateLoginPassword()"
                />
            </div>
            <button type="submit" name="loginButton" class="login-button">Log In</button>
        </form>
    </div>
</main>
<script src="../js/login.js"></script>
</body>
</html>
