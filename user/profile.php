<?php
include '../_base.php';

// ----------------------------------------------------------------------------
$_title = 'My Profile';
include '../head.php'; 

//Authentication 
if (!isset($_user['user_id'])) {
    header("Location: login.php"); 
    exit();
}

//latest profile edit
$stm = $_db->prepare(
    'SELECT username, email, user_photo, user_phone, user_address, reward_points FROM users WHERE user_id = ?'
);
$stm->execute([$_user['user_id']]);
$u = $stm->fetch(PDO::FETCH_ASSOC);

//if user not found, redirect to home
if (!$u) {
    redirect('/');
}

$username      = $u['username'] ?? '';
$email         = $u['email'] ?? '';
$photo         = $u['user_photo'] ?? '';
$user_phone    = $u['user_phone'] ?? '';
$user_address  = $u['user_address'] ?? '';
$reward_points = $u['reward_points'] ?? 0;

$_user['username']     = $username;
$_user['email']        = $email;
$_user['user_photo']   = $photo;
$_user['user_phone']   = $user_phone;
$_user['user_address'] = $user_address;

//validation messages
$_err = [];

//determine Reward Tier and Progress 
$points = (int)$reward_points;
$tiers = [
    ['name' => 'Iron',   'min' => 0,   'max' => 99,  'color' => '#9ca3af'],
    ['name' => 'Bronze', 'min' => 100, 'max' => 299, 'color' => '#cd7f32'],
    ['name' => 'Silver', 'min' => 300, 'max' => 599, 'color' => '#C0C0C0'],
    ['name' => 'Gold',   'min' => 600, 'max' => PHP_INT_MAX, 'color' => '#ffd700'],
];

$current_tier = $tiers[0];
$current_index = 0;
foreach ($tiers as $i => $t) {
    //find current tier based on points
    if ($points >= $t['min'] && $points <= $t['max']) {
        $current_tier = $t;
        $current_index = $i;
        break;
    }
}

//calculate progress towards next tier
if ($current_tier['max'] === PHP_INT_MAX) {
    $next_threshold = null; 
    $progress = 100;        
    $next_name = null;
} else {
    $next_threshold = $current_tier['max'] + 1;
    $next_name = isset($tiers[$current_index + 1]) ? $tiers[$current_index + 1]['name'] : null;
    $progress = ($points - $current_tier['min']) / ($next_threshold - $current_tier['min']) * 100;
    if ($progress < 0) $progress = 0;
    if ($progress > 100) $progress = 100;
}

//handle profile update submission
if (is_post()) {
    $username     = req('username');
    $email        = req('email');
    $user_phone   = req('user_phone');
    $user_address = req('user_address');
    $f            = get_file('photo');

    //validation checks
    if ($username === '') {
        $_err['username'] = 'Required';
    } elseif (strlen($username) > 100) {
        $_err['username'] = 'Maximum 100 characters';
    }

    if ($email === '') {
        $_err['email'] = 'Required';
    } elseif (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    } elseif (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?');
        $stm->execute([$email, $_user['user_id']]);
        if ($stm->fetchColumn() > 0) {
            $_err['email'] = 'Email already in use';
        }
    }

    if ($user_phone === '') {
        $_err['user_phone'] = 'Required';
    } elseif (strlen($user_phone) > 20) {
        $_err['user_phone'] = 'Maximum 20 characters';
    }

    if ($user_address === '') {
        $_err['user_address'] = 'Required';
    } elseif (strlen($user_address) > 255) {
        $_err['user_address'] = 'Maximum 255 characters';
    }

    // uploaded file validation (optional)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be an image';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }

    //if no validation errors, proceed to save
    if (!$_err) {
        if ($f) {
            //delete old photo if exists
            if (!empty($photo) && $photo !== 'default.jpg') {
                @unlink("../photos/" . $photo);
            }
            $photo = save_photo($f, '../photos/');
        }

        //update user record
        $stm = $_db->prepare(
            'UPDATE users SET email = ?, username = ?, user_photo = ?, user_phone = ?, user_address = ? WHERE user_id = ?'
        );
        $stm->execute([$email, $username, $photo, $user_phone, $user_address, $_user['user_id']]);

        //update session user info
        $_user['email']        = $email;
        $_user['username']     = $username;
        $_user['user_photo']   = $photo;
        $_user['user_phone']   = $user_phone;
        $_user['user_address'] = $user_address;

        temp('info', 'Profile updated successfully!');
        redirect('profile.php');
    }
}
?>

<div class="profile-wrapper">
    <link rel="stylesheet" href="../style.css">

    <form method="post" enctype="multipart/form-data" class="profile-card">
        <div class="profile-sidebar">
            <div class="avatar-container">
                <?php 
                if (!empty($photo)) {
                    $src = "../photos/" . htmlspecialchars($photo);
                } else {
                    $src = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=random&size=200";
                }
                ?>
                <img src="<?= $src ?>" alt="Profile" class="profile-avatar" id="currentAvatar">
            </div>
            <h2 class="sidebar-name"><?= htmlspecialchars($username) ?></h2>
            <p class="sidebar-email"><?= htmlspecialchars($email) ?></p>
            <p style="margin-top:6px; font-weight:600; color:#2b6cb0;">
                Reward Points: <?= htmlspecialchars($reward_points) ?>
            </p>
            <div class="reward-tier" style="margin-top:8px;">
                <span class="tier-badge" style="background: <?= htmlspecialchars($current_tier['color']) ?>;">
                    <?= htmlspecialchars($current_tier['name']) ?>
                </span>
                <div class="tier-progress" style="margin-top:8px; background:#e6e6e6; border-radius:6px; height:12px; overflow:hidden;">
                    <div class="tier-progress-bar" style="width: <?= round($progress) ?>%; background: <?= htmlspecialchars($current_tier['color']) ?>; height:100%;"></div>
                </div>
                <?php if ($next_threshold !== null): ?>
                    <small style="display:block; margin-top:6px; color:#6b7280;">
                        <?= max(0, $next_threshold - $points) ?> points to reach <?= htmlspecialchars($next_name) ?>
                    </small>
                <?php else: ?>
                    <small style="display:block; margin-top:6px; color:#6b7280;">You have reached the highest tier. Great!</small>
                <?php endif; ?>
            </div>
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
                    <div class="file-upload-wrapper" id="uploadWrapper">
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <span class="file-upload-label" id="uploadLabel">
                            <i class="fas fa-upload"></i> Choose New Photo (Max 1MB)
                        </span>
                    </div>
                    <small style="color: #9ca3af; display: block; margin-top: 5px;">
                        Supported: JPG, PNG, GIF (Max 1MB)
                    </small>

                    <div id="previewContainer" style="display: none; margin-top: 20px; text-align: center;">
                        <img id="photoPreview" src="" alt="New Photo Preview" 
                             style="max-width: 220px; max-height: 220px; border-radius: 50%; box-shadow: 0 6px 15px rgba(0,0,0,0.15);">
                        <p style="color: #28a745; font-weight: bold; margin: 15px 0 10px;">
                            <i class="fas fa-check-circle"></i> Upload successful!
                        </p>
                        <button type="button" id="removePhoto" 
                                style="color: #dc3545; background:none; border:none; font-size:0.95rem; cursor:pointer;">
                            <i class="fas fa-trash"></i> Remove new photo
                        </button>
                    </div>

                    <span class="error-text"><?= $_err['photo'] ?? '' ?></span>
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

document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    const photoPreview = document.getElementById('photoPreview');
    const currentAvatar = document.getElementById('currentAvatar');
    const uploadWrapper = document.getElementById('uploadWrapper');
    const uploadLabel = document.getElementById('uploadLabel');

    if (file) {
        if (file.size > 1 * 1024 * 1024) {
            alert('Photo must be less than 1MB');
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            photoPreview.src = event.target.result;
            previewContainer.style.display = 'block';

         
            currentAvatar.src = event.target.result;

           
            uploadWrapper.style.borderColor = '#28a745';
            uploadWrapper.style.backgroundColor = '#f0fff4';
            uploadLabel.innerHTML = 'New photo selected';
            uploadLabel.style.color = '#28a745';
        };
        reader.readAsDataURL(file);
    } else {
        resetUpload();
    }
});


document.getElementById('removePhoto').addEventListener('click', function() {
    document.getElementById('photo').value = '';
    resetUpload();
});

function resetUpload() {
    const previewContainer = document.getElementById('previewContainer');
    const currentAvatar = document.getElementById('currentAvatar');
    const uploadWrapper = document.getElementById('uploadWrapper');
    const uploadLabel = document.getElementById('uploadLabel');


    previewContainer.style.display = 'none';

   
    currentAvatar.src = "<?= !empty($photo) ? '../photos/' . htmlspecialchars($photo) : 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=random&size=200' ?>";


    uploadWrapper.style.borderColor = '#ccc';
    uploadWrapper.style.backgroundColor = '#f8f9fa';
    uploadLabel.innerHTML = 'Choose New Photo (Max 1MB)';
    uploadLabel.style.color = '#667eea';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="../script.js"></script>