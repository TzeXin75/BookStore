<?php
include './_base.php';

$id = req('id');
//if no id provided, redirect back to userslist
if (!$id) redirect('?page=users');

//when the page receives a POST request we handle different update actions
if (is_post()) {
    $action = req('action'); 

    //updates user role
    if ($action == 'update_role') {
        $new_role = req('role');
        $stm = $_db->prepare('UPDATE users SET user_role = ? WHERE user_id = ?');
        $stm->execute([$new_role, $id]);
    }

    //updates user profile details
    if ($action == 'update_profile') {
        $email   = req('email');
        $phone   = req('phone');
        $address = req('address');
        $dob     = req('dob');
        $status  = req('status'); //1 = active, 0 = inactive

        //database record 
        $stm = $_db->prepare('UPDATE users SET email=?, user_phone=?, user_address=?, user_dob=?, user_status=? WHERE user_id=?');
        $stm->execute([$email, $phone, $address, $dob, $status, $id]);
    }
}

//fetch user details
$stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
$stm->execute([$id]);
$u = $stm->fetch();

//if user not found, show error message
if (!$u) {
    echo "<div class='error-box'>User not found. <a href='?page=users'>Go Back</a></div>";
    return;
}
?>

<style>
    .radio-group {
        display: flex;
        gap: 20px;
        align-items: center;
        height: 38px;
    }
    .radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 500;
        margin: 0;
        color: #333;
    }
    .radio-label input[type="radio"] {
        margin: 0;
        width: 18px;
        height: 18px;
        accent-color: #2563eb;
        cursor: pointer;
    }
</style>

<div class="page-header">
    <link rel="stylesheet" href="../style.css">
    <h2>User Details</h2>
    <a href="?page=users" class="btn-back">← Back to List</a>
</div>

<div class="profile-grid">
    
    <div class="card profile-card">
        <div class="card-header-visual">
            <div class="avatar-wrapper">
                <?php 

                    //uploaded photo if present & generate image
                    $photo = !empty($u->user_photo) ? './photos/' . $u->user_photo : "https://ui-avatars.com/api/?name=" . urlencode($u->username) . "&background=random&size=128"; 
                ?>
                <img src="<?= $photo ?>" alt="Profile" class="avatar-lg">
                </div>
            
            <h3 class="profile-name"><?= htmlspecialchars($u->username) ?></h3>
            <p class="profile-email"><?= htmlspecialchars($u->email) ?></p>
            
            <div class="role-badge-display">
                <!-- show user role member/admin -->
                <span class="badge-pill role-<?= $u->user_role ?>">
                    <?= ucfirst($u->user_role) ?>
                </span>
            </div>
        </div>
        
        <div class="card-actions">
            <h4 class="action-title">Admin Controls</h4>
            <form method="post" class="role-form">
                <input type="hidden" name="action" value="update_role">
                
                <div class="form-group">
                    <label class="input-label">Assign Role</label>
                    <div class="select-wrapper">
                        <select name="role" onchange="this.form.submit()" class="custom-select">
                            <option value="member"   <?= $u->user_role == 'member'   ? 'selected' : '' ?>>Member</option>
                            <option value="admin"    <?= $u->user_role == 'admin'    ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <span class="select-arrow">▼</span>
                    </div>
                </div>
            </form>

            <div class="card-info-row">
                <span>Member Since</span>
                <strong><?= date('M Y', strtotime($u->user_registrationDate)) ?></strong>
            </div>
        </div>
    </div>

    <div class="card" id="personalInfoCard">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; border: none;">Personal Information</h3>
            
            <div>
                <button type="button" class="btn primary btn-edit-start" onclick="toggleEditMode(true)">Edit</button>
                <div class="btn-save-group">
                    <button type="button" class="btn-cancel" onclick="toggleEditMode(false)">Cancel</button>
                    <button type="submit" form="profileForm" class="btn primary">Update</button>
                </div>
            </div>
        </div>
        
        <form method="post" id="profileForm">
            <!-- main profile edit form -->
            <input type="hidden" name="action" value="update_profile">
            <table class="info-table">
                <tr>
                    <th style="width: 30%;">User ID</th>
                    <td>#<?= $u->user_id ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($u->username) ?></td>
                </tr>
                <tr>
                    <th>Email Address</th>
                    <td>
                        <span class="view-mode-field"><?= htmlspecialchars($u->email) ?></span>
                        <input type="email" name="email" class="edit-mode-field" value="<?= htmlspecialchars($u->email) ?>" required>
                    </td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td>
                        <span class="view-mode-field"><?= htmlspecialchars($u->user_phone ?: 'Not provided') ?></span>
                        <input type="text" name="phone" class="edit-mode-field" value="<?= htmlspecialchars($u->user_phone) ?>">
                    </td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td>
                        <span class="view-mode-field"><?= htmlspecialchars($u->user_dob ?: '-') ?></span>
                        <input type="date" name="dob" class="edit-mode-field" value="<?= htmlspecialchars($u->user_dob) ?>">
                    </td>
                </tr>

                <tr>
                    <th>Account Status</th>
                    <td>
                        <?php 
                            //determine if user is active or inactive
                            $is_active = ($u->user_status == 1);

                            if ($is_active) {
                                $bg_color = '#d1fae5'; $text_color = '#065f46'; 
                                $label = 'Active';
                            } else {
                                $bg_color = '#fee2e2'; $text_color = '#991b1b'; 
                                $label = 'Inactive';
                            }
                        ?>

                        <!-- colored badge show current status -->
                        <span class="view-mode-field">
                            <span class="badge" style="background:<?= $bg_color ?>; color:<?= $text_color ?>;">
                                <?= $label ?>
                            </span>
                        </span>
                        
                        <!-- radio buttons to toggle status -->
                        <div class="edit-mode-field">
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="status" value="1" <?= $is_active ? 'checked' : '' ?>> 
                                    Active
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="status" value="0" <?= !$is_active ? 'checked' : '' ?>> 
                                    Inactive
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>Joined Date</th>
                    <td style="color: #888;">
                        <?= htmlspecialchars($u->user_registrationDate ?: 'Unknown') ?>
                    </td>
                </tr>

                <tr>
                    <th>Address</th>
                    <td>
                        <!-- show address, allow editing -->
                        <span class="view-mode-field"><?= nl2br(htmlspecialchars($u->user_address ?: 'Not provided')) ?></span>
                        <textarea name="address" class="edit-mode-field" rows="3"><?= htmlspecialchars($u->user_address) ?></textarea>
                    </td>
                </tr>
            </table>
        </form>
    </div>

</div>

<script>

/*toggles the edit mode for the personal information card*/
function toggleEditMode(enable) {
    const card = document.getElementById('personalInfoCard');
    if (enable) {
        card.classList.add('is-editing');
    } else {
        card.classList.remove('is-editing');
        //reset form to original loaded / discard unsaved edits
        document.getElementById('profileForm').reset();
    }
}
</script>