<?php

//$year = '02';
//
//$year1 = '06';
//
//$year2 = $year1 - $year;
//
//$year3 = $year;
//
//if ( $year3 = 2 ){
//	$year3 = 1;
//}
//
//$date = 0 . $year3 . '-' . 0 . $year1;
//
//echo $date;
//echo $year;
//echo $year1;
//echo $year2;
//echo $year3;

$cluster = Cassandra::cluster()
        ->withContactPoints('127.0.0.1')#,'10.195.62.171','10.195.62.172','10.195.62.173','10.195.62.174')
        ->build();

$session = $cluster->connect();
echo sprintf("<p>Connected to %s</p>", 'JAO CLUSTER');
$keyspaces = $session->schema()->keyspaces();

#echo "<table border=\"1\">";
#echo "<tr><th>Keyspace</th><th>Table</th></tr>";
#foreach ($keyspaces as $keyspace) {
#    foreach ($keyspace->tables() as $table) {
#        echo sprintf("<tr><td>%s</td><td>%s</td></tr>\n", $keyspace->name(), $table->name());
#    }
#}
#echo "</table>";

//$sensores = array("temperature","humidity","dewpoint","windspeed");
//
//	$idsensor = array_shift($sensores);
//	foreach( $sensores as $sensor) { 
//		$idsensor = $idsensor . ', ' . $sensor ;
//	}
//	unset($sensor);	
//	echo sprintf("Sensores: %s", $idsensor);
//	echo  '<br>';


$initial_year = '2015';
$initial_month = '01';
$initial_day  = '01';

//echo $initial_year;
//echo "\n"; 
//echo $initial_month;
//echo "\n";
//echo $initial_day;
//echo "\n";


$final_year = '2015';
$final_month = '01';
$final_day = '12';

//echo $final_year;
//echo "\n";
//echo $final_month;
//echo "\n";
//echo $final_day;
//echo "\n";

$month = round($initial_month,1);
$day = round($initial_day,1);
$year = $initial_year;

//echo $year;
//echo "\n";
//echo $month;
//echo "\n";
//echo $day;
//echo "\n";

$less_year = $final_year - $initial_year;

$meteo = 'Meteo3';
//$date = '01-12';

//echo $less_year;
//echo "\n";
//echo $meteo;
//echo "\n";

//QUERY

$select = $session->prepare('SELECT * FROM weatherstation.data_2015 WHERE weatherstation_id = ? AND date = ?');

$time_start = microtime(true);

while ( $less_year >= 0 ){
	
	if ( $month == 13 ) {
		$month = 1 ;
	}
	
	while ( $month <= 12 ){
		
		if ( $day == 32 ) {
			$day = 1 ;	
		}
	
		while ( $day <= 31 ){
			
			####################### If month and day < 10 ####################
			if ( ($month < 10 ) && ( $day < 10 )) {
				$date = 0 . $month . '-' . 0 . $day;
				
				$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

                                $result = $session->execute($select, $options);
				
                                foreach ($result as $row) {
					echo sprintf("%s Date: %s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
				}
			
			}

			####################### Only month < 10 ##########################
			elseif ( $month < 10 ) {
				$date = 0 . $month . '-' . $day;				

				$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

                                $result = $session->execute($select, $options);

                                foreach ($result as $row) {
                                        echo sprintf("%s Date: %s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
                                }

			}

			####################### Only day < 10 ############################
			elseif ( $day < 10 ) {
				$date = $month . '-' . 0 . $day;

				$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

                                $result = $session->execute($select, $options);

                                foreach ($result as $row) {
                                        echo sprintf("%s Date: %s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
                                }

			}

			####################### Month and day > 10 #######################
			else {
				$date = $month . '-' . $day;

				$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

				$result = $session->execute($select, $options);
				
				foreach ($result as $row) {
					echo sprintf("%s Date: %s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
				}
			
			}
			$day = $day + 1;
			
			if ( $month == $final_month ) {
				if ( $day == $final_day+1) {
					$day = 32;
				}
			}
		}
		$month = $month + 1;
		
		if ( $year == $final_year) {
			if ( $month == ($final_month+1) ) {
				$month = 13;
			}
		}
	}
	$year = $year + 1;	
	$less_year = $less_year - 1;
}


//$result = $session->execute(
//		new Cassandra\SimpleStatement('SELECT * FROM weatherstation.data_2015 WHERE weatherstation_id = ? AND date = ?'),
//		new Cassandra\ExecutionOptions(array(
//			'arguments' => array ('Meteo3', '01-12')
//		))
//	);


$time_end = microtime (true);

$execution_time = ($time_end - $time_start);

echo sprintf("Total execution time: %s[s] \n", round($execution_time,3));
echo '<br>';

//foreach ($result as $row) {
//    echo sprintf("%s Date: %s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
//    echo '<br>';
//}

?>
