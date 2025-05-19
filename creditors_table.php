<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

if (!isset($_SESSION['company_id'])) {
    die("Unauthorized access");
}

$companyId = $_SESSION['company_id'];
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$query = "SELECT 
            dp.debt_id,
            dp.name, 
            SUM(dp.total) AS total_credit,
            SUM(dp.due_amount) AS current_due_amount,
            MAX(dp.date_created) AS last_credit_date,
            COUNT(*) AS credit_count
          FROM debt_payments dp
          WHERE dp.company_id = ? AND dp.debtor_creditor = 'creditor'
          GROUP BY dp.name, dp.debt_id
          ORDER BY current_due_amount DESC
          LIMIT ? OFFSET ?";

// Prepare the statement
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

// Bind parameters
mysqli_stmt_bind_param($stmt, "iii", $companyId, $itemsPerPage, $offset);

// Execute query
if (!mysqli_stmt_execute($stmt)) {
    die("Execute failed: " . mysqli_stmt_error($stmt));
}

// Get result
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Get result failed: " . mysqli_error($conn));
}
?>

<table id="creditors-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Credit</th>
            <th>Due Amount</th>
            <th>Last Credit Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)): ?>
                <tr data-debt-id="<?= htmlspecialchars($row['debt_id']) ?>">
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>Tsh <?= number_format($row['total_credit'], 2) ?></td>
                    <td>Tsh <?= number_format($row['current_due_amount'], 2) ?></td>
                    <td><?= date('M d, Y', strtotime($row['last_credit_date'])) ?></td>
                    <td>
                        <span class="status-badge status-<?= $row['current_due_amount'] > 0 ? 'due' : 'paid' ?>">
                            <?= $row['current_due_amount'] > 0 ? 'Due' : 'Paid' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn mark-paid" data-name="<?= htmlspecialchars($row['name']) ?>">
                                <i class='bx bx-check'></i>
                            </button>
                            <button class="action-btn view" data-name="<?= htmlspecialchars($row['name']) ?>">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="action-btn edit" data-name="<?= htmlspecialchars($row['name']) ?>">
                                <i class='bx bx-edit'></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; 
        } else {
            echo '<tr><td colspan="6">No credits found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
// Close statement
mysqli_stmt_close($stmt);
?>