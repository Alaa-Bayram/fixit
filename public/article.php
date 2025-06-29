<?php 
session_start();
include_once "php/db.php";
if(!isset($_SESSION['authenticated'])){
  header("location: login.html");
  exit();
}

// Language selection (default to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$lang_file = "lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("lang/en.php"); // fallback
}

if (!isset($_GET['article_id'])) {
    echo "No article ID provided.";
    exit();
}

$article_id = intval($_GET['article_id']);

$query = "SELECT * FROM articles WHERE article_id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars(mysqli_error($conn)));
}

mysqli_stmt_bind_param($stmt, 'i', $article_id);

if (mysqli_stmt_execute($stmt) === false) {
    die("Execute failed: " . htmlspecialchars(mysqli_stmt_error($stmt)));
}

$result = mysqli_stmt_get_result($stmt);

if ($result === false) {
    die("Get result failed: " . htmlspecialchars(mysqli_stmt_error($stmt)));
}

if (mysqli_num_rows($result) == 0) {
    echo "Article not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);

// Function to get the appropriate field based on language
function getTranslatedField($row, $field, $lang) {
    $langField = $field . '_' . $lang;
    return (!empty($row[$langField])) ? $row[$langField] : $row[$field];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getTranslatedField($row, 'title', $lang)); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/article.css">
    <?php if ($lang === 'ar') { ?>
        <link rel="stylesheet" href="css/ar.css">
    <?php } ?>
</head>
<body>
<?php include_once "header.php"; ?>

<div class="article-container">
    <div class="article-header">
        <h1 class="article-title"><?php echo htmlspecialchars(getTranslatedField($row, 'title', $lang)); ?></h1>
        <div class="article-meta">
            <span>
                    <i class="far fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($row['date'])); ?></span>
        </div>
    </div>

    <div class="article-content">
        <p class="article-paragraph"><?php echo htmlspecialchars(getTranslatedField($row, 'description', $lang)); ?></p>
        
        <img class="article-image" src="images/<?php echo htmlspecialchars($row['images']); ?>" alt="<?php echo htmlspecialchars(getTranslatedField($row, 'title', $lang)); ?>">
        
        <h2 class="article-section-title"><?php echo htmlspecialchars(getTranslatedField($row, 'sec_title', $lang)); ?></h2>
        
        <pre class="article-pre"><?php echo htmlspecialchars(getTranslatedField($row, 'content1', $lang)); ?></pre>
        
        <div class="article-highlight">
            <h2 class="article-section-title"><?php echo htmlspecialchars(getTranslatedField($row, 'tert_title', $lang)); ?></h2>
            <pre class="article-pre"><?php echo htmlspecialchars(getTranslatedField($row, 'content2', $lang)); ?></pre>
        </div>
    </div>

    <div class="article-footer">
        <a href="tips.php?lang=<?= $lang ?>" class="back-to-articles"><?php echo $translations['back_to_articles']; ?></a>
    </div>
</div>

<?php include_once "footer.html"; ?>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>