<?php
include '../_base.php';

// 1. Setup Page Info
$_title = 'My Profile';
include '../head.php'; 

// 2. Authentication Check
auth('member');

// 3. GET Request
if (is_get()) {
    $stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
    $stm->execute([$_user['user_id']]);
    $u = $stm->fetch();

    if (!$u) {
        redirect('/');
    }

    extract((array)$u); 
    $photo = $user_photo;
    $_SESSION['photo'] = $user_photo;
}

// 4. POST Request: Handle Updates
if (is_post()) {
    $username     = req('username');
    $email        = req('email');
    $user_phone   = req('user_phone');
    $user_address = req('user_address');
    $photo        = $_SESSION['photo'];
    $f            = get_file('photo');
    $user_role    = req('role');

    // --- Validation Logic ---
    
    // Email Validation
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?');
        $stm->execute([$email, $_user['user_id']]);
        if ($stm->fetchColumn() > 0) {
            $_err['email'] = 'Email already in use';
        }
    }

    // Name Validation
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (strlen($username) > 100) {
        $_err['username'] = 'Maximum 100 characters';
    }

    // Phone Validation
    if ($user_phone == '') {
        $_err['user_phone'] = 'Required';
    } else if (strlen($user_phone) > 20) {
        $_err['user_phone'] = 'Maximum 20 characters';
    }

    // Address Validation
    if ($user_address == '') {
        $_err['user_address'] = 'Required';
    } else if (strlen($user_address) > 255) {
        $_err['user_address'] = 'Maximum 255 characters';
    }

    // Photo Validation
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be an image';
        } else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }

    // --- Database Update ---
    if (!$_err) {
        if ($f) {
            if ($photo && $photo != 'default.jpg') {
                @unlink("../photos/$photo");
            }
            $photo = save_photo($f, '../photos/');
        }
        
        $stm = $_db->prepare('
            UPDATE users
            SET email = ?, username = ?, user_photo = ?, user_phone = ?, user_address = ?
            WHERE user_id = ?
        ');
        $stm->execute([$email, $username, $photo, $user_phone, $user_address, $_user['user_id']]);

        // Update Session
        $_user['email']        = $email;
        $_user['username']     = $username;
        $_user['user_phone']   = $user_phone;
        $_user['user_address'] = $user_address;
        $_user['user_photo']   = $photo;

        temp('info', 'Profile updated successfully!');
        redirect('profile.php');
    }
}
?>


<div class="profile-wrapper">
    <link rel="stylesheet" href="../style.css">
    
    <?php if ($msg = temp('info')): ?>
        <div class="alert-success">
            <i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="profile-card">
        
        <div class="profile-sidebar">
            <div class="avatar-container">
                <?php 
                    // Logic: Check if photo exists. If yes, use it. If no, generate a default avatar.
                    if (!empty($photo)) {
                        $src = "../photos/" . htmlspecialchars($photo);
                    } else {
                        // Generate avatar based on name
                        $src = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=random&size=150";
                    }
                ?>
                <img src="<?= $src ?>" alt="Profile" class="profile-avatar">
            </div>
            <h2 class="sidebar-name"><?= htmlspecialchars($username) ?></h2>
            <p class="sidebar-email"><?= htmlspecialchars($email) ?></p>
        </div>

        <div class="profile-content">
            <h3 class="section-title">Account Details</h3>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="username">Full Name</label>
                    <input type="text" id="username" name="username" class="form-input" 
                           value="<?= htmlspecialchars($username) ?>" maxlength="100">
                    <span class="error-text"><?= $_err['username'] ?? '' ?></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?= htmlspecialchars($email) ?>" maxlength="100">
                    <span class="error-text"><?= $_err['email'] ?? '' ?></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user_phone">Phone Number</label>
                    <input type="text" id="user_phone" name="user_phone" class="form-input" 
                           value="<?= htmlspecialchars($user_phone) ?>" maxlength="20">
                    <span class="error-text"><?= $_err['user_phone'] ?? '' ?></span>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="user_address">Shipping Address</label>
                    <textarea id="user_address" name="user_address" class="form-input" 
                              rows="3" maxlength="255"><?= htmlspecialchars($user_address) ?></textarea>
                    <span class="error-text"><?= $_err['user_address'] ?? '' ?></span>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="photo">Update Profile Photo</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="photo" name="photo" accept="image/*" style="width: 100%;">
                    </div>
                    <span class="error-text"><?= $_err['photo'] ?? '' ?></span>
                    <small style="color: #9ca3af;">Supported: JPG, PNG (Max 1MB)</small>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/" class="btn btn-secondary">Cancel</a>
            </div>
        </div>

    </form>
</div>

<div id="footer-placeholder"></div>
    <script>
    fetch('../footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });

    $(document).ready(function() {
        $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>