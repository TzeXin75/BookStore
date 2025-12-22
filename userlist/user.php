<?php
require './_base.php';

// --- (A) BATCH DELETE LOGIC ---
if (is_post()) {
    $ids = req('ids', []);
    if ($ids) {
        $count = count($ids);
        $placeholders = str_repeat('?,', $count - 1) . '?';
        $stm = $_db->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
        $stm->execute($ids);
        temp('info', "$count record(s) deleted");
    }
    redirect('?page=users'); // Reloads the correct page
}

// --- (B) SETTINGS ---
$fields = [
    'user_id'      => 'Id',
    'username'     => 'Name',
    'email'        => 'Email',
    'user_phone'   => 'Phone Number',
    'user_address' => 'Address',
    'user_role'    => 'Role',
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$name = req('name', '');
$pg = req('pg', 1); // Use $pg for page number

require_once './lib/SimplePager.php';

// --- (C) QUERY ---
$p = new SimplePager(
    "SELECT * FROM users WHERE user_role IN ('customer', 'member') AND username LIKE ? ORDER BY $sort $dir",
    ["%$name%"],
    10,
    $pg
);
$arr = $p->result;
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
    .search-bar { display: flex; gap: 5px; margin-bottom: 20px; }
    .table th, .table td { vertical-align: middle; }
    /* Highlight selected row */
    tr.selected { background-color: #e3f2fd; }
</style>

<form method="get" class="search-bar">
    <input type="hidden" name="page" value="users">
    
    <input type="search" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Search username..." style="padding: 8px; border: 1px solid #ddd;">
    <button type="submit">Search</button>
</form>

<p>
    <?= $p->count ?> of <?= $p->item_count ?> record(s) |
    Page <?= $p->page ?> of <?= $p->page_count ?>
</p>

<form method="post" id="batchForm">
    <button id="btnDelete" name="action" value="delete" type="button" style="border:none; background:none; cursor:pointer; font-size:1.5em;" title="Delete Selected">
        🗑️
    </button>
    <strong style="margin-left: 10px;">Selected: <span id="selection-count">0</span></strong>
    <br><br>

    <table class="table">
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
    
    // --- MEMORY SYSTEM ---
    let selectedIds = new Set();
    
    // Restore from memory
    try {
        const stored = sessionStorage.getItem('user_deletes');
        if (stored) selectedIds = new Set(JSON.parse(stored));
    } catch(e) { 
        sessionStorage.removeItem('user_deletes'); 
    }

    // --- FUNCTIONS ---
    function updateUI() {
        // 1. Update Checkboxes
        $('.row-cb').each(function() {
            const id = $(this).val();
            const checked = selectedIds.has(id);
            $(this).prop('checked', checked);
            $(this).closest('tr').toggleClass('selected', checked);
        });

        // 2. Update Master Checkbox
        const total = $('.row-cb').length;
        const checked = $('.row-cb:checked').length;
        $('#checkAll').prop('checked', (total > 0 && total === checked));

        // 3. Update Text
        $('#selection-count').text(selectedIds.size);
    }

    // --- EVENTS ---

    // A. Check All Click
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

    // B. Single Checkbox Click
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

    // C. Row Click (Go to details)
    $('tr[data-href]').click(function(e) {
        // Don't redirect if clicking checkbox or checkbox cell
        if (!$(e.target).is('input') && !$(e.target).hasClass('cb-cell')) {
            window.location = $(this).data('href');
        }
    });

    // D. Delete Button
    $('#btnDelete').click(function(e) {
        e.preventDefault(); // Stop default submit
        
        if (selectedIds.size === 0) {
            alert('No items selected!');
            return;
        }

        if (confirm('Delete ' + selectedIds.size + ' items?')) {
            const form = $('#batchForm');
            // Inject hidden inputs for ALL selected IDs (even those on other pages)
            selectedIds.forEach(id => {
                if (form.find('input[value="'+id+'"]').length === 0) {
                    form.append('<input type="hidden" name="ids[]" value="'+id+'">');
                }
            });
            sessionStorage.removeItem('user_deletes'); // Clear memory
            form.submit(); // Actually submit the form
        }
    });

    // E. AUTO-FIX PAGER LINKS (Safety Patch)
    // If SimplePager.php wasn't edited correctly, this JS fixes the links automatically.
    $('#pager-container a').each(function() {
        let href = $(this).attr('href');
        if (href && href.includes('?page=') && !href.includes('users')) {
            // Convert ?page=2 to ?page=users&pg=2
            href = href.replace('?page=', '?page=users&pg=');
            $(this).attr('href', href);
        }
    });

    // Initialize
    updateUI();
});
</script>