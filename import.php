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
//mysql.ini upravujem max_allowed_packet = 2M (pobodne bol 1M)

// upravenim tabulky rows na fixed sa zrychlil import 30 000 zaznamov z 340 sekund na 4
// Aria engine je vyrazne rychlejsia pri insertoch ako InnoDB
// optimalizacia kazdy 1000 INSERT INTO `table1` (`field1`, `field2`) VALUES ("data1", "data2"),("data1", "data2"),... zrychlila pri 300 000 zaznamoch z 4s na 0,45s

/*DROP TABLE IF EXISTS `engbird`.`temp_hum`;
CREATE TABLE  `engbird`.`temp_hum` (
  `sensor_id` tinyint(1) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `humidity` decimal(5,2) unsigned NOT NULL,
  `temp_hum_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`temp_hum_id`),
  UNIQUE KEY `Index_uniq` (`sensor_id`,`date`,`time`) USING BTREE
) ENGINE=Aria AUTO_INCREMENT=187880 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PAGE_CHECKSUM=1 ROW_FORMAT=FIXED;
*/

//aby mysql ukladali/zobrazovali zasielane data vo formate utf8, otestovat mozno s MariaDB uz nieje treba
$query=$result=$num="";
$query="SET NAMES 'utf8'";
mysqli_query($link, $query);
$result=mysqli_query($link, $query);

//Inicializacia premennych a poli
$date = $time = $import_time = $values = "";
$sensor_id = $temperature = $humidity = "0";

//  Nacitanie obsahu adresara
$cesta = './exports/1/'; 
// $cesta = './engbird/exports/2/'; // 


echo "cesta: ".$cesta."<br>";

$handle=opendir($cesta);
while (($file = readdir($handle))!==false) {
if (strlen($file)>8){$index_subory[]=$file;}
 }
closedir($handle);


echo "Importujem: ". count($index_subory)." zaznamov<br><br>";
if (count($index_subory)>0){
foreach ($index_subory as $subor) {
 echo $subor."<br>";


// Spracovanie txt suboru
$lines = file ($cesta.'\\'.$subor);
$casti = array ();
$temperature = $arrval = $hodnota_x = $hodnota_y = $values ="";
$query_header = "INSERT INTO ".$dbname.".`temp_hum` (`sensor_id`,`date`,`time`,`temperature`,`humidity`) VALUES ";

foreach ($lines as $line_num =>$line) {

    // hlavicka - urcuje poradie ulozenych dat
    //if ($line_num=="1"){};

$casti = explode(",",$line);
//print_r($casti)

//Array ( [0] => 2019-05-31 05:37:39 [1] => 15.89 [2] => 85 )

$date_time_casti = explode(" ", $casti[0]);
$date=$date_time_casti[0];
$time=$date_time_casti[1];
$temperature=$casti[1];
$humidity=number_format((float)$casti[2], 2, '.', ''); 


//echo"<pre>";print_r($date_time_casti);echo"</pre>";



$values.="('$sensor_id', '$date', '$time', '$temperature', '$humidity'),";

if ($line_num  % 1000 == 0) { //vkladam hlavicku pre query a robim query kazdych 1000-ci riadok hodnot => 10x rychlejsie inserty
//odstranenie poslednej ciarky
$values_rtrim = rtrim($values, ", ");
//$import_date = date('Y-m-d');
//$import_time = date('H:i:s');
// Ulozenie udajov do DB 
$query=$result=$num="";
//$query = "INSERT INTO ".$dbname.".`temp_hum` SET sensor_id = '$sensor_id',  date = '$date', time = '$time', temperature ='$temperature', humidity = '$humidity';";
//$query = "INSERT INTO ".$dbname.".`temp_hum` (`sensor_id`,`date`,`time`,`temperature`,`humidity`) VALUES $values_rtrim;";
$query = $query_header.$values_rtrim.";";
// import_time = '$import_time';
$result=mysqli_query($link,$query);
//echo $query."<br>";
$values="";
//unset($values);
}

 }
}

/*
//odstranenie poslednej ciarky
$values_rtrim = rtrim($values, ", ");
//$import_date = date('Y-m-d');
//$import_time = date('H:i:s');
// Ulozenie udajov do DB 
$query=$result=$num="";
//$query = "INSERT INTO ".$dbname.".`temp_hum` SET sensor_id = '$sensor_id',  date = '$date', time = '$time', temperature ='$temperature', humidity = '$humidity';";
//$query = "INSERT INTO ".$dbname.".`temp_hum` (`sensor_id`,`date`,`time`,`temperature`,`humidity`) VALUES $values_rtrim;";
$query = $query_header.$values_rtrim.";";
// import_time = '$import_time';
$result=mysqli_query($link,$query);
echo $query."<br>";
//}
//}
*/



/*
//print_r ($index_subory);
// Vymazanie suborov ak boli spravne ulozene do DB
foreach ($index_subory as $subor) {
$compressor_id = substr($subor,0,10);
$query=$result=$num="";
$query = "SELECT COUNT(*) as pocet FROM ".$dbname.".`axial` WHERE compressor_id = '$compressor_id' AND value > '0';";
//echo $query;
$result=mysql_query($query, $link);
$row     = mysql_fetch_array($result, MYSQL_ASSOC);
$pocet = $row['pocet'];
if ($pocet==1){unlink($cesta.'\\'.$subor);}
}
*/


}
unset($index_subory);
unset($values);


$memory_usage = convert(memory_get_usage(true));
$memory_peak = convert(memory_get_peak_usage(true));

$end_time = microtime(TRUE);
$time_taken = $end_time - $start_time;
$time_taken = round($time_taken,5);
echo '<center>stránka vygenerovaná za '.$time_taken.' sekúnd RAM: '.$memory_usage.' - '.$memory_peak.'</center>';
?>
