<?php
include '../_base.php';

$_title = 'User | Profile';
include '../_head.php';

// ----------------------------------------------------------------------------

// Authenticated users
auth();

if (is_get()) {
    $stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
    $stm->execute([$_user->user_id]);
    $u = $stm->fetch();


    if (!$u) {
        redirect('/');
    }

    extract((array)$u);

    $photo = $u->user_photo;
    $_SESSION['photo'] = $photo;
}

if (is_post()) {
    $username  = req('username');
    $email = req('email');
    $user_phone = req('user_phone');
    $user_address = req('user_address');
    $photo = $_SESSION['photo'];
    $f = get_file('photo');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else {
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM users
            WHERE email = ? AND user_id != ?
        ');
        $stm->execute([$email, $_user->user_id]);

        if ($stm->fetchColumn() > 0) {
            $_err['email'] = 'Duplicated';
        }
    }

    // Validate: name
    if ($username == '') {
        $_err['username'] = 'Required';
    }
    else if (strlen($username) > 100) {
        $_err['username'] = 'Maximum 100 characters';
    }

    // Validate: phone
    if ($user_phone == '') {
        $_err['user_phone'] = 'Required';
    }
    else if (strlen($user_phone) > 20) {
        $_err['user_phone'] = 'Maximum 20 characters';
    }

    // Validate: Address
    if ($user_address == '') {
        $_err['user_address'] = 'Required';
    }
    else if (strlen($user_address) > 255) {
        $_err['user_address'] = 'Maximum 255 characters';
    }

    // Validate: photo (file) --> optional
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }

    // DB operation
    if (!$_err) {
        // (1) Delete and save photo --> optional
        if ($f) {
            unlink("../photos/$photo");
            $photo = save_photo($f, '../photos');
        }
        
        // (2) Update user (email, name, photo, phone, address)
        $stm = $_db->prepare('
            UPDATE users
            SET email = ?, username = ?, user_photo = ?, user_phone = ?, user_address = ?
            WHERE user_id = ?
        ');
        $stm->execute([$email, $username, $photo, $user_phone, $user_address, $_user->user_id]);

        // (3) Update global user object
        $_user->email = $email;
        $_user->username  = $username;
        $_user->user_phone = $user_phone;
        $_user->user_address = $user_address;
        $_user->user_photo = $user_photo;

        temp('info', 'Profile updated successfully');
        redirect('/');
    }
}

// ----------------------------------------------------------------------------

?>

<form method="post" class="form" enctype="multipart/form-data">
    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>

    <label for="username">Name</label>
    <?= html_text('username', 'maxlength="100"') ?>
    <?= err('username') ?>

    <label for="user_phone">Phone Number</label>
    <?= html_text('user_phone', 'maxlength="20"') ?>
    <?= err('user_phone') ?>

    <label for="user_address">Address</label>
    <textarea id="user_address" name="user_address" maxlength="255"><?= encode($user_address) ?></textarea>
    <?= err('user_address') ?>

    <label for="photo">Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('photo', 'image/*', 'hidden') ?>
        <img src="../photos/<?= $photo ?>">
    </label>
    <?= err('photo') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';