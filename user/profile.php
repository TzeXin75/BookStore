<?php
include '../_base.php';

// 1. Setup Page Info
$_title = 'My Profile';
include '../head.php'; 

// 2. Authentication Check
auth();

// 3. GET Request: Fetch User Data
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

<style>
    /* --- Modern Profile UI Variables --- */
    :root {
        --primary-color: #4f46e5; /* Indigo */
        --primary-hover: #4338ca;
        --bg-color: #f3f4f6;
        --card-bg: #ffffff;
        --text-main: #111827;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
    }

    .profile-wrapper {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* --- The Card Container (Grid Layout) --- */
    .profile-card {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        display: grid;
        grid-template-columns: 320px 1fr; /* Sidebar | Content */
        min-height: 600px;
    }

    /* --- Left Sidebar (Visuals) --- */
    .profile-sidebar {
        background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%);
        border-right: 1px solid var(--border-color);
        padding: 40px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .avatar-container {
        position: relative;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .sidebar-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 10px 0 5px;
        color: var(--text-main);
    }

    .sidebar-email {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin-bottom: 30px;
        word-break: break-all;
    }

    /* --- Right Content (Form) --- */
    .profile-content {
        padding: 40px;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box; /* Critical for layout */
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .file-input-wrapper {
        margin-top: 5px;
        padding: 10px;
        border: 1px dashed var(--border-color);
        border-radius: 6px;
        background: #f9fafb;
    }

    /* --- Buttons --- */
    .btn-group {
        margin-top: 30px;
        display: flex;
        gap: 15px;
    }

    .btn {
        padding: 10px 24px;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: white;
        border: 1px solid var(--border-color);
        color: #374151;
    }

    .btn-secondary:hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
    }

    /* --- Alerts --- */
    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #a7f3d0;
    }

    .error-text {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
    }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .profile-card {
            grid-template-columns: 1fr; /* Stack vertically */
        }
        .profile-sidebar {
            border-right: none;
            border-bottom: 1px solid var(--border-color);
            padding: 30px;
        }
        .form-grid {
            grid-template-columns: 1fr; /* 1 column on mobile */
        }
        .form-group.full-width {
            grid-column: span 1;
        }
        .profile-content {
            padding: 25px;
        }
    }
</style>

<div class="profile-wrapper">
    
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

<?php include '../_foot.php'; ?>