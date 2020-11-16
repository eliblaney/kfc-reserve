<?php
session_start();
define('BLUEQUEUE', true);

if(!isset($_SESSION['user'])) {
	header('Location: login.php?redirect=admin.php');
	die('Please <a href="login.php?redirect=admin.php">click here</a> to log in.');
}

$user = $_SESSION['user'];

if(false && strcmp($user['admin'],'1')) {
	// Not authorized
	header('Location: index.php');
	die('You are not authorized to view this page.<br>Please <a href="login.php">click here</a> to return to the home page.');
}

require('db.php');
$conn = connectDB();

if(count($_POST)) {
	if(isset($_POST['delete'])) {
		$id = $_POST['delete'];
		$q = "DELETE FROM appointment WHERE appointment_id='$id'";
		mysqli_query($conn, $q);
	}
	if(isset($_POST['update'])) {
		$id = $_POST['update'];
		$customer = $_POST['customer_id'.$id];
		$date = $_POST['appointment_date'.$id];
		$start = $_POST['appointment_start_time'.$id];
		$end = $_POST['appointment_end_time'.$id];
		$facility = $_POST['facility_id'.$id];
		$class = $_POST['class_id'.$id];
		$court = $_POST['court_id'.$id];
		$q = "UPDATE appointment SET customer_id='$customer', appointment_date='$date', appointment_start_time='$start', appointment_end_time='$end', facility_id='$facility', class_id='$class', court_id='$court' WHERE appointment_id='$id'";
		mysqli_query($conn, $q);
	}
}

$q = "SELECT appointment.appointment_id, appointment_date, appointment_start_time, appointment_end_time, appointment.customer_id, appointment.facility_id, appointment.class_id, appointment.court_id, first_name, last_name, facility_type, class_type, court_type FROM appointment JOIN customer ON customer.customer_id=appointment.customer_id JOIN facility ON appointment.facility_id=facility.facility_id JOIN classes ON appointment.class_id=classes.class_id JOIN court ON appointment.court_id=court.court_id ORDER BY appointment_date ASC";
$result = mysqli_query($conn, $q);
$appointments = null;
if(mysqli_num_rows($result) > 0) {
	$appointments = [];
	while($row = mysqli_fetch_assoc($result)) {
		array_push($appointments, $row);
	}
}

function buildInput($id, $name, $label, $value) {
	if(!$value) {
		$value = $label;
	}
	return "<label>$label<input type='text' name='$name$id' value='$value' size='".(max(strlen($value),10))."' /></label>";
}

function showAppointments() {
	global $appointments;
	echo "<table><tr><th>Customer</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Facility</th><th>Class</th><th>Court</th><th>Operations</th></tr>";
	foreach($appointments as $a) {
		$id = $a['appointment_id'];
		$customer = buildInput($id, 'customer_id', $a['first_name'].' '.$a['last_name'], $a['customer_id']);
		$date = buildInput($id, 'appointment_date', $a['appointment_date']);
		$start = buildInput($id, 'appointment_start_time', $a['appointment_start_time']);
		$end = buildInput($id, 'appointment_end_time', $a['appointment_end_time']);
		$facility = buildInput($id, 'facility_id', $a['facility_type'], $a['facility_id']);
		$class = buildInput($id, 'class_id', $a['class_type'], $a['class_id']);
		$court = buildInput($id, 'court_id', $a['court_type'], $a['court_id']);
		$buttons = "<button type='submit' name='update' value='$id' class='btn btn-info'>Update</button><button type='submit' name='delete' value='$id' class='btn btn-danger'>Delete</button>";
		echo "<tr><td>$customer</td><td>$date</td><td>$start</td><td>$end</td><td>$facility</td><td>$class</td><td>$court</td><td>$buttons</td></tr>";
	}
	echo "</table>";
}

function printActiveUsers() {
	global $conn;
	$q = "SELECT first_name, last_name, COUNT(*) AS num FROM appointment JOIN customer ON customer.customer_id=appointment.customer_id GROUP BY appointment.customer_id";
	$result = mysqli_query($conn, $q);
	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$name = $row['first_name'].' '.$row['last_name'];
			$num = $row['num'];
			echo "['$name', $num],";
		}
	}
}

?>
<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<title>Administration</title>
		<meta name="description" content="Reserve KFC times and more with BlueQueue!">
		<meta name="author" content="Eli Blaney & Gisselle Estevez">

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
		<link rel="stylesheet" href="assets/css/reserve.css?v=1.0.1">

		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(drawChart);

			function drawChart() {
				var data = google.visualization.arrayToDataTable([
					['Name', 'Upcoming Appointments'],
<?php printActiveUsers(); ?>
				]);

				var options = {
					title: 'Upcoming Appointments'
				};

				var chart = new google.visualization.PieChart(document.getElementById('piechart'));

				chart.draw(data, options);
		  }
		</script>
	</head>
	<body>
		<div class="container-fluid p-0">
			<div class="cu-heading cu-border-top">
				<img id="logo" src="assets/img/logo.png" alt="Creighton Logo">
				<div class="nav-buttons">
					<a href="index.php" class="btn btn-primary">Home</a>
<?php
if($user) {
?>
					<a href="login.php?logout=true" class="btn btn-primary" data-tln="logout">Log out</a>
<?php
} else {
?>
					<a href="reserve.php" class="btn btn-primary" data-tln="login">Log in</a>
<?php
}
?>
				</div>
			</div>

			<div class="main-content mb-5">
				<h1>Administration</h1>
				<form method="POST" action="">
					<?php showAppointments(); ?>
				</form>
				<h1>Active Users</h1>
				<div id="piechart" style="width: 900px; height: 500px;"></div>
			</div>

			<div class="cu-heading cu-border-bottom">
				<img class="logo" src="assets/img/logo.png" alt="Creighton Logo">
				<div class="links ml-5">
					<a href="index.php">Home</a>
					<a href="about.php">About</a>
					<a href="reserve.php">Reserve</a>
<?php
if($user && $user['admin']) {
?>
					<a href="admin.php">Administration</a>
<?php
}
?>
				</div>
			</div>
		</div>

		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
	</body>
</html>
