<?php
require '../_base.php';
//-----------------------------------------------------------------------------

// (A) NEW: Handle Batch Delete (POST request)
if (is_post()) {
    $ids = req('ids', []); // Get the array of selected IDs

    if ($ids) {
        // Generate placeholders like (?,?,?) based on how many IDs were checked
        $count = count($ids);
        $placeholders = str_repeat('?,', $count - 1) . '?';

        // Delete from database
        $stm = $_db->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
        $stm->execute($ids);

        temp('info', "$count record(s) deleted");
    }
    redirect(); // Refresh the page
}

// (1) Sorting
$fields = [
    'user_id'      => 'Id',
    'username'     => 'Name',
    'email'        => 'Email',
    'user_phone'   => 'Phone Number',
    'user_address' => 'Address',
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// (2) Search (Get the search term)
$name = req('name'); // Retrieve the 'name' from the form

// (3) Paging & Query
$page = req('page', 1);

require_once '../lib/SimplePager.php';

// AND username LIKE ?" to the SQL query
// ADDED: ["%$name%"] to the parameters array
$p = new SimplePager(
    "SELECT * FROM users WHERE user_role = 'member' AND username LIKE ? ORDER BY $sort $dir", 
    ["%$name%"], 
    10, 
    $page
);

$arr = $p->result;

// ----------------------------------------------------------------------------
$_title = 'Member List'; 
include '../_head.php';
?>

<form>
    <?= html_search('name', 'placeholder="Search username..."') ?>
    <button>Search</button>
</form>

<p>
    <?= $p->count ?> of <?= $p->item_count ?> record(s) |
    Page <?= $p->page ?> of <?= $p->page_count ?>
</p>

<form method="post">
    
    <button id="btnDelete" name="action" value="delete" class="danger" >
    🗑️
    </button>

    <strong style="margin-left: 10px;">
        Selected: <span id="selection-count">0</span>
    </strong>
    <br><br>

    <table class="table">
        <tr>
            <th style="width: 30px;">
                <input type="checkbox" id="checkAll">
            </th>
            <?= table_headers($fields, $sort, $dir, "page=$page&name=$name") ?>
        </tr>

        <?php foreach ($arr as $s): ?>
        <tr data-get="member_details.php?id=<?= $s->user_id ?>">
            
            <td onclick="event.stopPropagation()">
                <input type="checkbox" name="ids[]" value="<?= $s->user_id ?>">
            </td>

            <td><?= $s->user_id ?></td>
            <td><?= $s->username ?></td>
            <td><?= $s->email ?></td>
            <td><?= $s->user_phone ?></td>
            <td><?= $s->user_address  ?></td>
        </tr>
        <?php endforeach ?>
    </table>
</form>

<br>
<?= $p->html("sort=$sort&dir=$dir&name=$name") ?>

<script>
    // 1. Initialize Memory
    // We use a Set to store unique IDs
    let selectedIds = new Set(JSON.parse(sessionStorage.getItem('member_deletes') || '[]'));

    // This function updates the visual checkboxes and the counter number
    function updateDisplay() {
        // A. Update the checkboxes (Visual)
        $('input[name="ids[]"]').each((i, el) => {
            if (selectedIds.has(el.value)) {
                $(el).prop('checked', true);
            } else {
                $(el).prop('checked', false);
            }
        });

        // B. Update the "Check All" box
        const all = $('input[name="ids[]"]').length;
        const checked = $('input[name="ids[]"]:checked').length;
        const isAllChecked = (all > 0 && all === checked);
        $('#checkAll').prop('checked', isAllChecked);

        // C. Update the Counter Text (The part that was missing!)
        $('#selection-count').text(selectedIds.size);
    }

    // 2. Run immediately on page load to restore previous selections
    $(() => {
        updateDisplay();
    });

    // 3. Handle Single Checkbox Change
    // We use $(document) to ensure we catch the click even if the table HTML is messy
    $(document).on('change', 'input[name="ids[]"]', e => {
        // Stop the row click (Details Page)
        e.stopPropagation(); 
        
        const id = e.target.value;
        if (e.target.checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        
        // Save to memory
        sessionStorage.setItem('member_deletes', JSON.stringify([...selectedIds]));
        updateDisplay();
    });

    // 4. Handle "Check All" Change
    $(document).on('change', '#checkAll', e => {
        e.stopPropagation();
        const isChecked = e.target.checked;
        
        $('input[name="ids[]"]').each((i, el) => {
            const id = el.value;
            if (isChecked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
        });

        sessionStorage.setItem('member_deletes', JSON.stringify([...selectedIds]));
        updateDisplay();
    });

    // 5. Handle Form Submit (Injecting IDs)
    $('form').on('submit', e => {
        const container = $(e.target);
        
        selectedIds.forEach(id => {
            // Only add if it doesn't exist yet
            if (container.find(`input[value="${id}"]`).length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }).appendTo(container);
            }
        });

        // Clear memory after submit
        sessionStorage.removeItem('member_deletes');
    });

    // Handle Delete Button Click
    $('#btnDelete').on('click', e => {
        // 1. Get the current number of selected items
        const count = selectedIds.size;

        // 2. If nothing selected, show a warning and STOP.
        if (count === 0) {
            alert('No members selected.');
            e.preventDefault(); // Stop form from submitting
            return;
        }

        // 3. Show dynamic confirmation message
        // This puts the 'count' variable inside the text
        if (!confirm(`Are you sure you want to delete ${count} member(s)?`)) {
            e.preventDefault(); // Stop if user clicks 'Cancel'
        }
        
        // If user clicks 'OK', the code continues and submits the form automatically.
    });

    // 6. Stop row clicks when clicking the checkbox cell (extra safety)
    $('td').on('click', 'input', e => e.stopPropagation());
</script>

<?php
include '../_foot.php';