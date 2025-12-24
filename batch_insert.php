<?php
// Define category mapping
$categories = [
    'Fiction' => ['Novel', 'Comic'],
    'Non-Fiction' => ['Biography', 'Self-help'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];
?>

<div class="product-page" style="padding: 20px;">
    <h2 style="color: #2c3e50; margin-bottom: 20px;">Batch Add Products</h2>
    
    <form action="admin.php?page=process_batch_insert" method="POST">
        <div class="card" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table id="batchTable" style="width: 100%; border-collapse: collapse; min-width: 1100px;">
                <thead style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Title</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Author</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Category</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Subcategory</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Price (RM)</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem;">Stock</th>
                        <th style="padding: 12px; text-align: center; font-size: 0.85rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-item">
                        <td style="padding: 10px;"><input type="text" name="titles[]" required style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;"></td>
                        <td style="padding: 10px;"><input type="text" name="authors[]" style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;"></td>
                        
                        <td style="padding: 10px;">
                            <select name="categories[]" class="cat-select" onchange="updateSubcats(this)" required style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Category</option>
                                <?php foreach($categories as $cat => $subs): ?>
                                    <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <td style="padding: 10px;">
                            <select name="subcategories[]" class="subcat-select" required style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Subcategory</option>
                            </select>
                        </td>

                        <td style="padding: 10px;"><input type="number" step="0.01" name="prices[]" required style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;"></td>
                        <td style="padding: 10px;"><input type="number" name="stocks[]" required style="width:100%; padding: 8px; border:1px solid #ddd; border-radius:4px;"></td>
                        <td style="padding: 10px; text-align: center;">
                            <button type="button" onclick="removeRow(this)" style="color: #dc2626; border: none; background: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="padding: 15px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="addRow()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">+ Add Another Row</button>
            </div>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <button type="submit" style="background: #2563eb; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; cursor: pointer;">Save All Products</button>
            <a href="admin.php?page=product_dir" style="padding: 12px 30px; text-decoration: none; color: #4b5563; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 600;">Cancel</a>
        </div>
    </form>
</div>

<script>
// JSON data for categories and subcategories
const categoryMap = <?= json_encode($categories) ?>;

// Function to update subcategories based on category selection
function updateSubcats(catElement) {
    const row = catElement.closest('tr');
    const subcatSelect = row.querySelector('.subcat-select');
    const selectedCat = catElement.value;

    // Clear existing options
    subcatSelect.innerHTML = '<option value="">Subcategory</option>';

    if (selectedCat && categoryMap[selectedCat]) {
        categoryMap[selectedCat].forEach(sub => {
            const option = document.createElement('option');
            option.value = sub;
            option.textContent = sub;
            subcatSelect.appendChild(option);
        });
    }
}

function addRow() {
    const tbody = document.querySelector('#batchTable tbody');
    const firstRow = document.querySelector('.row-item');
    const newRow = firstRow.cloneNode(true);
    
    // Clear the values in the new row
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    
    // Reset dropdowns
    const catSelect = newRow.querySelector('.cat-select');
    const subSelect = newRow.querySelector('.subcat-select');
    catSelect.value = '';
    subSelect.innerHTML = '<option value="">Subcategory</option>';
    
    tbody.appendChild(newRow);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.row-item');
    if (rows.length > 1) {
        btn.closest('tr').remove();
    }
}
</script>