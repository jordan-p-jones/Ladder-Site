<?php
  include("dbConnection.php");
  
  // Sanitize phone number.
  $form_telephone = str_replace('-', '', $_POST["tele"]);
  $form_telephone = str_replace('(', '', $form_telephone);
  $form_telephone = str_replace(')', '', $form_telephone);
  $form_telephone = str_replace(' ', '', $form_telephone);
  
  try {
    // Prepare an insert query
    $statement = $db->prepare("insert into player (name, email, rank, 
                               username, phone, password) 
                               select :name, :email, max(rank)+1, :username, 
                               :phone, :password from player");

    // Execute the query
    $statement->execute(array(':name'=>$_POST["name"], ':email'=>$_POST["email"], 
                              ':username'=>$_POST["uname"], ':phone'=>$form_telephone, 
                              ':password'=>password_hash($_POST["pwd1"], PASSWORD_DEFAULT)));

    // Check the results - should be one row
    if ($statement->rowCount() != 1) {
      // Can check the status to see if we violated the unique
      // constraint on username and alert the user.
      echo "
        <script type='text/javascript'>
          alert('Requested username is already taken.');
          window.location.assign('index.html');
        </script>
        ";
    }
    else
    {
      session_start();
      session_unset();
      session_destroy();
      
      session_start();
      $_SESSION["username"] = $_POST["uname"];
      $_SESSION["name"] = $_POST["name"];
      header('Location: welcome.php');
    }
  }
  catch (PDOException $e) {
     echo "A connection error has occurred. Please try again later.<br/>";
     die();
  }
  
?>