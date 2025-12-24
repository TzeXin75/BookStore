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
            <p>From, BookStore Admin</p>
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
    <title>Reset Password - BookStore</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(231,76,60,0.2);
        }
        .error-msg {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 8px;
            display: block;
        }
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
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
            color: #e74c3c;
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
            .reset-container { padding: 15px; }
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