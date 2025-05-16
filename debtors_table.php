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
            name, 
            SUM(total) AS total_debt,
            SUM(due_amount) AS current_due_amount,
            MAX(date_created) AS last_debt_date,
            COUNT(*) AS debt_count
          FROM debt_payments 
          WHERE company_id = $companyId AND debtor_creditor = 'debtor'
          GROUP BY name 
          ORDER BY current_due_amount DESC
          LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<table id="debtors-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Debt</th>
            <th>Due Amount</th>
            <th>Last Debt Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>Tsh <?= number_format($row['total_debt'], 2) ?></td>
                <td>Tsh <?= number_format($row['current_due_amount'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($row['last_debt_date'])) ?></td>
                <td>
                    <span class="status-badge status-<?= $row['current_due_amount'] > 0 ? 'due' : 'paid' ?>">
                        <?= $row['current_due_amount'] > 0 ? 'Due' : 'Paid' ?>
                    </span>
                </td>
                <td>
                    <button class="action-btn mark-paid" data-name="<?= htmlspecialchars($row['name']) ?>">
                        <i class='bx bx-check'></i>
                    </button>
                    <button class="action-btn view" data-name="<?= htmlspecialchars($row['name']) ?>">
                        <i class='bx bx-show'></i>
                    </button>
                    <button class="action-btn edit" data-name="<?= htmlspecialchars($row['name']) ?>">
                        <i class='bx bx-edit'></i>
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>