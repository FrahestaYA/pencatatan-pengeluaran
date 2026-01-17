<?php
// includes/functions.php

function getBalance($pdo, $user_id)
{
    // Income
    $stmt = $pdo->prepare("
        SELECT SUM(amount) FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.user_id = ? AND c.type = 'income'
    ");
    $stmt->execute([$user_id]);
    $income = $stmt->fetchColumn() ?: 0;

    // Expense
    $stmt = $pdo->prepare("
        SELECT SUM(amount) FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.user_id = ? AND c.type = 'expense'
    ");
    $stmt->execute([$user_id]);
    $expense = $stmt->fetchColumn() ?: 0;

    return $income - $expense;
}

function getMonthlySummary($pdo, $user_id, $month = null)
{
    if (!$month)
        $month = date('Y-m');

    // Income
    $stmt = $pdo->prepare("
        SELECT SUM(amount) FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.user_id = ? AND c.type = 'income' AND DATE_FORMAT(e.date, '%Y-%m') = ?
    ");
    $stmt->execute([$user_id, $month]);
    $income = $stmt->fetchColumn() ?: 0;

    // Expense
    $stmt = $pdo->prepare("
        SELECT SUM(amount) FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.user_id = ? AND c.type = 'expense' AND DATE_FORMAT(e.date, '%Y-%m') = ?
    ");
    $stmt->execute([$user_id, $month]);
    $expense = $stmt->fetchColumn() ?: 0;

    return ['income' => $income, 'expense' => $expense];
}

function getRecentTransactions($pdo, $user_id, $limit = 5)
{
    $stmt = $pdo->prepare("
        SELECT e.*, c.name as category_name, c.type as category_type, c.icon 
        FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.user_id = ? 
        ORDER BY e.date DESC, e.id DESC 
        LIMIT $limit
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getCategoryExpenses($pdo, $user_id, $month = null)
{
    if (!$month)
        $month = date('Y-m');

    $stmt = $pdo->prepare("
        SELECT c.name, c.icon, SUM(e.amount) as total 
        FROM expenses e
        JOIN categories c ON e.category_id = c.id
        WHERE e.user_id = ? AND c.type = 'expense' AND DATE_FORMAT(e.date, '%Y-%m') = ?
        GROUP BY c.id
        ORDER BY total DESC
    ");
    $stmt->execute([$user_id, $month]);
    return $stmt->fetchAll();
}

function formatRupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}
?>