<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Get admin details
$admin = get_admin_details($pdo);

// Set default language and handle language switching
$lang = $_GET['lang'] ?? 'en';
if (!in_array($lang, ['en', 'fr', 'ar'])) {
    $lang = 'en';
}

// Set direction based on language
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

// Process search if submitted
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitize_input($_GET['search']);
    $search_results = search_workers_services($pdo, $search_term);
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons for the dropdown arrow -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <!-- Add CSRF token meta tag -->
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <style>
        /* Language Dropdown Styles */
        .language-dropdown {
            position: relative;
            display: inline-block;
        }

        .language-dropbtn {
            background-color: transparent;
            color: #333;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .language-dropbtn:hover {
            background-color: #f1f1f1;
        }

        .language-dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 120px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            <?php echo ($dir === 'rtl') ? 'left: 0;' : 'right: 0;'; ?>
        }

        .language-dropdown-content a {
            color: #333;
            padding: 8px 12px;
            text-decoration: none;
            display: block;
            text-align: <?php echo ($dir === 'rtl') ? 'right' : 'left'; ?>;
        }

        .language-dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .language-dropdown-content a.active {
            background-color: #ddd;
            font-weight: bold;
        }

        .language-dropdown:hover .language-dropdown-content {
            display: block;
        }

        /* RTL specific adjustments */
        [dir="rtl"] .sidebar-button,
        [dir="rtl"] .search-form,
        [dir="rtl"] .profile-details {
            float: right;
        }

        [dir="rtl"] .search-box {
            margin-left: 20px;
            margin-right: 0;
        }

        [dir="rtl"] .profile-details {
            margin-left: 20px;
            margin-right: auto;
        }
    </style>
</head>

<body>
    <?php include_once "includes/sidebar.php"; ?>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard"><?= $trans['dashboard']?></span>
            </div>

            <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form">
                <div class="search-box">
                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                    <input type="text" name="search" placeholder="<?php echo isset($trans['search_placeholder']) ? $trans['search_placeholder'] : 'Search for workers or services'; ?>"
                        value="<?php echo isset($_GET['search']) ? h($_GET['search']) : ''; ?>" />
                    <button type="submit"><i class="bx bx-search"></i></button>
                </div>
            </form>
            
            <div class="language-dropdown" style="margin-<?php echo ($dir === 'rtl') ? 'left' : 'right'; ?>: 20px;">
                <button class="language-dropbtn">
                    <span class="current-language">
                        <?= match($lang) {
                            'en' => '🌐 EN',
                            'fr' => '🌐 FR',
                            'ar' => '🌐 عربي',
                            default => '🌐 EN'
                        } ?>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="language-dropdown-content">
                    <?php
                    // Preserve all GET parameters when switching languages
                    $queryParams = $_GET;
                    foreach (['en', 'fr', 'ar'] as $language) {
                        $queryParams['lang'] = $language;
                        $activeClass = $lang === $language ? 'active' : '';
                        echo '<a href="?' . http_build_query($queryParams) . '" class="' . $activeClass . '">';
                        echo match($language) {
                            'en' => 'English',
                            'fr' => 'Français',
                            'ar' => 'العربية',
                            default => 'English'
                        };
                        echo '</a>';
                    }
                    ?>
                </div>
            </div>

            <?php if ($admin): ?>
                <div class="profile-details">
                    <img src="../public/images/<?php echo h($admin['img'] ?? 'default-profile.jpg'); ?>" alt="Profile" />
                    <span class="admin_name"><?php echo h($admin['fname'] . ' ' . $admin['lname']); ?></span>
                    <i class="bx bx-chevron-down"></i>
                    <div class="dropdown-menu">
                        <a href="profile.php"><i class="bx bx-user"></i><?php echo isset($trans['profile']) ? $trans['profile'] : 'Profile'; ?></a>
                        <a href="logout.php"><i class="bx bx-log-out"></i> <?php echo isset($trans['logout']) ? $trans['logout'] :'Logout'; ?></a>
                    </div>
                </div>
            <?php endif; ?>
        </nav>

        <?php if (!empty($search_results)): ?>
            <div class="search-results">
                <h3><?php echo ($lang === 'ar') ? 'نتائج البحث عن:' : 'Search Results for:'; ?> <?php echo h($_GET['search']); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo ($lang === 'ar') ? 'الاسم' : 'Name'; ?></th>
                            <th><?php echo ($lang === 'ar') ? 'المهارات' : 'Skills'; ?></th>
                            <th><?php echo ($lang === 'ar') ? 'الخدمة' : 'Service'; ?></th>
                            <th><?php echo ($lang === 'ar') ? 'الإجراءات' : 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $result): ?>
                            <tr>
                                <td><?php echo h($result['fname'] . ' ' . $result['lname']); ?></td>
                                <td><?php echo h($result['skills'] ?? 'N/A'); ?></td>
                                <td><?php echo h($result['service_title'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="worker_detail.php?id=<?php echo h($result['user_id']); ?>" class="btn btn-view">
                                        <i class="bx bx-show"></i> <?php echo ($lang === 'ar') ? 'عرض' : 'View'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        