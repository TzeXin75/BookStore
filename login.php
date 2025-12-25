<?php
include '_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $email = req('email');
    $password = req('password');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }

    // Login user
    if (!$_err) {
        // Check user credentials
        $stm = $_db->prepare('
            SELECT * FROM users
            WHERE email = ? AND user_password = SHA1(?)
        ');
        
        $stm->execute([$email, $password]);
        $user = $stm->fetch(PDO::FETCH_ASSOC); // <--- STEP 1: USER IS DEFINED HERE

        if ($user) {
            // --- [FIXED CODE IS HERE] ---
            // If user_status is 0 (Inactive)
            if ($user['user_status'] == 0) {
                // We MUST pass the message in the URL (?info=...) so the HTML can read it from $_GET['info']
                redirect('login.php?info=Your account is inactive. Please contact the administrator.');
            }
            // -----------------------------

            temp('info', 'Login successfully');

            // Determine where to redirect based on role
            if ($user['user_role'] === 'admin'|| $user['user_role'] === 'member') {
                login($user, 'index.php');     
            }
        }
        else {
            $_err['password'] = 'Invalid email or password';
        }
    }
}

// ----------------------------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIX SEVEN BS</title>
    <link rel="stylesheet" href="style.css" />
   
    
</head>
<body>
    <?php include 'head.php'; ?>

    <div class="login-container">
        <div class="login-box">
            <h2>Welcome to SIX SEVEN BS</h2>
            <p class="login-subtitle">Login to your account</p>
            
            <?php if (isset($_GET['info'])): ?>
                <div class="success-box" style="padding: 10px; background: #d1fae5; color: #065f46; border-radius: 5px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($_GET['info']); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post">
                <div class="login-form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           maxlength="100" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                    <?php if (isset($_err['email'])): ?>
                        <div style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">
                            <?php echo $_err['email']; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="login-form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           maxlength="100" required>
                    <?php if (isset($_err['password'])): ?>
                        <div style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">
                            <?php echo $_err['password']; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="login-form-group" style="margin-top: 1.5rem; display: flex; gap: 10px;">
                    <button type="submit" class="login-btn">Login</button>
                    <button type="reset" class="login-reset-btn">Reset</button>
                </div>
                
                <div class="login-links">
                    <a href="user/reset.php" id="forgot-password">Forgot Password?</a>
                </div>

                <div class="register-link">
                    Don't have an account? <a href="/user/register.php">Register here</a>
                </div>
            </form>
        </div>
    </div>

    <div id="footer-placeholder"></div>
    <script>
    fetch('footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>