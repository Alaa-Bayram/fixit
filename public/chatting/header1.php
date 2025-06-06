<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>FixIt</title>
  <link rel="stylesheet" href="../css/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<div class="header1">
    <nav class="navbar">
        <div class="logo"><a href="#"><img src="../app_images/logo1.png"></a></div>
        <input type="checkbox" id="menu-toggler">
        <label for="menu-toggler" id="hamburger-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
                <path d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </label>
            <ul class="all-links">
                <?php
                // Function to generate navigation links with preserved parameters
                function navLink($page, $icon, $text) {
                    global $lang;
                    $currentParams = $_GET;
                    $url = "$page.php?" . http_build_query(array_merge($currentParams, ['lang' => $lang]));
                    echo "<li><a href=\"$url\"><i class=\"$icon\"></i> $text</a></li>";
                }
                
                // Navigation items with translations
                navLink('../home', 'fas fa-home', $translations['home'] ?? 'Home');
                navLink('../services', 'fas fa-tools', $translations['services'] ?? 'Services');
                navLink('../tips', 'fas fa-lightbulb', $translations['tips'] ?? 'Tips');
                navLink('../contact', 'fas fa-envelope', $translations['contact'] ?? 'Contact');
                navLink('../profile', 'fas fa-user', $translations['profile'] ?? 'Profile');
                ?>
                
                <li class="language-selector">
                    <div class="dropdown">
                        <button class="dropbtn">
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
                        <div class="dropdown-content">
                            <?php
                            $languages = [
                                'en' => $translations['english'] ?? 'English',
                                'ar' => $translations['arabic'] ?? 'العربية', 
                                'fr' => $translations['french'] ?? 'Français'
                            ];
                            
                            foreach ($languages as $code => $name) {
                                $urlParams = array_merge($_GET, ['lang' => $code]);
                                $activeClass = ($lang === $code) ? 'active' : '';
                                echo "<a href='?".http_build_query($urlParams)."' class='$activeClass'>$name</a>";
                            }
                            ?>
                        </div>
                    </div>
                </li>
                
                <li><a href="#" class="logout" onclick="logout()"><?= $translations['logout'] ?? 'Logout' ?></a></li>
    </ul>
    </nav>
    </div>
    
    