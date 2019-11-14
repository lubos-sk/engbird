<?php

// zaciatok progrmau
$start_time = microtime(TRUE);
//$memory_start = convert(memory_get_usage(true));

//nastavenia php
ini_set("memory_limit","16M"); //384M, 512M, 1024M, 1500M
set_time_limit(360);
//ini_set('display_errors', false);
//phpinfo(); // Naprogramovane v PHP Version 7.3.0 
//SHOW VARIABLES LIKE "%version%"; //  DB: 'version', '10.1.37-MariaDB', Win32

// Nastavenie kodovania
mb_language('Neutral');
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale(LC_ALL, 'sk_SK.UTF-8', 'sk_SK', 'slovak', 'slovak_slovak', 'svk_svk.windows1250');

//pomocna funkcia na prepocet kb/mb pre memory usage
function convert($size)
{
    $unit=array('B','kB','MB','GB','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

// Pripojenie na DB
include 'dbconnect.php';
$link=mysqli_connect ($dbserver,$dbuser,$dbpassword,$dbname) or die ("spojenie neúspešné"); 
$db = mysqli_select_db($link,$dbname) or die ("nepodarilo sa otvoriť databázu.");


//aby mysql ukladali/zobrazovali zasielane data vo formate utf8, otestovat mozno s MariaDB uz nieje treba
$query=$result=$num="";
$query="SET NAMES 'utf8'";
mysqli_query($link, $query);
$result=mysqli_query($link, $query);

//Inicializacia premennych a poli
//$date = $time = $import_time = $values = $sensor_id = $temperature = $humidity = "";
$k = "0";


// Nacitanie  udajov z DB 
$query=$result=$num="";
$query = "SELECT date, time, temperature, humidity  FROM ".$dbname.".`temp_hum` WHERE sensor_id=0;";// LIMIT 1000
//echo $query;
$result= mysqli_query($link,$query);
$num_rows=mysqli_num_rows($result);
while ($row=mysqli_fetch_array($result)){
$date[$k] =  $row['date'];
$time[$k] = $row['time'];
$temperature[$k] = $row['temperature'];
$humidity[$k] = $row['humidity'];
$date_time[$k] = $date[$k]." ".$time[$k];
$k++;
}

// vytvorenie figure.js - json suboru
$data = [
	'x' =>  $date_time,
  'y' =>  $temperature,
  'y2'=>  $humidity
];

//echo "<pre>".json_encode($data)."</pre>";
$fp = fopen('figure.js', 'w');
fwrite($fp, json_encode($data));
fclose($fp);


//Doplnenie chybajucich elementov
//Simply, decode it using json_decode()
//And append array to resulting array.
//Again encode it using json_encode()

/*
// Zobrazenie udajov na obrazovke
echo "<pre>";
for ($k = 0; $k<$num_rows; $k++) {
    echo $date[$k]." ".$time[$k].",".$temperature[$k].",".$humidity[$k]."\n";
}
*/

//  <script src=\"https://cdn.plot.ly/plotly-latest.min.js\"></script>




$memory_usage = convert(memory_get_usage(true));
$memory_peak = convert(memory_get_peak_usage(true));
$end_time = microtime(TRUE);
$time_taken = $end_time - $start_time;
$time_taken = round($time_taken,5);
echo '<center>stránka vygenerovaná za '.$time_taken.' sekúnd RAM: '.$memory_usage.' - '.$memory_peak.'</center>';
?>
