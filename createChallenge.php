<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  $format = 'Y#m#d H:i';
  $dateTime = DateTime::createFromFormat($format, $_POST['date'] . ' ' . $_POST['time']);
  
  try
  {
    $statement = $db->prepare("insert into challenge values(:challenger, :challengee, CURRENT_DATE, null, :scheduled)");
    $statement->execute(array(':challenger'=>$_SESSION['username'], ':challengee'=>$_SESSION['challengee'], ':scheduled'=>$dateTime->format($format)));
  }
  catch (PDOException $e)
  {
    print "A connection error has occurred.<br/>";
    die();
  }
  
  // Go back to the welcome page.
  header('Location: welcome.php');
?>