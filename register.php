<?php include('class/action.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registration Page</title>
    <link rel="stylesheet" href="/css/home-style.css"/>
</head>
<body>
<header>
    <h1>Football Quiz!</h1>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="quiz.php">Take the Quiz</a></li>
            <li>
                <a href="scoreboard.php">View the Scoreboard</a>
            </li>
            <li><a href="login.php">Log In</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>
</header>
<main>

    <h3> <?php echo isset($error) ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : ""; ?></h3>
    <section class="registration-container">
        <form
                class="registration-form"
                action="register.php"
                method="POST"
                enctype="multipart/form-data"
        >
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
            <h2>Create Your Account</h2>
            <div class="form-group">
                <label for="username">Username</label>
                <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        minlength="6"
                        maxlength="20"
                        autocomplete="username"
                        oninput="validateUsername()"
                />
                <div id="username-error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        maxlength="50"
                        autocomplete="email"
                        pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                        oninput="validateEmail()"
                />
                <div id="email-error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        maxlength="20"
                        oninput="validatePassword()"
                />
                <div id="password-error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input
                        type="password"
                        id="confirm-password"
                        name="confirm-password"
                        required
                        minlength="8"
                        maxlength="20"
                        oninput="validateConfirmPassword()"
                />
                <div id="confirm-password-error" class="error-message"></div>
            </div>
            <button type="submit" class="register-button" name="registerUser">Register</button>
            <p class="login-link">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </form>
    </section>
</main>
<footer>
    <p>© <span id="currentYear"></span> Football Quiz</p>
</footer>
<script src="/js/register.js"></script>
</body>
</html>
