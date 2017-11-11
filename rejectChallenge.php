<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  try
  {
    $statement = $db->prepare("delete from challenge where challenger = :challenger and challengee = :challengee and scheduled = :scheduled");
    $statement->execute(array(':challenger'=>$_POST['challenger'], ':challengee'=>$_SESSION['username'], ':scheduled'=>$_POST['scheduled']));
  }
  catch (PDOException $e)
  {
    print "A connection error has occurred.<br/>";
    die();
  }
  
  header('Location: welcome.php');
?>