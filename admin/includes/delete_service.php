<?php
include('config.php');
    $id = $_POST['service_id'];
    $query="DELETE FROM services where service_id = '$id'" ;
    if(mysqli_query($conn,$query)){
    header('location: ../all_services.php');
    exit;}
    else{
    echo"Error deleting record: ".mysqli_error($conn);
    }

?>