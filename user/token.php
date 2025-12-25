<?php
include '../_base.php';

// ----------------------------------------------------------------------------

// (1) Delete expired tokens
$_db->query('DELETE FROM token WHERE expire < NOW()');

$id = req('id');

// (2) Is token id valid?
if (!is_exists($id, 'token', 'id')) {
    temp('info', 'Invalid token. Try again');
    redirect('/');
}

if (is_post()) {
    $password = req('password');
    $confirm  = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    // DB operation
    if (!$_err) {
        // Update user (password) based on token id + delete token
        $stm = $_db->prepare('
            UPDATE users
            SET user_password = SHA1(?)
            WHERE user_id = (SELECT user_id FROM token WHERE id = ?);

            DELETE FROM token WHERE id = ?;
        ');
        $stm->execute([$password, $id, $id]);

        temp('info', 'Record updated');
        redirect('/login.php');
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIX SEVEN BS</title>
    <link rel ="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <div class="main-content">
        <div class="reset-container">
            <div class="reset-card">
                <div class="reset-header">
                    <h1>Reset Your Password</h1>
                    <p>Please enter your new password below</p>
                </div>

                <form method="post" class="reset-form" action="token.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" maxlength="100" placeholder="Enter new password" required value="<?= htmlspecialchars(req('password')) ?>">
                        <?= err('password') ? '<div class="error-msg">' . htmlspecialchars($_err['password']) . '</div>' : '' ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" id="confirm" name="confirm" maxlength="100" placeholder="Confirm new password" required value="<?= htmlspecialchars(req('confirm')) ?>">
                        <?= err('confirm') ? '<div class="error-msg">' . htmlspecialchars($_err['confirm']) . '</div>' : '' ?>
                    </div>

                    <button type="submit" class="submit-btn">Update Password</button>
                    <button type="reset" class="reset-btn">Clear</button>

                    <div class="login-link">
                        <a href="../login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>
</html>

<?php
