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
    <title>Reset Password - BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            max-width: 500px;
            width: 100%;
        }

        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            text-align: center;
            padding: 50px 30px 40px;
        }

        .reset-header h1 {
            margin: 0 0 10px;
            font-size: 2.2rem;
        }

        .reset-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .reset-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231,76,60,0.2);
        }

        .error-msg {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 8px;
            display: block;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .reset-btn {
            width: 100%;
            background: #f1f1f1;
            color: #333;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 15px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: #666;
        }

        .login-link a {
            color: #2c3e50;
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .reset-form { padding: 30px 20px; }
            .reset-header { padding: 40px 20px 30px; }
        }

        @media (max-width: 480px) {
            .reset-header h1 { font-size: 1.8rem; }
            .reset-form { padding: 25px 15px; }
        }
    </style>
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
