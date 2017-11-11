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
    <h1 align='center'>Issue a Challenge</h1>
    <p><hr></p>
    
    <h3>Enter the date and time this match will take place if accepted:</h3>
    <form name='issue-challenge-form' id='issue-challenge-form' action='createChallenge.php' method='post' onSubmit="return validateForm();">
      Date (yyyy/mm/dd):<br>
      <input type='text' name='date' id='date' onChange='validateDate();' required><br><br>
      Time (hour:minute in 24-hour format):<br>
      <input type='text' name='time' id='time' onchange='validateTime();' required><br><br>
      <input type='submit' value='Submit'> 
    </form>
    
    <input type='button' value='Cancel' onclick='window.location.assign("welcome.php");'>
    
  </body>
  
</html>