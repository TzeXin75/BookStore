<?php 
    include '../_base.php';

    if (is_post()) {
    $email = req('email');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_exists($email, 'users', 'email')) {
        $_err['email'] = 'Not exists';
    }

    // Send reset token (if valid)
    if (!$_err) {
        // TODO: (1) Select user
        $stm = $_db->prepare('SELECT * FROM users WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        // TODO: (2) Generate token id
        $id = sha1(uniqid() . rand());

        // TODO: (3) Delete old and insert new token
        $stm = $_db->prepare('
            DELETE FROM token WHERE user_id = ?;

            INSERT INTO token (id, expire, user_id)
            VALUES (?, ADDTIME(NOW(), "00:15"), ?);
        ');
        $stm->execute([$u->user_id, $id, $u->user_id]);

        // TODO: (4) Generate token url
        $url = base("user/token.php?id=$id");

        // TODO: (5) Send email
        $m = get_mail();
        $m->addAddress($u->email, $u->username);
        $m->isHTML(true);
        $m->Subject = 'Reset Password';
        $m->Body = "
            <p>Dear $u->username,<p>
            <h1 style='color: red'>Reset Password</h1>
            <p>
                Please click <a href='$url'>here</a>
                to reset your password.
            </p>
            <p>From, SIX SEVEN BS Admin</p>
        ";
        $m->send();

        temp('info', 'Email sent');
        redirect('/');
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIX SEVEN BS</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <div class="main-content">
        <div class="reset-container">
            <div class="reset-card">
                <div class="reset-header">
                    <h1>Forgot Password?</h1>
                    <p>Enter your email and we'll send you a link to reset your password</p>
                </div>
                
                

                <form method="post" class="reset-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" maxlength="100" placeholder="Enter your registered email" value="<?= htmlspecialchars(req('email')) ?>" required>
                        <?= err('email') ? '<div class="error-msg">' . htmlspecialchars($_err['email']) . '</div>' : '' ?>
                    </div>

                    <button type="submit" class="submit-btn">Send Reset Link</button>
                    <button type="reset" class="reset-btn">Clear</button>

                    <div class="login-link">
                        Remember your password? <a href="../login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
</body>
</html>