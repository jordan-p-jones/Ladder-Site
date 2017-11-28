<?php
  include("authenticate.php");
  include("dbConnection.php");
  
  // Get the username of the challengee given in the form.
  $statement = $db->prepare("select username from active_player where name = :name"); // ASSUMES NO DUPLICATE NAMES IN DATABASE
  $statement->execute(array(':name'=>$_POST['challengee']));
  $challengee_uname = $statement->fetchColumn(0);
  
  // Create a session variable to hold the challengee.
  $_SESSION['challengee'] = $challengee_uname;
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
      Issue Challenge
    </title>
    
    <script>
      function validateDate(){
        var dateField = document.getElementById('date');
        
        if (dateField.value.search(/[0-9][0-9][0-9][0-9]-|\/[0-1][1-9]-|\/[0-3][0-9]/) == -1 || Number(dateField.value.slice(5, 7)) > 12 || Number(dateField.value.slice(8, 10)) > 31)
        {
          alert(dateField.value + ' is not a valid date.');
          dateField.focus();
          dateField.select();
          return false;
        }
        return true;
      }
    </script>
    
    <script>
      function validateTime(){
        var timeField = document.getElementById('time');
        
        if (timeField.value.search(/[0-2]?[0-9]:[0-5][0-9]/) == -1 || Number(timeField.value.slice(0, 2)) > 23)
        {
          alert(timeField.value + ' is not a valid time.');
          timeField.focus();
          timeField.select();
          return false;
        }
        return true;
      }
    </script>
    
    <script>
      function validateForm(){
        return validateDate() && validateTime();
      }
    </script>
  </head>
  
  <body>
    <div class="w3-container">
      <h1 align='center'>Issue a Challenge</h1>
      <p><hr></p>
      <div class="w3-card-1" style="width:25%">
        <div class="w3-container">
          <h3>Enter the date and time this match will take place if accepted:</h3>
        </div>
        <form class="w3-container" name='issue-challenge-form' id='issue-challenge-form' action='createChallenge.php' method='post' onSubmit="return validateForm();">
          <p>
          <label class="w3-text-blue-grey"><b>Date (yyyy/mm/dd)</b></label>
          <input class="w3-input w3-border w3-light-grey" type='text' name='date' id='date' onChange='validateDate();' required></p>
          <p>
          <label class="w3-text-blue-grey"><b>Time (hour:minute in 24-hour format)</b></label>
          <input class="w3-input w3-border w3-light-grey" type='text' name='time' id='time' onchange='validateTime();' required></p>
          <p>
          <button class="w3-btn w3-blue-grey">Submit</button></p>
        </form>
        <div class="w3-container">
          <button class="w3-btn w3-blue-grey" onclick='window.location.assign("welcome.php");'>Cancel</button>
        </div>
      </div>
    </div>
  </body>
  
</html>