<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  $user_wins = 0;
  $opp_wins = 0;
  
  // Find who the other player is.
  $statement = $db->prepare("select * from challenge where (challenger = :user or challengee = :user) and accepted is not null");
  $statement->execute(array(':user'=>$_SESSION['username']));
  $results = $statement->fetchAll();
  $opponent = $results[0]['challenger'];
  $user_rank_higher = true;
  
  if ($opponent == $_SESSION['username'])
  {
    $opponent = $results[0]['challengee'];
    // Know opponent is higher rank.
    $user_rank_higher = false;
  }
  
  try
  {
    $db->beginTransaction();
    
    // Insert 3 required games.
    insertGame($_POST['me-game-1'], $_POST['opp-game-1'], 1, $user_wins, $opp_wins, $opponent, $db, $results);
    insertGame($_POST['me-game-2'], $_POST['opp-game-2'], 2, $user_wins, $opp_wins, $opponent, $db, $results);
    insertGame($_POST['me-game-3'], $_POST['opp-game-3'], 3, $user_wins, $opp_wins, $opponent, $db, $results);

    // Insert other games if they were played.
    if ($_POST['me-game-4'] != '' && $_POST['opp-game-4'] != '')
    {
      insertGame($_POST['me-game-4'], $_POST['opp-game-4'], 4, $user_wins, $opp_wins, $opponent, $db, $results);

      if ($_POST['me-game-5'] != '' && $_POST['opp-game-5'] != '')
      {
        insertGame($_POST['me-game-5'], $_POST['opp-game-5'], 5, $user_wins, $opp_wins, $opponent, $db, $results);
      }
    }
    
    // Update player ranks only if the winner was originally of a lower rank than the loser.
    if ((!$user_rank_higher && $user_wins > $opp_wins) || ($user_rank_higher && $opp_wins > $user_wins))
    {
      // Decide usernames of winner and loser.
      $winner_username = $_SESSION['username'];
      $loser_username = $opponent;
      
      if ($opp_wins > $user_wins)
      {
        $winner_username = $opponent;
        $loser_username = $_SESSION['username'];
      }
      
      // Decide the rank range in which players will be moved down a rank.
      $statement = $db->prepare("select rank from active_player where username = :player");
      $statement->execute(array(':player'=>$winner_username));
      $low_range = $statement->fetchColumn(0) - 1;
      $statement->execute(array(':player'=>$loser_username));
      $high_range = $statement->fetchColumn(0);
      
      // Temporarily set the rank of the winner to 0 so that others can be moved down.
      $set_rank = $db->prepare("update active_player set rank = :rank where username = :player");
      $set_rank->execute(array(':player'=>$winner_username, ':rank'=>0));
      
      // Get all players who need to be moved down.
      $statement = $db->prepare("select username, rank from active_player where rank between :high_range and :low_range order by rank desc");
      $statement->execute(array(':high_range'=>$high_range, ':low_range'=>$low_range));
      $players = $statement->fetchAll();
      
      // Move each player down, one at a time.
      foreach($players as $player)
      {
        $set_rank->execute(array(':player'=>$player['username'], ':rank'=>$player['rank']+1));
      }
      
      // Give the winner the original rank of the loser.
      $set_rank->execute(array(':player'=>$winner_username, ':rank'=>$high_range));
    } // if

    // Remove the challenge entry.
    $statement = $db->prepare("delete from challenge where challenger = :challenger and challengee = :challengee and accepted is not null");
    $statement->execute(array(':challenger'=>$results[0]['challenger'], ':challengee'=>$results[0]['challengee']));
    
    $db->commit();
  } // try
  catch (Exception $e) 
  {
    $db->rollBack();
    echo "Something went wrong. Please try again in a few minutes.";
  }

  // Go back to the Welcome page.
  header('Location: welcome.php');
  
  
  // Functions ----------------------------------------------------------------

  function insertGame($user_score, $opp_score, $game_number, &$user_wins, &$opp_wins, $opponent, $db, $results)
  {
    // Decide the winner and loser, along with their scores.
    $winner = $_SESSION['username'];
    $loser = $opponent;
    $winner_score = $user_score;
    $loser_score = $opp_score;
    
    if ($winner_score < $loser_score)
    {
      $winner = $opponent;
      $loser = $_SESSION['username'];
      $winner_score = $opp_score;
      $loser_score = $user_score;
      $opp_wins++;
    }
    else
    {
      $user_wins++;
    }
    
    // Insert the row.
    $statement = $db->prepare("insert into game values(:winner, :loser, :played, :game_number, :winner_score, :loser_score)");
    $statement->execute(array(':winner'=>$winner, ':loser'=>$loser, ':played'=>$results[0]['scheduled'], ':game_number'=>$game_number,
                              ':winner_score'=>$winner_score, ':loser_score'=>$loser_score));
  }
  
?>