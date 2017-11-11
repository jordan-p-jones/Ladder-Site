<?php
  session_start();
  
  if (!isset($_SESSION['username']))
  {    
    echo "
        <script type='text/javascript'>
          alert('You must log in.');
          window.location.assign('index.html');
        </script>
        ";
    
    exit();
  }
?>