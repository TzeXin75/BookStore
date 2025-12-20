<?php
require '../_base.php';
// ----------------------------------------------------------------------------

// (A) GET Request: Fetch existing data
if (is_get()) {
    $id = req('id');

    $stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
    $stm->execute([$id]);
    $u = $stm->fetch();

    if (!$u) {
        redirect('member.php');
    }

    // Extract values
    $user_id      = $u->user_id;
    $username     = $u->username;
    $email        = $u->email;
    $user_phone   = $u->user_phone;
    $user_address = $u->user_address;
    $user_photo   = $u->user_photo; // Fetch photo to display it
    $user_role    = $u->user_role;
    $user_registrationDate = $u->user_registrationDate;
}

// (B) POST Request: Validate and Update
if (is_post()) {
    $id = req('id');
    
    $username     = req('username');
    $email        = req('email');
    $user_phone   = req('user_phone');
    $user_address = req('user_address');

    // 1. Validate Email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    }
    else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?');
        $stm->execute([$email, $id]);
        if ($stm->fetchColumn() > 0) {
            $_err['email'] = 'Email already exists';
        }
    }

    // 2. Validate Name
    if ($username == '') {
        $_err['username'] = 'Required';
    }
    else if (strlen($username) > 100) {
        $_err['username'] = 'Maximum 100 characters';
    }

    // 3. Validate Phone
    if ($user_phone == '') {
        $_err['user_phone'] = 'Required';
    }

    // 4. Validate Address
    if ($user_address == '') {
        $_err['user_address'] = 'Required';
    }

    // 5. Update Database
    if (!$_err) {
        $stm = $_db->prepare('
            UPDATE users 
            SET username = ?, email = ?, user_phone = ?, user_address = ? 
            WHERE user_id = ?
        ');
        $stm->execute([$username, $email, $user_phone, $user_address, $id]);

        temp('info', 'Member updated successfully');
        redirect("member_details.php?id=$id");
    }
}

// ----------------------------------------------------------------------------
$_title = 'Update Member';
include '../_head.php';
?>

<style>
    #photo {
        display: block;
        border: 1px solid #333;
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    /* Ensure inputs fit nicely in the table cells */
    .table.detail input, 
    .table.detail textarea {
        width: 100%;
        box-sizing: border-box;
    }
</style>

<form method="post">
    
    <p>
        <img src="/photos/<?= $user_photo ?>" id="photo">
    </p>

    <table class="table detail">
        <tr>
            <th>User ID</th>
            <td>
                <b><?= $id ?></b>
                </td>
        </tr>
        <tr>
            <th>Name</th>
            <td>
                <?= html_text('username', 'maxlength="100"') ?>
                <?= err('username') ?>
            </td>
        </tr>
        <tr>
            <th>Email</th>
            <td>
                <?= html_text('email', 'maxlength="100"') ?>
                <?= err('email') ?>
            </td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>
                <?= html_text('user_phone', 'maxlength="20"') ?>
                <?= err('user_phone') ?>
            </td>
        </tr>
        <tr>
            <th>Address</th>
            <td>
                <textarea name="user_address" rows="3"><?= encode($user_address) ?></textarea>
                <?= err('user_address') ?>
            </td>
        </tr>
        <tr>
            <th>Role</th>
            <td>
                <b><?= $user_role?></b>
                </td>
        </tr>
        <tr>
            <th>Registration Date</th>
            <td>
                <b><?= $user_registrationDate?></b>
                </td>
        </tr>
    </table>

    <br>

    <section>
        <button>Confirm Update</button>
        <button type="button" onclick="history.back()">Cancel</button>
    </section>
</form>

<?php
include '../_foot.php';
?>