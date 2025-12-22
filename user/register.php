<?php
include '../_base.php';

// ----------------------------------------------------------------------------
$_err = [];
if (is_post()) {
    $email    = req('email');
    $password = req('password');
    $confirm  = req('confirm');
    $name     = req('name');
    $phone    = req('phone');
    $address  = req('address');
    $f = get_file('photo');

    // Validate: email
    if (!$email) {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_unique($email, 'users', 'email')) {
        $_err['email'] = 'Duplicated';
    }

    // Validate: password
    if (!$password) {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    // Validate: name
    if (!$name) {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    // Validate: phone
    if (!$phone) {
        $_err['phone'] = 'Required';
    }
    else if (strlen($phone) > 20) {
        $_err['phone'] = 'Maximum 20 characters';
    }
    else if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $phone)) {
        $_err['phone'] = 'Invalid phone number';
    }
    else if (!is_unique($phone, 'users', 'user_phone')) {
        $_err['phone'] = 'Duplicated';
    }

    // Validate: address
    if (!$address) {
        $_err['address'] = 'Required';
    }
    else if (strlen($address) > 255) {
        $_err['address'] = 'Maximum 255 characters';
    }

    // Validate: photo (file)
    if (!$f) {
        $_err['photo'] = 'Required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    // DB operation
    if (!$_err) {
        // (1) Save photo
        $photo = save_photo($f, '../photos');
        
        // (2) Insert user (member)
        $stm = $_db->prepare('
            INSERT INTO users (email, user_password, username, user_photo, user_role, user_phone, user_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $hashedPassword = sha1($password);       
        $stm->execute([$email, $hashedPassword, $name, $photo, 'customer', $phone, $address]);

        temp('info', 'Registration successful! Please login.');
        redirect('/login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BookStore</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            /* height: 100%; */
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
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 50px 30px 40px;
        }
        .register-header h1 {
            margin: 0 0 10px;
            font-size: 2.2rem;
        }
        .register-header p {
            margin: 0;
            opacity: 0.9;
        }
        .register-form {
            padding: 40px 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
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
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
        }
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
            border: 2px dashed #ccc;
            border-radius: 8px;
            text-align: center;
            padding: 20px;
            cursor: pointer;
        }
        .file-upload-label {
            color: #667eea;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: #667eea;
            font-weight: bold;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .error-msg {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }
        @media (max-width: 768px) {
            .register-form { padding: 30px 20px; }
            .register-header { padding: 40px 20px 30px; }
            .register-container { padding: 15px; }
        }
        @media (max-width: 480px) {
            .register-header h1 { font-size: 1.8rem; }
            .register-form { padding: 25px 15px; }
        }
    </style>
</head>
<body>

    <?php include '../head.php'; ?>

    <div class="main-content">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h1>Create Your Account</h1>
                    <p>Join BookStore and explore endless stories!</p>
                </div>

                <form method="post" class="register-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" maxlength="100" 
                               placeholder="Enter your email" required
                               value="<?= encode($_POST['email'] ?? '') ?>">
                        <?php if ($_err['email'] ?? false): ?>
                            <span class="error-msg"><?= $_err['email'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" maxlength="100" 
                               placeholder="At least 5 characters" required>
                        <?php if ($_err['password'] ?? false): ?>
                            <span class="error-msg"><?= $_err['password'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" id="confirm" name="confirm" maxlength="100" 
                               placeholder="Re-enter password" required>
                        <?php if ($_err['confirm'] ?? false): ?>
                            <span class="error-msg"><?= $_err['confirm'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" maxlength="100" 
                               placeholder="Your full name" required
                               value="<?= encode($_POST['name'] ?? '') ?>">
                        <?php if ($_err['name'] ?? false): ?>
                            <span class="error-msg"><?= $_err['name'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" maxlength="20" 
                               placeholder="e.g., 012-3456789" required
                               value="<?= encode($_POST['phone'] ?? '') ?>">
                        <?php if ($_err['phone'] ?? false): ?>
                            <span class="error-msg"><?= $_err['phone'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" maxlength="255" 
                                  placeholder="Enter your full address" required><?= encode($_POST['address'] ?? '') ?></textarea>
                        <?php if ($_err['address'] ?? false): ?>
                            <span class="error-msg"><?= $_err['address'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="photo">Profile Photo</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <span class="file-upload-label">
                                <i class="fas fa-upload"></i> Upload Photo (Max 1MB)
                            </span>
                        </div>
                        <p class="file-hint">JPG, PNG, GIF - 1MB max</p>
                        <?php if ($_err['photo'] ?? false): ?>
                            <span class="error-msg"><?= $_err['photo'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="submit-btn">Register</button>

                    <div class="login-link">
                        Already have an account? <a href="../login.php">Login here</a>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>