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

<!-- Modern UI styles for users list -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    :root{
        --bg: #f6f9fc;
        --card: #ffffff;
        --muted: #6b7280;
        --accent: #2b6cb0;
        --accent-600: #2563eb;
        --border: #e6eef8;
    }
    body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background: var(--bg); }
    .user-panel { max-width: 1100px; margin: 12px auto; padding: 18px; }
    .panel-card { background: var(--card); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 2px 6px rgba(16,24,40,0.04); overflow: hidden; }
    .panel-header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid var(--border); }
    .panel-title { font-weight:600; color:#111827; font-size:18px; }
    .controls { display:flex; gap:10px; align-items:center; }
    .search-input { padding:8px 12px; border:1px solid #d1e3fb; border-radius:6px; min-width:240px; outline:none; }
    .search-input:focus{ box-shadow: 0 0 0 4px rgba(37,99,235,0.08); border-color:var(--accent-600); }
    .btn { background:var(--accent); color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:600; }
    .btn.ghost { background:transparent; color:var(--accent-600); border:1px solid #cfe3ff; }
    .info-line { padding:12px 20px; color:var(--muted); font-size:14px; }
    table.user-table { width:100%; border-collapse:collapse; font-size:14px; }
    table.user-table th, table.user-table td { padding:14px 18px; text-align:left; border-bottom:1px solid #f1f5f9; color:#111827; }
    table.user-table thead th { background:#fbfdff; font-weight:600; color:#374151; }
    table.user-table tbody tr:hover { background: #f8fbff; cursor: pointer; }
    tr.selected { background-color: #eaf4ff !important; }
    .cb-cell { width:48px; }
    .row-username { font-weight:600; color:#0f172a; }
    #selection-count { background:#eef6ff; padding:2px 8px; border-radius:999px; color:var(--accent-600); margin-left:6px; }
    #pager-container { padding:16px 20px; display:flex; justify-content:flex-end; }
    #pager-container a { margin-left:8px; padding:6px 10px; background:#fff; border:1px solid #e6eef8; color:#0f172a; border-radius:6px; text-decoration:none; }
    #pager-container a.active, #pager-container a:hover { background:var(--accent-600); color:#fff; border-color:var(--accent-600); }
    @media (max-width:900px){ .user-panel{ padding:8px } .search-input{ min-width:140px }}
</style>

<div class="user-panel">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">Users</div>
            <div class="controls">
                <form method="get" style="display:flex;align-items:center;gap:8px;" class="search-form">
                    <input type="hidden" name="page" value="users">
                    <input type="search" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Search username..." class="search-input">
                    <button type="submit" class="btn ghost">Search</button>
                </form>
                <div style="display:flex;align-items:center;gap:12px;">
                    <button id="btnDelete" name="action" value="delete" type="button" class="btn" title="Delete Selected">🗑️ Delete</button>
                    <span style="color:var(--muted);font-size:14px;">Selected: <span id="selection-count">0</span></span>
                </div>
            </div>
        </div>

        <div class="info-line">
            <?= $p->count ?> of <?= $p->item_count ?> record(s) | Page <?= $p->page ?> of <?= $p->page_count ?>
        </div>

        <form method="post" id="batchForm">
            <table class="user-table">
        <tr>
            <th style="width: 30px;"><input type="checkbox" id="checkAll"></th>
            <?= table_headers($fields, $sort, $dir, "page=users&name=" . urlencode($name)) ?>
        </tr>

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
    </table>
</form>

<br>

<div id="pager-container">
    <?= $p->html("page=users&sort=$sort&dir=$dir&name=" . urlencode($name), "", "pg") ?>
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