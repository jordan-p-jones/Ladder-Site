<?php
  include("authenticate.php");
  include("dbConnection.php");

  // Accepting a challenge updates the challenge entry and removes all other unaccepted challenge entries in which 
  // the user participates.
  try
  {
    // Make sure challenge still exists, and if it does not, send back to welcome page with alert.
    $statement = $db->prepare("select * from challenge where challenger = :challenger and challengee = :challengee and scheduled = :scheduled");
    $statement->execute(array(':challenger'=>$_POST['challenger'], ':challengee'=>$_SESSION['username'], ':scheduled'=>$_POST['scheduled']));
    if ($statement->rowCount() < 1)
    {
      echo "
        <script type='text/javascript'>
          alert('That challenge is no longer available.');
          window.location.assign('welcome.php');
        </script>";
    }
    else
    {
      $statement = $db->prepare("update challenge set accepted = CURRENT_DATE where challenger = :challenger and challengee = :challengee and scheduled = :scheduled");
      $statement->execute(array(':challenger'=>$_POST['challenger'], ':challengee'=>$_SESSION['username'], ':scheduled'=>$_POST['scheduled']));
      $statement = $db->prepare("delete from challenge where (challenger = :user or challengee = :user) and accepted isNull");
      $statement->execute(array(':user'=>$_SESSION['username']));
    }
  }
  catch (PDOException $e)
  {
    print "A connection error has occurred.<br/>";
    die();
  }

  header('Location: welcome.php');
?>