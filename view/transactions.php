<?php
session_start();
require_once '../functions/transaction_functions.php';

$transactions = getAllTransactions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Transaction History</h1>
        <nav>
            <a href="./admin/dashboard.php" class="btn">Back to Dashboard</a>
        </nav>
    </header>
    <main>
        <section class="transaction-history">
            <h2>Transactions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item ID</th>
                        <th>User ID</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                            <td><?= htmlspecialchars($transaction['item_id']) ?></td>
                            <td><?= htmlspecialchars($transaction['user_id']) ?></td>
                            <td><?= htmlspecialchars($transaction['type']) ?></td>
                            <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                            <td><?= htmlspecialchars($transaction['date']) ?></td>
                            <td><?= htmlspecialchars($transaction['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>