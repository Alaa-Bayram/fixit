<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

try {
    // SQL query to count articles
    $count_query = "SELECT COUNT(*) AS article_count FROM articles";
    $stmt = $pdo->query($count_query);
    $row_count = $stmt->fetch();
    $article_count = $row_count['article_count'];

    // SQL query to fetch latest two articles
    $articles_query = "SELECT * FROM articles ORDER BY date DESC LIMIT 5";
    $stmt = $pdo->query($articles_query);
    $articles = $stmt->fetchAll();

    // Fetch all articles
    $all_articles_query = "SELECT * FROM articles ORDER BY article_id DESC";
    $stmt = $pdo->query($all_articles_query);
    $all_articles = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    // Initialize with empty arrays/values if queries fail
    $article_count = 0;
    $articles = [];
    $all_articles = [];
}
?>