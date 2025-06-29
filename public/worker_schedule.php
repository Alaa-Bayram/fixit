<?php
session_start();
$worker_id = $_SESSION['unique_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Worker Schedule</title>
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    
<div class="planner">
    <div class="header">
        <button id="print-schedule" class="print"><i class="fa fa-print" style="color:white;font-size:20px"> Print</i></button>
        <h1>My Schedule</h1>
        <p>Worker ID: <?php echo $worker_id; ?></p>
        <button id="prev-day">&lt;</button>
        <p id="date"></p>
        <button id="next-day">&gt;</button>
    </div>
    <div class="schedule" id="schedule">
        <!-- Schedule slots will be populated by JavaScript -->
    </div>
    <div class="total-cost" id="total-cost"></div>
</div>

<script src="javascript/schedule.js"></script>
</body>
</html>
