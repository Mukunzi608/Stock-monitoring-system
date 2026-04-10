<?php
$SERVERNAME="localhost";
$USERNAME="root";
$PASSWORD="";
$DBNAME="stock_system";
$conn=new mysqli($SERVERNAME,$USERNAME,$PASSWORD,$DBNAME);
if($conn->connect_error){
    echo"connection error" .$conn->connect_error;
}
else{
  //  echo"connection successfully";
}

?>