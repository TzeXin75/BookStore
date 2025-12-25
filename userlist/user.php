<?php


//start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//only admin can access this page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

//handle batch delete action
if (is_post()) {
    $ids = req('ids', []);
    if ($ids) {
        $count = count($ids);
        $placeholders = str_repeat('?,', $count - 1) . '?';
        $stm = $_db->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
        $stm->execute($ids);
        temp('info', "$count record(s) deleted");
    }
    redirect('?page=users');
}

$fields = [
    'user_id'      => 'Id',
    'username'     => 'Name',
    'email'        => 'Email',
    'user_phone'   => 'Phone Number',
    'user_address' => 'Address',
    'user_role'    => 'Role',
];

//sort field and direction
$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

//search username
$name = req('name', '');
$pg = req('pg', 1);

require_once './lib/SimplePager.php';

//initialize pager
$p = new SimplePager(
    "SELECT * FROM users WHERE user_role IN ('member') AND username LIKE ? ORDER BY $sort $dir",
    ["%$name%"],
    10, 
    $pg  
);
$arr = $p->result; //result rows for current page
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
    /* Ensure the entire layout starts from the top */
    .admin-layout {
        display: flex;
        align-items: stretch; /* Makes sidebar and content equal height */
        min-height: 100vh;
        width: 100%;
        margin: 0;
    }

    /* This is the part that fixes the vertical centering */
    .main-content {
        flex-grow: 1;
        padding: 25px;
        background-color: #f4f7f6;
        display: flex;
        flex-direction: column; 
        /* justify-content: flex-start ensures content stays at the top */
        justify-content: flex-start; 
        /* align-items: stretch ensures content fills the width, not the vertical center */
        align-items: stretch; 
        gap: 20px;
    }

    /* Ensure your page header doesn't have huge top margins */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-top: 0; /* Remove any top gap */
        padding-top: 0;
        margin-bottom: 20px;
    }
</style>

<div class="admin-layout">

    <main class="main-content">

        <div class="search-section">
            <form method="get" class="search-bar">
                <input type="hidden" name="page" value="users">
                <input type="search" name="name" value="<?= htmlspecialchars($name) ?>" 
                       placeholder="Search username..." 
                       style="padding: 8px; border: 1px solid #ddd; width: 300px;">
                <button type="submit" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Search</button>
            </form>
        </div>

        <div class="stats-bar">
            <strong>Status:</strong> <?= $p->count ?> of <?= $p->item_count ?> record(s) | 
            <strong>Page:</strong> <?= $p->page ?> of <?= $p->page_count ?>
        </div>

        <form method="post" id="batchForm">
            <div style="margin-bottom: 10px; display: flex; align-items: center;">
                <button id="btnDelete" name="action" value="delete" type="button" 
                        style="border:none; background:none; cursor:pointer; font-size:1.5em;" title="Delete Selected">
                    🗑️
                </button>
                <span style="margin-left: 10px;">Selected: <strong id="selection-count">0</strong></span>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="checkAll"></th>
                        <?= table_headers($fields, $sort, $dir, "page=users&name=" . urlencode($name)) ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arr as $s): ?>
                    <tr data-href="?page=user_details&id=<?= $s->user_id ?>">
                        <td class="cb-cell">
                            <input type="checkbox" name="ids[]" value="<?= $s->user_id ?>" class="row-cb">
                        </td>
                        <td><?= $s->user_id ?></td>
                        <td><?= $s->username ?></td>
                        <td><?= $s->email ?></td>
                        <td><?= $s->user_phone ?></td>
                        <td><?= $s->user_address ?></td>
                        <td><?= $s->user_role ?></td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </form>

        <div id="pager-container" class="pager-section">
            <?= $p->html("page=users&sort=$sort&dir=$dir&name=" . urlencode($name), "", "pg") ?>
        </div>

    </main>
</div>

<script>
$(document).ready(function() {
    
    let selectedIds = new Set();
    
    try {
        const stored = sessionStorage.getItem('user_deletes');
        if (stored) selectedIds = new Set(JSON.parse(stored));
    } catch(e) { 
        sessionStorage.removeItem('user_deletes'); 
    }

    function updateUI() {
        //update checkboxes and selection count
        $('.row-cb').each(function() {
            const id = $(this).val();
            const checked = selectedIds.has(id);
            $(this).prop('checked', checked);
            $(this).closest('tr').toggleClass('selected', checked);
        });

        //update check all checkbox
        const total = $('.row-cb').length;
        const checked = $('.row-cb:checked').length;
        $('#checkAll').prop('checked', (total > 0 && total === checked));

        //update selection count
        $('#selection-count').text(selectedIds.size);
    }

    //checkbox event handlers
    $('#checkAll').change(function(e) {
        e.stopPropagation();
        const isChecked = $(this).prop('checked');
        $('.row-cb').each(function() {
            const id = $(this).val();
            if (isChecked) selectedIds.add(id);
            else selectedIds.delete(id);
        });
        saveAndRefresh();
    });

    //single row checkbox
    $(document).on('change', '.row-cb', function(e) {
        e.stopPropagation();
        const id = $(this).val();
        if ($(this).prop('checked')) selectedIds.add(id);
        else selectedIds.delete(id);
        saveAndRefresh();
    });

    function saveAndRefresh() {
        sessionStorage.setItem('user_deletes', JSON.stringify([...selectedIds]));
        updateUI();
    }

    //row click to redirect
    $('tr[data-href]').click(function(e) {
        //dont redirect if click checkbox or checkbox cell
        if (!$(e.target).is('input') && !$(e.target).hasClass('cb-cell')) {
            window.location = $(this).data('href');
        }
    });

    //batch delete button
    $('#btnDelete').click(function(e) {
        e.preventDefault(); 
        
        if (selectedIds.size === 0) {
            alert('No items selected!');
            return;
        }

        if (confirm('Delete ' + selectedIds.size + ' items?')) {
            const form = $('#batchForm');
            selectedIds.forEach(id => {
                if (form.find('input[value="'+id+'"]').length === 0) {
                    form.append('<input type="hidden" name="ids[]" value="'+id+'">');
                }
            });
            sessionStorage.removeItem('user_deletes'); 
            form.submit(); 
        }
    });

    //adjust pager links
    $('#pager-container a').each(function() {
        let href = $(this).attr('href');
        if (href && href.includes('?page=') && !href.includes('users')) {
            //insert users page param
            href = href.replace('?page=', '?page=users&pg=');
            $(this).attr('href', href);
        }
    });
    updateUI();
});
</script>