<div class="table-responsive">
    <table id="transactions-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td class="type-<?= $transaction['transaction_type'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $transaction['transaction_type'])) ?>
                </td>
                <td><?= number_format($transaction['amount'], 2) ?></td>
                <td><?= htmlspecialchars($transaction['description']) ?></td>
                <td><?= date('M j, Y H:i', strtotime($transaction['date_made'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="pagination">
    <button id="prev-page" <?= $page <= 1 ? 'disabled' : '' ?>>
        <i class='bx bx-chevron-left'></i>
    </button>
    <span id="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
    <button id="next-page" <?= $page >= $totalPages ? 'disabled' : '' ?>>
        <i class='bx bx-chevron-right'></i>
    </button>
</div>