<?php
  include("authenticate.php");
  include("dbConnection.php");
?>

<html>
  <head>
    <meta http-equiv="refresh" content="300;index.html"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="-1"/>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  
    <title>
	    Welcome Back
    </title>
    
    <script type='text/javascript'>
      function goToMatchEntry() {
        window.location.assign('match-entry.html');
      }
    </script>
    
    <script type='text/javascript'>
      function confirmLeave() {
        var leaving = confirm('Are you sure? You will no longer show up on the ladder or statistics to other players, but you may re-join later by logging in with your credentials for this account.');
        if (leaving)
        {
          window.location.assign('leave.php');
        }
      }
    </script>
  </head>
  
  <body>
    <div class="w3-container">
      <div style="text-align: right; position: absolute; right: 1cm; margin-top: 0.5cm; width: 175px; height: 175px; padding: 20px; border: 1px solid blue; box-sizing: border-box; background-color: white;">
        <?php        
          echo '<span id="username">Hello, ' . htmlspecialchars($_SESSION["name"]) . '.</span>';
        ?>
        <p>
        <button class="w3-btn w3-blue" onclick="window.location.assign('logout.php');">Log out</button></p>
        <button class="w3-btn w3-blue" onclick="confirmLeave();">Leave ladder</button>
      </div>
    
      <h1 align=center>Welcome Back to the Ladder</h1>
        <br><p><hr></p><br>
      
      <h2>Current Ladder Standings</h2><br>
      <table class="w3-table-all w3-centered" id='ladder-table' border='1' width='55%'>
        <tr><th align='center'>Rank</th><th align='center'>Player</th><th align='center'>Challenge Status</th>
          <th align='center'>Match Win Rate</th><th align='center'>Game Win Rate</th><th align='center'>Average Win Margin</th><th align='center'>Average Loss Margin</th></tr>
        
        <?php
          // Get the players who can be challenged by the user.
          $statement = $db->prepare("Select c.name as challengee
                                      from active_player as p, active_player as c where
                                      p.username = :user and c.rank between (p.rank-3) and (p.rank-1) and
                                      not exists (select * from challenge
                                                  where (challenger = c.username or
                                                        challengee = c.username or
                                                        challenger = p.username or
                                                        challengee = p.username) and
                                                        not accepted isNull);");

          $statement->execute(array(':user'=>$_SESSION['username']));
          $challengees = $statement->fetchAll();

          // Get the players and their ranks and make a table row for each one.
          $result = $db->query("select p.rank, p.name, p.username,
                                        coalesce( (select cast(count(*) as float(2)) from match_view as m
                                          where m.winner = p.username)
                                        /
                                        (select cast(count(*) as float(2)) from match_view as m
                                          where m.winner = p.username or m.loser = p.username), 0.0)
                                      as match_win_pct,
                                        coalesce( (select cast(count(*) as float(2)) from game as g
                                          where g.winner = p.username)
                                        /
                                        (select cast(count(*) as float(2)) from game as g
                                          where g.winner = p.username or g.loser = p.username), 0.0)
                                      as game_win_pct,
                                        coalesce( (select avg(winner_score - loser_score) from game as g
                                          where p.username=g.winner), 0.0) as avg_win_margin,
                                        coalesce( (select avg(winner_score - loser_score) from game as g
                                          where p.username=g.loser), 0.0)  as avg_lose_margin
                                  from active_player as p
                                  where exists (select * from match_view as m where p.username = m.winner or
                                    p.username=m.loser)
                                  union
                                  Select rank, name, username, 0.0, 0.0, 0.0, 0.0 from active_player
                                  where not exists (select * from match_view where username = winner or
                                    username = loser)
                                  Order by rank asc;");
          $players = $result->fetchAll();
          foreach($players as $player)
          {
            $available = false;
            echo "<tr><td align='center'>" . $player['rank'] . "</td><td align='center'>" . htmlspecialchars($player['name']) . "</td>";

            // If the current player is in the challengees array, show that person can be challenged.
            foreach($challengees as $challengee)
            {
              if ($player['name'] == $challengee['challengee'])
              {
                echo "<td align='center'>Available - <button class='w3-btn w3-round-large w3-border w3-amber' form='" . htmlspecialchars($player['name']) . "'>Challenge</button></td>
                <form id='" . htmlspecialchars($player['name']) . "' action='challenge.php' method='post'>
                  <input type='text' name='challengee' id='challengee' value='" . htmlspecialchars($player['name']) . "' hidden>
                </form>";
                $available = true;
              }
            }

            if (!$available)
            {
              // See if the current player has accepted a challenge.
              $statement = $db->prepare("Select * from challenge where challenger = :user and challengee = :player and accepted is not null");
              $statement->execute(array(':user'=>$_SESSION['username'], ':player'=>$player['username']));
              if ($statement->rowCount() == 1)
              {
                $challenge = $statement->fetchAll();
                echo "<td align='center'>Accepted your challenge taking place at " . $challenge[0]['scheduled'] . "</td>";
              }
              else
              {
                // See if the current player has challenged the user, where the user has not accepted or rejected yet.
                $statement = $db->prepare("Select * from challenge where challenger = :player and challengee = :user and accepted is null");
                $statement->execute(array(':player'=>$player['username'], ':user'=>$_SESSION['username']));
                
                if ($statement->rowCount() > 0)
                {
                  // Display each challenge given by this player.
                  $results = $statement->fetchAll();
                  echo "<td align='center'>";
                  foreach($results as $row)
                  {
                    echo "Challenged you to a match taking place at " . $row['scheduled'] . " - 
                      <button class='w3-btn w3-round-large w3-border w3-green' form='" . htmlspecialchars($row['challenger']) . $row['scheduled'] . "' formaction='acceptChallenge.php'>Accept</button> 
                      <button class='w3-btn w3-round-large w3-border w3-red' form='" . htmlspecialchars($row['challenger']) . $row['scheduled'] . "'>Reject</button></br>";
                  }
                  echo "</td>";
                  // Create the forms outside of the table data.
                  foreach($results as $row)
                  {
                    echo "<form id='" . htmlspecialchars($row['challenger']) . $row['scheduled'] . "' action='rejectChallenge.php' method='post'>
                      <input type='text' name='challenger' id='challenger' value='" . htmlspecialchars($row['challenger']) . "' hidden>
                      <input type='text' name='scheduled' id='scheduled' value = '" . $row['scheduled'] . "' hidden>
                    </form>";
                  }
                }
                else
                {
                  // See if the user has accepted a challenge from the current player.
                  $statement = $db->prepare("Select * from challenge where challenger = :player and challengee = :user and accepted is not null");
                  $statement->execute(array(':player'=>$player['username'], ':user'=>$_SESSION['username']));
                  if ($statement->rowCount() == 1)
                  {
                    $challenge = $statement->fetchAll();
                    echo "<td align='center'>You accepted a challenge taking place at " . $challenge[0]['scheduled'] . "</td>";
                  }
                  else if ($player['username'] == $_SESSION['username'])
                  {
                    echo "<td align='center'>(You)</td>";
                  }
                  else
                  {
                    echo "<td align='center'>Unavailable</td>";
                  }
                } // else
              } // else
            } // if
          
            // Display stats and finish the row.
            echo "<td align='center'>" . number_format($player['match_win_pct']*100) . "%</td>
                  <td align='center'>" . number_format($player['game_win_pct']*100) . "%</td>
                  <td align='center'>" . number_format($player['avg_win_margin'], 2) . "</td>
                  <td align='center'>" . number_format($player['avg_lose_margin'], 2) . "</td></tr>";
          }
        ?>
        
      </table>
      
      <p><hr><p>
      <h2>Recent Match Results</h2>
      <?php
        $statement = $db->prepare("Select * from challenge where accepted is not null and (challenger = :username or challengee = :username)");
        $statement->execute(array(':username'=>$_SESSION['username']));
        
        // Conditionally display the match results entry link.
        if ($statement->rowCount() == 1)
        {
          // Find who the other player is.
          $results = $statement->fetchAll();
          $opponent = $results[0]['challenger'];
          
          if ($opponent == $_SESSION['username'])
          {
            $opponent = $results[0]['challengee'];
          }
          
          // Get the real name of the opponent.
          $statement = $db->prepare("select name from active_player where username = :username");
          $statement->execute(array(':username'=>$opponent));
          $opp_name = $statement->fetchColumn(0);
          
          echo "<h3>Finished your match against " . htmlspecialchars($opp_name) . "? <a href='match-entry.php'>Enter the results here.</a></h3>";
        }
      ?>
      
      <?php
        // Get recent match results and display each one as a paragraph.
        $match_results = $db->query("Select p1.name as match_winner, p2.name as match_loser, won, lost from
                                      Match_view join active_player as p1 on match_view.winner = p1.username
                                        Join active_player as p2 on match_view.loser = p2.username
                                      Order by played desc
                                      Limit 7");
        $results_array = $match_results->fetchAll();
        foreach($results_array as $row)
        {
          $win_dif = ($row['won'] - $row['lost']);
          $end = " games!</p>";
          if ($win_dif == 1)
          {
            $end = " game!</p>";
          }
          echo "<p>" . htmlspecialchars($row['match_winner']) . " won a match against " . htmlspecialchars($row['match_loser']) . " by " . $win_dif . $end;
        }
      ?>
      
    </div>
  </body>

</html>