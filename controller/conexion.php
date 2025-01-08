<?php
//datos del servidor
$server = "localhost";
$username = "root";
$password = "";
$bd = "dashjdarwin";
//creamos una conexión
$conn = mysqli_connect($server, $username, $password, $bd);
//Chequeamos la conexión
if (!$conn) {
    die("Conexión fallida:" . mysqli_connect_error());
}
