<?php
// Start session and check user's session
session_start();
define('BLUEQUEUE', true);

// Redirect to login if not logged in
if(!isset($_SESSION['user'])) {
	header('Location: login.php?redirect=reserve.php');
	die('Please <a href="login.php?redirect=reserve.php">click here</a> to log in.');
}

$user = $_SESSION['user'];
?>

<?php
//Function to build a calendar
function build_calendar($month,$year){
	//Connect to Bluequeue DB
	$mysqli = new mysqli('127.0.0.1', 'u301528007_bluequeue', 'BlueQueue551', 'u301528007_bluequeue');
	//Select all appointments in the appointment table
	$stmt=$mysqli->prepare("SELECT * FROM appointment WHERE MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?");
	$stmt->bind_param('ss',$month,$year); // s = string
	//Create array containing all reservations
	$reservations = array();
	if($stmt->execute()){
		$result = $stmt->get_result();
		if($result->num_rows>0){
			while($row = $result->fetch_assoc()){
				//Insert appointment date into the reservations array
				$reservations[] = $row['appointment_date'];
			}
		}
		$stmt->close();
	}

	//Array containing names of all the days in a week
	$daysOfWeek=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	//Get the first day of the month, that's an argument of this function
	$firstDayOfMonth=mktime(0,0,0,$month,1,$year);
	//Getting the number of days this month contains
	$numberDays=date('t',$firstDayOfMonth);
	//Getting some information about the first day pd this month
	$dateComponents=getdate($firstDayOfMonth);
	//Getting the name of this month
	$monthName=$dateComponents['month'];
	//Getting the index value 0-6 of the first day of this $month
	$dayOfWeek=$dateComponents['wday'];
	//Getting the current date
	$dateToday=date('Y-m-d');

	//Now creating HTML table
	$calendar="<table class='table table-bordered'>";
	$calendar.="<center><h2>$monthName $year</h2>";
	//Previos Month
	$calendar.="<a class='btn btn-xs btn-primary' href='?month=".date('m',mktime(0,0,0,$month-1,1,$year))."&year=".date('Y',mktime(0,0,0,$month-1,1,$year))."'>Previous Month</a> ";

	//Current Month
	$calendar.="<a class='btn btn-xs btn-primary' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a> ";

	//Next Month
	$calendar.="<a class='btn btn-xs btn-primary' href='?month=".date('m',mktime(0,0,0,$month+1,1,$year))."&year=".date('Y',mktime(0,0,0,$month+1,1,$year))."'>Next Month</a></center><br>";

	$calendar.="<tr>";

	//Creating the calendar headers
	foreach($daysOfWeek as $day){
		$calendar.="<th class='header'>$day</th>";
	}

	$calendar.="</tr><tr>";
	//The variable $dayOfWeek will make sure that there must be only 7 colums on our table
	if($dayOfWeek > 0){
		for($i=0;$i<$dayOfWeek;$i++){
			$calendar.="<td></td>";
		}
	}
	//Initiation the day counter
	$currentDay=1;

	//Getting the month number
	$month=str_pad($month,2,"0",STR_PAD_LEFT);

	while($currentDay <= $numberDays){

		//if 7th column(saturday) reached start a new row

		if($dayOfWeek == 7){
			$dayOfWeek = 0;
			$calendar.="</tr><tr>";
		}

		$currentDayRel=str_pad($currentDay,2,"0",STR_PAD_LEFT);
		$date="$year-$month-$currentDayRel";

		$dayname = strtolower(date('l',strtotime($date)));
		$eventNum=0;
		$today = $date==date('Y-m-d')? "today" : "";
		// Show Not Available and Already Reserved buttons so that users can't make any new appointments
		if($date<date('Y-m-d')){
			$calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Not Available</button>";
		}elseif(in_array($date,$reservations)){
			$calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Already Reserved</button>";
		} // Else show Reserve so that they are redirected to appointment.php to make a reservation
		else{
			$calendar.="<td class='$today'><h4>$currentDay</h4> <a href='appointment.php?date=".$date."' class='btn btn-sucess btn-xs'>Reserve</a>";
		}


		$calendar.="</td>";

		//Incrementing the counters
		$currentDay++;
		$dayOfWeek++;
	}
	//Completing the row of thee last week in month, if necessary
	if($dayOfWeek != 7){
		$remainingDays=7-$dayOfWeek;
		for($k=0;$k<$remainingDays;$k++){
			$calendar.="<td></td>";
		}
	}
	$calendar.="</tr>";
	$calendar.="</table>";
	return $calendar;
	}
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Title + Description of the page -->
		<title>BlueQueue Reserve</title>
		<meta name="description" content="Reserve KFC times and more with BlueQueue!">
		<meta name="author" content="Eli Blaney & Gisselle Estevez">
		<!--Bootstrap Links-->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
		<!--CSS stylesheet link to reserve.css-->
		<link rel="stylesheet" href="assets/css/reserve.css?v=1.0.1">

	</head>
	<body>
		<div class="container-fluid p-0">
			<!-- Top of the Page -->
			<div class="cu-heading cu-border-top">
				<img id="logo" src="assets/img/logo.png" alt="Creighton Logo">
				<div class="nav-buttons">
					<a href="index.php" class="btn btn-primary">Home</a>
					<?php //Show logout if the user has already log in
					if($user) {
					?>
						<a href="login.php?logout=true" class="btn btn-primary" data-tln="logout">Log out</a>
					<?php // Show log in if the user has not log in yet
					} else {
					?>
						<a href="reserve.php" class="btn btn-primary" data-tln="login">Log in</a>
					<?php
					}
					?>
				</div>
			</div>

			<div class="main-content mb-5">
				<!-- Main content -->
				<h1>Make Reservation</h1>

				<?php
					// Get values + call build_calendar
					$month = $_GET['month'];
					$year = $_GET['year'];
					if(!isset($_GET['month']) || !isset($_GET['year'])) {
						$dateComponents=getdate();
						$month=$dateComponents['mon'];
						$year=$dateComponents['year'];
					}
					// Print the calendar on the page
					echo build_calendar($month,$year);
				?>

			</div>
		</div>
			<!-- Bottom of page -->
			<div class="cu-heading cu-border-bottom">
				<img class="logo" src="assets/img/logo.png" alt="Creighton Logo">
				<div class="links ml-5">
					<!--Links to navigate through the pages -->
					<a href="index.php">Home</a>
					<a href="about.php">About</a>
					<a href="reserve.php">Reserve</a>
<?php // If the user is an Admin show the admin link at the bottom of the page
if($user && $user['admin']) {
?>
					<a href="admin.php">Administration</a>
<?php
}
?>
				</div>
			</div>
		</div>
		<!--Bootstrap Links-->
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
	</body>
</html>
