<?php
  include("../dbConnection.php");
  
  // Query the given username and password and see if a row is returned.
  // If something is returned, go to the welcome page.
  $loggedIn = false;
  $statement = $db->prepare("select name, rank, password from player where username = :username");
  $statement->execute(array(':username'=>$_POST["username"]));
  
  // Username is found.
  if ($statement->rowCount() == 1)
  {
    $result = $statement->fetchAll();
    
    // Verify password.
    if (password_verify($_POST['pswd'], $result[0]['password']))
    {
      $loggedIn = true;
      session_start();
      session_unset();
      session_destroy();
      
      session_start();
      $_SESSION["username"] = $_POST["username"];
      $_SESSION["name"] = $result[0]['name'];
      
      // If the rank is null, ask if the person wants to reactivate the account.
      if ($result[0]['rank'] == null)
      {
        echo "
          <script type='text/javascript'>
            var reactivate = confirm('This account has previously been deactivated. Would you like to re-join the ladder with this account?');
            if (reactivate)
            {
              window.location.assign('http://csis314-jjones.bitnamiapp.com/reactivate.php');
            }
            else
            {
              window.location.assign('http://csis314-jjones.bitnamiapp.com/index.html');
            }
          </script>";
      }
      // Login normally.
      else
      {
        header('Location: http://csis314-jjones.bitnamiapp.com/welcome.php');
      }
    }
  }
  
  // If login falied, return to the home page and give an error message.
  if (!$loggedIn)
  {
    echo "
      <script type='text/javascript'>
        alert('Username and password combination is incorrect.');
        window.location.assign('http://csis314-jjones.bitnamiapp.com/index.html');
      </script>";
  }

?>