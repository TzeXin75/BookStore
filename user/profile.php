<?php
// profile.php - Fully edited, secure, beautiful user profile page

// ALL PHP CODE AT THE TOP - BEFORE ANY HTML OUTPUT!
include '../_base.php';  // This handles session_start(), auth(), $_db, etc.

$_title = 'My Profile';

// Force login (redirect if not authenticated)
auth();

// Default photo
$photo = 'default.jpg';

// Fetch current user data (GET or after update)
if (is_get() || is_post()) {


    $stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
    $stm->execute([$_user['user_id']]);
    $u = $stm->fetch(PDO::FETCH_OBJ);  // Use object for consistency

    if (!$u) {
        temp('info', 'User not found.');
        redirect('../index.php');
    }

    $username     = $u->username ?? '';
    $email        = $u->email ?? '';
    $user_phone   = $u->user_phone ?? '';
    $user_address = $u->user_address ?? '';
    $photo        = $u->user_photo ?? 'default.jpg';

    $_SESSION['photo'] = $photo;  // Update session photo
}

// Handle form submission (POST)
if (is_post()) {
    $username     = req('username');
    $user_phone   = req('user_phone');
    $user_address = req('user_address');

    // Validation
    $_err = [];
    if (empty($username)) $_err['username'] = 'Full name is required';
    if (!empty($user_phone) && !preg_match('/^\+?\d{10,15}$/', $user_phone)) {
        $_err['user_phone'] = 'Invalid phone number';
    }

    // File upload handling (profile photo)
    if (!empty($_FILES['photo']['name'])) {
        $upload_dir = '../photos/';
        $file_ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'gif'];
        $new_name   = uniqid() . '.' . $file_ext;

        if ($_FILES['photo']['error'] === UPLOAD_ERR_OK &&
            in_array($file_ext, $allowed) &&
            $_FILES['photo']['size'] <= 2 * 1024 * 1024) {  // 2MB max

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_name)) {
                $photo = $new_name;
                // Delete old photo if not default
                if ($old_photo = $u->user_photo ?? '' && $old_photo !== 'default.jpg') {
                    @unlink($upload_dir . $old_photo);
                }
            } else {
                $_err['photo'] = 'Failed to upload photo';
            }
        } else {
            $_err['photo'] = 'Invalid file type or size (max 2MB)';
        }
    }

    // Update database if no errors
    if (empty($_err)) {
        $stm = $_db->prepare('
            UPDATE users 
            SET username = ?, user_phone = ?, user_address = ?, user_photo = ?
            WHERE user_id = ?
        ');
        $stm->execute([$username, $user_phone, $user_address, $photo, $_user->user_id]);

        temp('info', 'Profile updated successfully!');
        redirect('profile.php');  // Refresh to show updated data
    }
}

// If we reach here, safe to output HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BookStore</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
        }
        .profile-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px 40px;
            text-align: center;
            position: relative;
        }
        .profile-photo-wrapper {
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid white;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-header h1 {
            margin: 0 0 10px;
            font-size: 2.2rem;
        }
        .profile-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .profile-form-section {
            padding: 60px 40px 40px;
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
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }
        .file-input {
            margin-top: 10px;
        }
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-msg {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
            font-size: 1rem;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .profile-container { margin: 30px 15px; }
            .profile-header { padding: 50px 20px 30px; }
            .profile-form-section { padding: 40px 20px 20px; }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-photo-wrapper">
            <img src="../photos/<?= htmlspecialchars($photo) ?>" alt="Profile Photo" class="profile-photo">
        </div>
        <h1><?= htmlspecialchars($username) ?></h1>
        <p><?= htmlspecialchars($email) ?></p>
    </div>

    <div class="profile-form-section">
        <?php if ($msg = temp('info')): ?>
            <div class="success-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Full Name</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                <?php if (err('username')): ?><span class="error-msg"><?= $_err['username'] ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?= htmlspecialchars($email) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="user_phone">Phone Number</label>
                <input type="tel" id="user_phone" name="user_phone" value="<?= htmlspecialchars($user_phone) ?>">
                <?php if (err('user_phone')): ?><span class="error-msg"><?= $_err['user_phone'] ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="user_address">Address</label>
                <input type="text" id="user_address" name="user_address" value="<?= htmlspecialchars($user_address) ?>">
            </div>

            <div class="form-group">
                <label for="photo">Profile Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*" class="file-input">
                <?php if (err('photo')): ?><span class="error-msg"><?= $_err['photo'] ?></span><?php endif; ?>
                <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">Max 2MB (JPG, PNG, GIF)</p>
            </div>

            <button type="submit" class="submit-btn">Update Profile</button>
        </form>

        <div class="back-link">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>
</div>

</body>
</html>