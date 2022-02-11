<?php
$conf = include "config.php";
$connection = new mysqli($conf["servername"], $conf["username"], $conf["password"], $conf["dbname"]);

if ($connection->connect_error) {
  die("Connection failed: " . $connection->connect_error);
}

$sql = "CREATE TABLE IF NOT EXIST test (
id INT(6) UNSIGNED PRIMARY KEY,
title VARCHAR(30) NOT NULL";

if ($connection->query($sql) === TRUE) {
  echo "Table test created successfully";
} else {
  echo "Error creating table: " . $connection->error;
}
return $connection;
?>