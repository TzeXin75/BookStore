<?php
include '../_base.php';

// ----------------------------------------------------------------------------

// (1) Get the ID from the URL
$id  = req('id');

// (2) Select the USER based on user_id
// Note: Changed 'id' to 'user_id' to match your database
$stm = $_db->prepare('SELECT * FROM users WHERE user_id = ?');
$stm->execute([$id]);
$u = $stm->fetch();

// (3) Redirect if user not found
if (!$u) {
    redirect('member.php'); // Or whatever your list page is named
}

// ----------------------------------------------------------------------------

$_title = 'Member | Detail';
include '../_head.php';
?>

<style>
    #photo {
        display: block;
        border: 1px solid #333;
        width: 200px;
        height: 200px;
        object-fit: cover; /* Ensures the photo doesn't look stretched */
        border-radius: 5px;
    }
</style>

<p>
    <img src="/photos/<?= $u->user_photo ?>" id="photo">
</p>

<table class="table detail">
    <tr>
        <th>User ID</th>
        <td><?= $u->user_id ?></td>
    </tr>
    <tr>
        <th>Name</th>
        <td><?= $u->username ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= $u->email ?></td>
    </tr>
    <tr>
        <th>Phone</th>
        <td><?= $u->user_phone ?></td>
    </tr>
    <tr>
        <th>Address</th>
        <td><?= nl2br($u->user_address) ?></td>
    </tr>
    <tr>
        <th>Role</th>
        <td><?= $u->user_role ?></td>
    </tr>
    <tr>
        <th>Registration Date</th>
        <td><?= $u->user_registrationDate ?></td>
    </tr>
</table>

<p>
    <button onclick="location='member_update.php?id=<?= $u->user_id ?>'">Update</button>

    <button onclick="location='member.php'">Back</button>
</p>

<?php
include '../_foot.php';
?>