<?php

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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
    :root{ --bg:#f6f9fc; --card:#fff; --muted:#6b7280; --accent:#2563eb; --border:#e6eef8 }
    body{ font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background:var(--bg); }
    .user-panel{ max-width:1100px; margin:14px auto; padding:12px }
    .panel-card{ background:var(--card); border:1px solid var(--border); border-radius:8px; box-shadow:0 4px 18px rgba(16,24,40,0.04); overflow:hidden }
    .panel-header{ display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid var(--border) }
    .panel-title{ font-size:18px; font-weight:600; color:#0f172a }
    .controls{ display:flex; align-items:center; gap:10px }
    .btn{ background:var(--accent); color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:600 }
    .btn.ghost{ background:transparent; color:var(--accent); border:1px solid #dbeafe }
    .panel-body{ padding:18px }
    .profile-grid{ display:grid; grid-template-columns:320px 1fr; gap:18px }
    .card{ background:transparent; border-radius:6px }
    .profile-card{ border:1px solid var(--border); padding:18px; border-radius:8px }
    .avatar-lg{ width:96px; height:96px; border-radius:12px; object-fit:cover; border:1px solid #eef6ff }
    .avatar-wrapper{ display:flex; align-items:center; gap:12px }
    .profile-name{ margin:12px 0 0; font-size:16px; font-weight:700 }
    .profile-email{ margin:6px 0 0; color:var(--muted); font-size:13px }
    .role-badge-display .badge-pill{ display:inline-block; padding:6px 10px; border-radius:999px; background:#eef2ff; color:#1e40af; font-weight:600; margin-top:10px }
    .card-actions{ margin-top:16px; border-top:1px dashed #f1f5f9; padding-top:12px }
    .card-info-row{ display:flex; justify-content:space-between; padding:8px 0; color:var(--muted) }
    .select-wrapper{ position:relative; display:inline-block }
    .custom-select{ padding:8px 36px 8px 12px; border-radius:6px; border:1px solid #e6eef8 }
    .select-arrow{ position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--muted) }

    /* personal info card */
    #personalInfoCard{ border:1px solid var(--border); padding:18px; border-radius:8px; background:linear-gradient(180deg,#fff,#fbfdff) }
    .info-table{ width:100%; border-collapse:collapse; font-size:14px }
    .info-table th{ text-align:left; padding:10px 12px; width:28%; color:var(--muted); font-weight:600 }
    .info-table td{ padding:10px 12px }

    /* edit/view mode toggles */
    .edit-mode-field{ display:none }
    #personalInfoCard.is-editing .edit-mode-field{ display:block }
    #personalInfoCard.is-editing .view-mode-field{ display:none }

    .radio-group{ display:flex; gap:18px; align-items:center }
    .radio-label{ display:flex; align-items:center; gap:8px; cursor:pointer; color:#111827 }
    .radio-label input[type="radio"]{ width:16px; height:16px; accent-color:var(--accent) }

    .btn-save-group{ display:none }
    #personalInfoCard.is-editing .btn-save-group{ display:inline-flex; gap:8px }
    .btn-cancel{ background:transparent; border:1px solid #e6eef8; padding:8px 12px; border-radius:6px; cursor:pointer }

    @media (max-width:800px){ .profile-grid{ grid-template-columns:1fr; } .avatar-lg{ width:72px; height:72px } }
</style>

<div class="user-panel">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">User Details</div>
            <div class="controls">
                <a href="?page=users" class="btn ghost">← Back to List</a>
            </div>
        </div>
        <div class="panel-body">
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
        </div>
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