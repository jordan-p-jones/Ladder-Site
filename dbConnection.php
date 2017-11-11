<?php
  $dbDatabase = "ladder";
  $dbUsername = "bitnami";
  $dbPassword = "bitnami";

  // Attempt to connect to the database using a persistent connection.
  try
  {
    $db = new PDO("pgsql:dbname='$dbDatabase' password='$dbPassword' user='$dbUsername'",
                  $dbUsername, $dbPassword, array( PDO::ATTR_PERSISTENT => true));
  }
  catch (PDOException $e)
  {
    print "A connection error has occurred. Please try again later.<br/>";
    die();
  }
?>