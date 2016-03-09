<html>
    <head>
        <meta charset="utf-8"/>
        <title>Retrieve keyspaces</title>
    </head>
    <body>

<?php

$cluster = Cassandra::cluster()
        ->withContactPoints('10.195.62.172','10.195.62.173','10.195.62.174','10.195.62.175','10.195.62.176')
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

$final_year = '2015';
$final_month = '12';
$final_day = '31';

$month = round($initial_month,1);
$day = round($initial_day,1);
$year = $initial_year;

$less_year = $final_year - $initial_year;

$keyspace = 'weatherstation';
$table = 'data_2015';
$meteos = array('Meteo1','Meteo2','Meteo2','Meteo3','Meteo4','Meteo5','Meteo6','Meteo7','Meteo8','Meteo9','Meteo10','Meteo11');
$sensor = 'humidity, temperature, dewpoint, winddirection, windspeed, pressure';

//QUERY
$query = "SELECT $sensor FROM $keyspace.$table WHERE weatherstation_id = ? AND date = ?";

$select = $session->prepare("$query");

$time_start = microtime(true);

foreach ($meteos as $meteo){

	$month = round($initial_month,1);
	$day = round($initial_day,1);
	$year = $initial_year;

	$less_year = $final_year - $initial_year;
	
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
	                                        //echo sprintf("%s Date: %s-%s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $year-$date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
        	                                //echo '<br>';
                	                }
			
				}

				####################### Only month < 10 ##########################
				elseif ( $month < 10 ) {
					$date = 0 . $month . '-' . $day;				
	
					$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

                        	        $result = $session->execute($select, $options);

                                	foreach ($result as $row) {
                                        	//echo sprintf("%s Date: %s-%s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $year, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
                                 	     	//echo '<br>';
	                                }

				}
	
				####################### Only day < 10 ############################
				elseif ( $day < 10 ) {
					$date = $month . '-' . 0 . $day;

					$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

        	                        $result = $session->execute($select, $options);

                	                foreach ($result as $row) {
                        	                //echo sprintf("%s Date: %s-%s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $year, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
                                        	//echo '<br>';
                                	}

				}

				####################### Month and day > 10 #######################
				else {
					$date = $month . '-' . $day;

					$options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

					$result = $session->execute($select, $options);
				
					foreach ($result as $row) {
						//echo sprintf("%s Date: %s-%s Temperature: %0.2f Humidity: %0.2f Dewpoint: %0.2f Pressure: %0.2f WindSpeed: %0.2f WindDirection: %0.2f \n", $meteo, $year, $date, $row['temperature'],$row['humidity'],$row['dewpoint'], $row['pressure'],$row['windspeed'],$row['winddirection']);
						//echo '<br>';
					}
			
				}
				$day = $day + 1;
			
				if ( $month == $final_month ) {
					if ( $day == ($final_day+1) ) {
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

    </body>
</html>

