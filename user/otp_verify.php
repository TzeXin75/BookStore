<?php
include '../_base.php';

$_err = [];
if (!isset($_SESSION['pending_user'])) {
    redirect('register.php');
}

$pending = $_SESSION['pending_user'];

if (time() > $pending['expires']) {
    unset($_SESSION['pending_user']);
    temp('info', 'OTP expired. Please register again.');
    redirect('register.php');
}

if (is_post()) {
    $otp_input = req('otp');

    if (!$otp_input) {
        $_err['otp'] = 'Required';
    } elseif ($otp_input != $pending['otp']) {
        $_err['otp'] = 'Invalid OTP';
    }

    if (!$_err) {
        // Activate user
        $_db->prepare('UPDATE users SET user_status = 1 WHERE user_id = ?')
            ->execute([$pending['user_id']]);

        unset($_SESSION['pending_user']);
        temp('info', 'Registration successful! You can now log in.');
        redirect('/login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - SIX SEVEN BS</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <?php include '../head.php'; ?>

    <div class="main-content">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h1>Verify Your Email</h1>
                    <p>We sent a 6-digit code to <?= htmlspecialchars($pending['email']) ?></p>
                </div>

                <form method="post" class="register-form">
                    <div class="register-form-group">
                        <label for="otp">Enter Verification Code</label>
                        <input type="text" id="otp" name="otp" maxlength="6" placeholder="123456" required autocomplete="off">
                        <?php if ($_err['otp'] ?? false): ?>
                            <span class="error-msg"><?= $_err['otp'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="register-submit-btn">Verify & Complete Registration</button>
                </form>
            </div>
        </div>
    </div>

    <div id="footer-placeholder"></div>

    <script>
    fetch('../footer.html')
        .then(r => r.text())
        .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>
</body>
</html>