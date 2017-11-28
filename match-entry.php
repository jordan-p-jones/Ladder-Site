<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  // Find who the other player is.
  $statement = $db->prepare("select * from challenge where (challenger = :user or challengee = :user) and accepted is not null");
  $statement->execute(array(':user'=>$_SESSION['username']));
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
?>

<html>

  <head>
    <meta http-equiv="refresh" content="300;index.html"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="-1"/>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    
    <style>
      textarea 
      {
        width: 100%;
      }
    </style>
  
    <title>
      Match Results Entry
    </title>
    <script>
      function validateScore(scoreField){
        if (isNaN(scoreField.value) || scoreField.value < 0)
        {
          alert(scoreField.value + " is not a valid score.");
          scoreField.focus();
          scoreField.select();
          return false;
        }
        else
        {
          return true;
        }
      }
    </script>
	
    <script>
      function validateGame(scoreField1, scoreField2){
        if (validateScore(scoreField1) && validateScore(scoreField2))
        {
            if ((scoreField1.value != '' && scoreField2.value != '') && ((scoreField1.value < 15 && scoreField2.value < 15) || (Math.abs(scoreField2.value - scoreField1.value) < 2)
              || ((scoreField1.value > 15 || scoreField2.value > 15) && Math.abs(scoreField2.value - scoreField1.value) != 2)))
            {
              var game;
            
              switch (scoreField1.id){
                case 'me-game-1':
                  game = 'game 1';
                  break;
                case 'me-game-2':
                  game = 'game 2';
                  break;
                case 'me-game-3':
                  game = 'game 3';
                  break;
                case 'me-game-4':
                  game = 'game 4';
                  break;
                case 'me-game-5':
                  game = 'game 5';
                  break;
              }
        
              alert("The scores given for " + game + " are invalid. The winner must reach 15 points and win by at least 2.");
              scoreField2.focus();
              scoreField2.select();
              return false;
            }
            else
            {
              return true;
            }
        }
        else
        {
          return false;
        }
      }
    </script>
	
    <script>
      function validateForm(){
        if (!(validateGame(document.getElementById('me-game-1'), document.getElementById('opp-game-1'))
            && validateGame(document.getElementById('me-game-2'), document.getElementById('opp-game-2'))
            && validateGame(document.getElementById('me-game-3'), document.getElementById('opp-game-3'))
            && validateGame(document.getElementById('me-game-4'), document.getElementById('opp-game-4'))
            && validateGame(document.getElementById('me-game-5'), document.getElementById('opp-game-5'))))
        {
          return false;
        }
        else if ((document.getElementById('me-game-4').value != '' && document.getElementById('opp-game-4').value == '')
              || (document.getElementById('me-game-4').value == '' && document.getElementById('opp-game-4').value != ''))
        {
          alert("Inconsistent data for game 4.");
          return false;
        }
        else if ((document.getElementById('me-game-5').value != '' && document.getElementById('opp-game-5').value == '')
              || (document.getElementById('me-game-5').value == '' && document.getElementById('opp-game-5').value != ''))
        {
          alert("Inconsistent data for game 5.");
          return false;
        }
        else if (document.getElementById('me-game-4').value == '' && document.getElementById('opp-game-4').value == ''
              && document.getElementById('me-game-5').value != '' && document.getElementById('opp-game-5').value != '')
        {
          alert("You reported scores for game 5, but not for game 4. Please correct this inconsistency and submit again.");
          return false;
        }
        else
        {
          // See if either player has won more than 3 matches, which is not allowed.
          var p1Wins = 0;
          var p2Wins = 0;
          
          for (var i = 1; i < 6; i++)
          {
            if (document.getElementById('me-game-' + String(i)).value > document.getElementById('opp-game-' + String(i)).value)
            {
              p1Wins++;
            }
            else if (document.getElementById('me-game-' + String(i)).value < document.getElementById('opp-game-' + String(i)).value)
            {
              p2Wins++;
            }
          }        
          
          if (p1Wins > 3 || p2Wins > 3)
          {
            alert('It is not possible for a player to win more than three games in a match. Please correct this problem submit the scores again.');
            return false;
          }
          
          return true;
        }
      }
    </script>
  </head>

  <body>
    <div class="w3-container">
      <h1 align='center'>Match Result Entry</h1>
      <p><hr></p>
      
      <h3>Enter the scores of each game played into the table:</h3>
      <form name='match-results-form' id='match-results-form' action='insertGames.php' method='post' onSubmit="return validateForm();">
        <table class="w3-table-all" id='match-results-table' border='1' width='22.2%'>
          <tr><th align='center'></th><th align='center'>My Score</th><th align='center'><?php echo htmlspecialchars($opp_name) . "'s Score" ?></th></tr>
          <tr><th align='center'>Game 1</th>
            <td align='center'><input class="w3-input w3-border" type='text' id='me-game-1' name='me-game-1' onChange="validateGame(this, document.getElementById('opp-game-1'));" required></td>
            <td align='center'><input class="w3-input w3-border" type='text' id='opp-game-1' name='opp-game-1' onChange="validateGame(document.getElementById('me-game-1'), this);" required></td>
          </tr>
          <tr><th align='center'>Game 2</th>
            <td align='center'><input class="w3-input w3-border" type='text' id='me-game-2' name='me-game-2' onChange="validateScore(this); validateGame(this, document.getElementById('opp-game-2'));" required></td>
            <td align='center'><input class="w3-input w3-border" type='text' id='opp-game-2' name='opp-game-2' onChange="validateScore(this); validateGame(document.getElementById('me-game-2'), this);" required></td>
          </tr>
          <tr><th align='center'>Game 3</th>
            <td align='center'><input class="w3-input w3-border" type='text' id='me-game-3' name='me-game-3' onChange="validateScore(this); validateGame(this, document.getElementById('opp-game-3'));" required></td>
            <td align='center'><input class="w3-input w3-border" type='text' id='opp-game-3' name='opp-game-3' onChange="validateScore(this); validateGame(document.getElementById('me-game-3'), this);" required></td>
          </tr>
          <tr><th align='center'>Game 4</th>
            <td align='center'><input class="w3-input w3-border" type='text' id='me-game-4' name='me-game-4' onChange="validateScore(this) validateGame(this, document.getElementById('opp-game-4'));"></td>
            <td align='center'><input class="w3-input w3-border" type='text' id='opp-game-4' name='opp-game-4' onChange="validateScore(this); validateGame(document.getElementById('me-game-4'), this);"></td>
          </tr>
          <tr><th align='center'>Game 5</th>
            <td align='center'><input class="w3-input w3-border" type='text' id='me-game-5' name='me-game-5' onChange="validateScore(this); validateGame(this, document.getElementById('opp-game-5'));"></td>
            <td align='center'><input class="w3-input w3-border" type='text' id='opp-game-5' name='opp-game-5' onChange="validateScore(this); validateGame(document.getElementById('me-game-5'), this);"></td>
          </tr>
        </table><br>
        <button class="w3-btn w3-blue-grey">Submit</button></p>
      </form>
      <button class="w3-btn w3-blue-grey" onclick='window.location.assign("welcome.php");'>Cancel</button>
    </div>
  </body>

</html>