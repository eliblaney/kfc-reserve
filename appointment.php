<?php
  session_start();
  define('BLUEQUEUE');

  if(!isset($_SESSION['user'])) {
  	header('Location: login.php?redirect=reserve.php');
  	die('Please <a href="login.php?redirect=reserve.php">click here</a> to log in.');
  }

  $user = $_SESSION['user'];
  $user_id = $user['customer_id'];

  if(isset($_GET['date'])){
      $date = $_GET['date'];
  }

  if(isset($_POST['submit'])){
      $start = $_POST['start-time'];
      $end = $_POST['end-time'];
      $facility = $_POST['facility'];
      $court = $_POST['court'];
      $classes = $_POST['classes'];
      //Update with Database Connection
      $mysqli = new mysqli('127.0.0.1', 'u301528007_bluequeue', 'BlueQueue551', 'u301528007_bluequeue');
      $stmt = $mysqli->prepare("INSERT INTO appointment (appointment_date, appointment_start_time, appointment_end_time, customer_id, facility_id, class_id, court_id) VALUES (?,?,?,?,?,?,?)");
      $stmt->bind_param('sss', $date, $start, $end, $user_id,$facility,$classes,$court);
      $stmt->execute();
      $msg = "<div class='alert alert-success'>Booking Successfull</div>";
      $stmt->close();
      $mysqli->close();
  }

 ?>

//INSERT
// Pass appointment_date
// Choose appointment_start_time
// Choose appointment_end_time
// Pass customer_id
// Choose facility_id
// Optional: class_id
// Optional: court_id

<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/main.css">
  </head>

  <body>
    <div class="container">
        <h1 class="text-center">Reservation for Date: <?php echo date('F d/Y', strtotime($date)); ?></h1><hr>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
               <?php echo isset($msg)?$msg:''; ?>
                <form action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="">Start Time</label>
                        <input type="time" class="form-control" name="start-time">
                    </div>
                    <div class="form-group">
                        <label for="">End Time</label>
                        <input type="time" class="form-control" name="end-time">
                    </div>
                    <div class="form-group">
                        <label for="">Facility (1- Weights | 2- Cardio)</label>
                        <input type="number" class="form-control" name="facility" min="1" max="2">
                    </div>
                    <div class="form-group">
                        <label for="">Classes (1- Cycling | 2- Boxing)</label>
                        <input type="number" class="form-control" name="classes" min="1" max="2">
                    </div>
                    <div class="form-group">
                        <label for="">Courts (1- Basketball | 2- Volleyball)</label>
                        <input type="number" class="form-control" name="court" min="1" max="2">
                    </div>
                    <button class="btn btn-primary" type="submit" name="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>

</html>