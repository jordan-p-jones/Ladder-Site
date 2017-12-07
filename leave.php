<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  try
  {
    $db->beginTransaction();
    
    // Remember rank of player being deleted.
    $statement = $db->prepare("select rank from player where username = :username");
    $statement->execute(array(':username'=>$_SESSION['username']));
    $rank = $statement->fetchAll();
    
    // Set the rank of the user to null in the player table.
    $set_rank = $db->prepare("update player set rank = :rank where username = :player");
    $set_rank->execute(array(':player'=>$_SESSION['username'], ':rank'=>null));
    
    // Get all players who need to be moved up.
    $statement = $db->prepare("select username, rank from active_player where rank > :rank order by rank asc");
    $statement->execute(array(':rank'=>$rank[0]['rank']));
    $players = $statement->fetchAll();
    
    // Move each player up, one at a time.
    foreach($players as $player)
    {
      $set_rank->execute(array(':player'=>$player['username'], ':rank'=>$player['rank']-1));
    }

    // Remove all challenges that contain the session's username.
    $statement = $db->prepare("delete from challenge where challenger = :username or challengee = :username");
    $statement->execute(array(':username'=>$_SESSION['username']));
    $db->commit();
  }
  catch (Exception $e) 
  {
    $db->rollBack();
    echo "Something went wrong. Please try again in a few minutes.";
    die();
  }
  
  session_unset();
  session_destroy();
  header('Location: index.html');
?>
