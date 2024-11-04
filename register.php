<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="main-content">
        <div class="cube-container">
            <div class="cube">
                <!-- Login Form -->
                <div class="face front">
                    <h2>Login</h2>
                    <form id="login-form" action="login.php" method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                        <button type="submit">Login</button>
                        
                        <p>Don't have an account? <button type="button" id="signup-btn">Sign Up</button></p>
                    </form>
                </div>

                <!-- Signup Form -->
                <div class="face back">
                    <h2>Sign Up</h2>
                    <form id="signup-form" action="signup.php" method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="company_name" placeholder="Company Name" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        <button type="submit">Sign Up</button>
                        <p>Already have an account? <button type="button" id="login-btn">Login</button></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('signup-btn').addEventListener('click', function() {
            document.querySelector('.cube').classList.add('rotated');
        });

        document.getElementById('login-btn').addEventListener('click', function() {
            document.querySelector('.cube').classList.remove('rotated');
        });
    </script>
</body>
</html>
