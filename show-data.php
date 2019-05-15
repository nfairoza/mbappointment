<?php

  $credentials_check = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/');
  if ($credentials_check == ''){
    exit('<span style="color:red">Unable to retrieve AWS credentials. Please assign an IAM Role to this instance.</span>');
  }

  include('get-parameters.php');

  if ($ep == '') {
   echo 'Please configure Settings to connect to database';
  }
  else {
    # Display inventory

    // Set incoming variables
    isset($_REQUEST['mode']) ? $mode=$_REQUEST['mode'] : $mode="";
    isset($_REQUEST['id']) ? $id=urldecode($_REQUEST['id']) : $id="";
    isset($_REQUEST['store']) ? $store=urldecode($_REQUEST['store']) : $store="";
    isset($_REQUEST['item']) ? $item=$_REQUEST['item'] : $item="";
    isset($_REQUEST['quantity']) ? $quantity=$_REQUEST['quantity'] : $quantity="";


    // Connect to the RDS database
    $connect = mysqli_connect($ep, $un, $pw) or die(mysqli_error($connect));

    mysqli_select_db($connect, $db) or die(mysqli_error($connect));

  if ( $mode=="add")
   {
   Print '<h2>Add Appointment</h2>
   <p>
   <form action=';
   echo $_SERVER['PHP_SELF'];
   Print '
   method=post>
   <table>
   <tr><td>CustomerName:</td><td><input type="text" name="store" /></td></tr>
   <tr><td>StylerName:</td><td><input type="text" name="item" /></td></tr>
   <tr><td>scheduledTime:</td><td><input type="text" name="quantity" /></td></tr>
   <tr><td colspan="2" align="center"><input type="submit" class="blue-button"/></td></tr>
   <input type=hidden name=mode value=added>
   </table>
   </form> <p>';
   }

   if ( $mode=="added")
   {
   mysqli_query ($connect, "INSERT INTO schedule (CustomerName, StylerName, scheduledTime) VALUES ('$store', '$item', $quantity)");
   }

  if ( $mode=="edit")
   {
   Print '<h2>Edit Appointment</h2>
   <p>
   <form action=';
   echo $_SERVER['PHP_SELF'];
   Print '
   method=post>
   <table>
   <tr><td>Store:</td><td><input type="text" value="';
   Print $store;
   print '" name="store" /></td></tr>
   <tr><td>Item:</td><td><input type="text" value="';
   Print $item;
   print '" name="item" /></td></tr>
   <tr><td>Quantity:</td><td><input type="text" value="';
   Print $quantity;
   print '" name="quantity" /></td></tr>
   <tr><td colspan="3" align="center"><input type="submit" class="blue-button" /></td></tr>
   <input type=hidden name=mode value=edited>
   <input type=hidden name=id value=';
   Print $id;
   print '>
   </table>
   </form> <p>';
   }

   if ( $mode=="edited")
   {
    error_log("UPDATE schedule SET CustomerName = '$store', StylerName = '$item', scheduledTime = $quantity WHERE id = $id");
   mysqli_query ($connect, "UPDATE schedule SET CustomerName = '$store', StylerName = '$item', scheduledTime = $quantity WHERE id = $id");
   Print "Data Updated!<p>";
   }

  if ( $mode=="remove")
   {
   mysqli_query ($connect, "DELETE FROM schedule where id=$id");
   Print "Entry has been removed <p>";
   }

   $data = mysqli_query($connect, "SELECT * FROM schedule ORDER BY id ASC") or die(mysqli_error($connect));
   Print "<table id='inventory' border cellpadding=3>";
   Print "<tr><th width=10/><th width=10/> " .
     "<th>CustomerName</th> " .
     "<th>StylerName</th> " .
     "<th>scheduledTime</th></tr>";
   while($info = mysqli_fetch_array( $data ))
   {
   Print "<tr><td><a href=" .$_SERVER['PHP_SELF']. "?id=" . $info['id'] ."&mode=remove><i class='fas fa-trash-alt' style='color:#d82323;'></i></a></td>";
   Print "<td><a href=" .$_SERVER['PHP_SELF']. "?id=" . $info['id'] ."&store=" . urlencode($info['CustomerName']) . "&item=" . urlencode($info['StylerName']) . "&quantity=" . $info['scheduledTime'] ."&email=" . "&mode=edit><i class='fas fa-edit'></i></a></td>";
   Print "<td>".$info['CustomerName'] . "</td> ";
   Print "<td>".$info['StylerName'] . "</td> ";
   Print "<td>".$info['scheduledTime'] . "</td> ";
   Print "<tr>";
   }
   Print "</table>";
   Print "<br/><a href=" .$_SERVER['PHP_SELF']. "?mode=add class='blue-button'><i class='fas fa-plus'></i> Add Appointment</a>";
  }
?>
