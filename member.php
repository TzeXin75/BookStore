<?php
require '_base.php';

//-----------------------------------------------------------------------------

// (1) Sorting
$fields = [
    'user_id'         => 'ID',
    'username'       => 'Name',
    'email'          => 'Email',
    'user_phone'     => 'Phone Number',
    'user_address'   => 'Address',
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// (2) Paging
$page = req('page', 1);

require_once 'lib/SimplePager.php';
$p = new SimplePager("SELECT * FROM users WHERE user_role = 'member' ORDER BY $sort $dir", [], 10, $page);
$members = $p->result; // renamed for clarity

$_title = 'Members Management';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Members Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .members-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .results-info {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .members-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .members-table {
            width: 100%;
            border-collapse: collapse;
        }

        .members-table thead {
            background-color: #2c3e50;
            color: white;
        }

        .members-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .members-table th a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .members-table th a:hover {
            text-decoration: underline;
        }

        .members-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .members-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 10px 16px;
            background: #ecf0f1;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .pagination a.active {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }

        .pagination a:hover:not(.active) {
            background: #3498db;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .members-table,
            .members-table thead,
            .members-table tbody,
            .members-table th,
            .members-table td,
            .members-table tr {
                display: block;
            }

            .members-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .members-table tr {
                border: 1px solid #ddd;
                border-radius: 8px;
                margin-bottom: 15px;
                padding: 15px;
                background: #fff;
                box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            }

            .members-table td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            .members-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                font-weight: bold;
                text-align: left;
                color: #2c3e50;
            }
        }
    </style>
</head>
<body>

<div class="members-container">
    <div style="display: flex; justify-content: space-between; align-items: end; margin-bottom: 1rem;">
        <div>
            <h2 class="page-title">Members Management</h2>
            <p class="results-info">
                Showing <?= count($members) ?> of <?= $p->item_count ?> member(s) |
                Page <?= $p->page ?> of <?= $p->page_count ?>
            </p>
        </div>
    </div>

    <!-- Members Table Card -->
    <div class="members-card">
        <table class="members-table">
            <thead>
                <tr>
                    <?php 
                    // Reuse your table_headers function with preserved sorting & pagination
                    table_headers($fields, $sort, $dir, "page=members&page={$p->page}");
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($members): ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td data-label="ID"><?= $member->user_id ?></td>
                            <td data-label="Name"><strong><?= htmlspecialchars($member->username) ?></strong></td>
                            <td data-label="Email"><?= htmlspecialchars($member->email) ?></td>
                            <td data-label="Phone"><?= htmlspecialchars($member->user_phone ?? '-') ?></td>
                            <td data-label="Address"><?= htmlspecialchars($member->user_address ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d; font-size: 1.1rem;">
                            No members found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination using SimplePager -->
    <br>
    <?= $p->html("sort=$sort&dir=$dir&page=members") ?>
</div>

</body>
</html>