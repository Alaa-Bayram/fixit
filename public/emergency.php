<?php
include_once "php/session_check.php";
include_once "php/fetch_services.php";

// Set language from URL or session
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Include translations
$lang_file = "lang/$lang.php";
$translations = file_exists($lang_file) ? include($lang_file) : include("lang/en.php");
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['emergency_title'] ?? 'Emergency Help' ?></title>
    <link rel="stylesheet" href="css/emergency.css">
    <link rel="stylesheet" href="css/ar.css">
    <?php include_once "header.php"; ?>
    <style>
        /* Hide video container by default */
        #videoContainer {
            display: none;
            margin-top: 20px;
        }

        /* Display captured image */
        #capturedImage {
            margin-top: 20px;
            max-width: 100%;
            height: auto;
        }

        /* Hide the delete image button if there's no captured image */
        .camera-controls #deleteImageButton {
            display: none;
        }

        
    </style>
</head>
<body>
    <h1 class="emHeader"><i class="bi bi-exclamation-triangle-fill" style="color: rgb(255, 81, 0);font-size: 30px;"></i> <?= $translations['emergency_heading'] ?? 'Emergency Help' ?></h1> 
    <p class="emNote"><?= $translations['emergency_note'] ?? 'If you\'re facing an <b>urgent</b> problem that needs immediate attention, please fill out the form below.<br> Our <b>available workers</b> will respond as quickly as possible to assist you.' ?></p>
    <center><button type="submit" class="sub"><a href="client_emergencies.php?lang=<?= $lang ?>"><?= $translations['your_emergencies'] ?? 'Your Emergencies' ?> <i class="bi bi-cursor-fill icon-left"></i></a></button></center>
    <div class="emForm">
        <form id="emergencyForm" action="php/submit_emergency.php?lang=<?= $lang ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="service"><?= $translations['service_needed'] ?? 'Service Needed' ?> :</label>
                <select name="service" id="service" required>
                    <option value=""><?= $translations['select_service'] ?? 'Select Service' ?></option>
                    <?php echo $services_options; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description" class="desc"><?= $translations['problem_description'] ?? 'Problem Description' ?> :</label>
                <textarea name="description" id="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="address"><?= $translations['address'] ?? 'Address' ?> :</label>
                <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone"><?= $translations['phone_number'] ?? 'Phone Number' ?> :</label>
                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <label for="region"><?= $translations['region'] ?? 'Region' ?> :</label>
                <input type="text" name="region" id="region" value="<?php echo htmlspecialchars($region); ?>" required>
            </div>
            <div class="form-group">
                <label for="image"><?= $translations['upload_image'] ?? 'Upload Image (optional)' ?> :</label>
                <input type="file" name="image" id="image" accept="image/*">
                <input type="hidden" id="capturedImageData" name="captured_image_data">
                <button type="button" class="camera-button" id="deleteImageButton" onclick="deleteImage()" style="display: none;margin-top:30px"><?= $translations['delete_image'] ?? 'Delete Image' ?></button>
                <div id="imageName"></div>
            </div>
            
            <div id="videoContainer">
                <video id="video" width="100%" height="auto" autoplay></video>
                <img id="capturedImage" src="" alt="<?= $translations['captured_image'] ?? 'Captured Image' ?>" style="display: none;">
            </div>
            <div class="camera-controls">
                <button type="button" class="camera-button" id="startCameraButton" onclick="showCamera()"><?= $translations['take_live_photo'] ?? 'Take a Live Photo' ?></button>
                <button type="button" class="camera-button" id="captureButton" onclick="captureImage()" style="display: none;"><?= $translations['capture_image'] ?? 'Capture Image' ?></button>
                <button type="button" class="camera-button" id="deleteImageButton" onclick="deleteImage()"><?= $translations['delete_image'] ?? 'Delete Image' ?></button>
            </div>

            <button type="submit" class="submit"><?= $translations['submit_emergency'] ?? 'Submit Emergency' ?></button>
        </form>
    </div>
    <script src="javascript/emergency.js"></script>
    <script>
        document.getElementById('emergencyForm').addEventListener('submit', function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            formData.append('lang', '<?= $lang ?>');

            fetch('php/submit_emergency.php?lang=<?= $lang ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?= $translations['emergency_submitted'] ?? 'Emergency submitted successfully!' ?>');
                    window.location.href = 'client_emergencies.php?lang=<?= $lang ?>';
                } else {
                    alert(data.message || '<?= $translations['submission_error'] ?? 'An error occurred while submitting your emergency. Please try again later.' ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= $translations['submission_error'] ?? 'An error occurred while submitting your emergency. Please try again later.' ?>');
            });
        });
    </script>
</body>
</html>