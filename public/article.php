<?php 
session_start();
include_once "php/db.php";
if(!isset($_SESSION['authenticated'])){
  header("location: login.html");
  exit(); // Important to stop script execution after redirection
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

// Debugging: Output the contents of $row
// Uncomment the following lines to see the contents of $row
// echo "<pre>";
// print_r($row);
// echo "</pre>";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/style1.css">
    <link rel="stylesheet" href="css/stylebtn.css">
</head>
<body>
<button class="chatbot-toggler">
  <a href="users.php"><i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i></a>  
</button>
<div class="article">
    
    <h1><?php echo htmlspecialchars($row['title']); ?></h1>
    <p class="articleparag"><?php echo htmlspecialchars($row['description']); ?></p>
    <img class="image" src="images/<?php echo htmlspecialchars($row['images']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
    <h2><?php echo htmlspecialchars($row['sec_title']); ?></h2>
    <div class="content1">
    <p><?php echo htmlspecialchars($row['content1']); ?></p></div>
    <center>
        <div class="contentArt">
            <h2><?php echo htmlspecialchars($row['tert_title']); ?></h2>
            <pre><?php echo htmlspecialchars($row['content2']); ?></pre>
        </div>
    </center>
</div>

<?php include_once "header.php"; ?>
<?php include_once "footer.html"; ?>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>
