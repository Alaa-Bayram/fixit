<?php 
include_once "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['service_id']) && !isset($_POST['update_service'])) {
        $service_id = $_POST['service_id'];

        // Fetch the existing service details
        $query = "SELECT * FROM services WHERE service_id = $service_id";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $service = mysqli_fetch_assoc($result);
        } else {
            die("Service not found.");
        }
    } elseif (isset($_POST['update_service'])) {
        // Handle the update logic here
        $service_id = $_POST['service_id'];
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Handle image update
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $img_name = $_FILES['image']['name'];
            $img_type = $_FILES['image']['type'];
            $tmp_name = $_FILES['image']['tmp_name'];
            
            $img_explode = explode('.', $img_name);
            $img_ext = end($img_explode);

            $extensions = ["jpeg", "png", "jpg"];
            if (in_array($img_ext, $extensions)) {
                $types = ["image/jpeg", "image/jpg", "image/png"];
                if (in_array($img_type, $types)) {
                    $time = time();
                    $new_img_name = $time . $img_name;
                    if (move_uploaded_file($tmp_name, "../../public/images/" . $new_img_name)) {
                        $images = $new_img_name;
                        $query = "UPDATE services SET title = '$title', description = '$description', images = '$images' WHERE service_id = $service_id";
                    } else {
                        die("Error: Failed to move uploaded file!");
                    }
                } else {
                    die("Error: Please upload an image file - jpeg, png, jpg!");
                }
            } else {
                die("Error: Please upload an image file - jpeg, png, jpg!");
            }
        } else {
            $query = "UPDATE services SET title = '$title', description = '$description' WHERE service_id = $service_id";
        }

        if (mysqli_query($conn, $query)) {
            header("Location: ../all_services.php");
            exit();
        } else {
            die("Update failed: " . mysqli_error($conn));
        }
    } else {
        die("Invalid request.");
    }
}
?>