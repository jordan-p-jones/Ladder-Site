<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  $statement = $db->prepare("update player set rank = (select coalesce(max(rank)+1, 1) from player) where username = :username");
  $statement->execute(array(':username'=>$_SESSION['username']));
  header('Location: welcome.php');
?>
