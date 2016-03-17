<?php 
	//functions to test elapsed time in seconds
	function micro_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	function hPa2mmhg($val)
	{
		$tmp = (double)$val;
		return $tmp * 0.75;
	}

	function getWeatherData($start, $end, $format, $idweatherstation, $idsensor, $header=true) { 
		return getWeatherDataOracle($start, $end, $format, $idweatherstation, $idsensor, $header);
	}
	
	function getWeatherData2($start, $end, $format, $idweatherstation, $idsensor, $header=true) {
		return getWeatherDataOracle($start, $end, $format, $idweatherstation, $idsensor, $header, true);
	}

	function getWeatherData3($start, $end, $format, $idweatherstation, $idsensor, $header=true) {
                return getWeatherDataCassandra($start, $end, $format, $idweatherstation, $idsensor, $header);
        }
	
	function getWeatherDataOracle($start, $end, $format, $idweatherstation, $idsensor, $header=true, $withtime=false)
	{
		$access = date("Y/m/d H:i:s");
		syslog(LOG_WARNING, "tshen performance test, start: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
	
		$sensor = array(1=> 'Humidity',
			2=> 'Temperature',
			4=> 'Dewpoint',
			5=> 'Wind Direction',
			6=> 'Wind Speed',
			8=> 'Pressure',
			99=> 'All');
		$debug = 1;

		$carry_return ="\r\n";
		$weatherstationName = "Meteo" . $idweatherstation;
		$headerData = "# weather station data of : " . $weatherstationName . $carry_return;

		if($idsensor == 99)
		{
			if($idweatherstation == 1) 
				$idsensors = "(11, 12, 14, 15, 16, 18)";
			else if($idweatherstation == 2) 
				$idsensors = "(21, 22, 24, 25, 26, 28)";
			else if($idweatherstation == 3) 
				$idsensors = "(31, 32, 34, 35, 36, 38)";
			else if($idweatherstation == 4)
				$idsensors = "(41, 42, 44, 45, 46, 48)";
			else if($idweatherstation == 5)
				$idsensors = "(51, 52, 54, 55, 56, 58)";
			else if($idweatherstation == 6)
				$idsensors = "(61, 62, 64, 65, 66, 68)";
			else if($idweatherstation == 7)
				$idsensors = "(71, 72, 74, 75, 76, 78)";
			else if($idweatherstation == 8)
				$idsensors = "(79, 80, 82, 83, 84, 86)";
			else if($idweatherstation == 9)
				$idsensors = "(87, 88, 90, 91, 92, 94)";
			else if($idweatherstation == 10)
				$idsensors = "(95, 96, 98, 99, 100, 102)";
			else if($idweatherstation == 11)
				$idsensors = "(103, 104, 106, 107, 108, 110)";
			else
				return "Error, weather station id=". $idweatherstation . " is invalid.";

			$sql = "select * from ( ";
			$sql = $sql ."select to_char(weather_data.timestamp, 'YYYY-MM-DD\"T\"HH24:MI:SS.FF3') as TimeStamp,";
			$sql = $sql . "weather_data.idsensor,";
			$sql = $sql . "weather_data.value ";
			$sql = $sql . "from weather_data inner join Sensor On weather_data.idSensor = Sensor.IdSensor ";
			$sql = $sql . "where Sensor.IdWeatherStation=" . $idweatherstation;
			if (!$withtime) {
				$sql = $sql . " and weather_data.timestamp >= to_date('". trim($start) . " 00:00:00', 'YYYY-MM-DD HH24:MI:SS')";
				$sql = $sql . " and weather_data.timestamp <= to_date('". trim($end) . " 00:00:00', 'YYYY-MM-DD HH24:MI:SS') ";
			}
			else {
				$sql = $sql . " and weather_data.timestamp >= to_date('". str_replace("T", " ", trim($start)) . "', 'YYYY-MM-DD HH24:MI:SS')";
				$sql = $sql . " and weather_data.timestamp <= to_date('". str_replace("T", " ", trim($end)) . "', 'YYYY-MM-DD HH24:MI:SS') ";
			}
			$sql = $sql . "order by weather_data.timestamp, weather_data.IdSensor";
			$sql = $sql . " ) pivot ( MAX(value) for idsensor in " . $idsensors .") order by 1";
			error_log($sql);

			/**
			$dbstr = "(DESCRIPTION =
				(SDU=32767)
				(SEND_BUF_SIZE=500000)
				(RECV_BUF_SIZE=500000)
				(ADDRESS_LIST =
					(ADDRESS = (PROTOCOL = TCP)(HOST = oraosf.osf.alma.cl)(PORT = 1521))
					(ADDRESS = (PROTOCOL = TCP)(HOST = orastbosf1.osf.alma.cl)(PORT = 1521))
					(LOAD_BALANCE = yes)
					(FAILOVER = on)
				)
				(CONNECT_DATA =
					(SERVICE_NAME = WEATHER.OSF.CL)
					(failover_mode=(type=select)(method=basic))
				)
			)";
			*/
			$connection = oci_connect("weather","new2me", "WEATHER.OSF.CL");
			//$connection = oci_connect("weather","new2me", $dbstr);
			$osql = oci_parse($connection, $sql);
			oci_execute($osql);			

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, sql executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			if($debug) 
				$headerData .= "# SQL=" . $sql . $carry_return;

			$headerData .= "#" . $carry_return;
			$headerData .= "# Date [utc]; Humidity [%]; Temperature [celsius]; Dewpoint [celsius]; Wind Direction [degree]; Wind Speed [m/s]; Pressure [hPa]" . $carry_return;
			$headerData .= "#" . $carry_return;

			$rawData = "";

			while($row = oci_fetch_array($osql, OCI_BOTH+OCI_RETURN_NULLS))
			{
				$rawData .= sprintf("%s; %.3f; %.3f; %.3f; %.3f; %.3f; %.3f\r\n", $row[0], $row[1], $row[2], $row[3],$row[4], $row[5], $row[6]);
			}

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			oci_free_statement($osql);

			if($header)
				return $headerData . $rawData;
			else 
				return $rawData;

		}
		else
		{	
			//generate ISO8601 date format i.e: 2010-04-20T00:00:01.000, weather station doesn't have higher resolution than seconds.
			switch ($idweatherstation) {
				// Meteo410
				case 8:
				    switch ($idsensor) {
						// Humidity
						case 1:
							$idsensors = "(79)";
							break;
						// Temperature
						case 2:
							$idsensors = "(80)";
							break;
						// Dewpoint
						case 4:
							$idsensors = "(82)";
							break;
						// Wind Direction
						case 5:
							$idsensors = "(83)";
							break;
						// Wind Speed
						case 6:
							$idsensors = "(84)";
							break;
						// Pressure
						case 8:
							$idsensors = "(86)";
							break;
					}
					break;
				// Meteo131
				case 9:
					switch ($idsensor) {
						// Humidity
						case 1:
							$idsensors = "(87)";
							break;
						// Temperature
						case 2:
							$idsensors = "(88)";
							break;
						// Dewpoint
						case 4:
							$idsensors = "(90)";
							break;
						// Wind Direction
						case 5:
							$idsensors = "(91)";
							break;
						// Wind Speed
						case 6:
							$idsensors = "(92)";
							break;
						// Pressure
						case 8:
							$idsensors = "(94)";
							break;
					}
					break;
				// Meteo129
				case 10:
					switch ($idsensor) {
						// Humidity
						case 1:
							$idsensors = "(95)";
							break;
						// Temperature
						case 2:
							$idsensors = "(96)";
							break;
						// Dewpoint
						case 4:
							$idsensors = "(98)";
							break;
						// Wind Direction
						case 5:
							$idsensors = "(99)";
							break;
						// Wind Speed
						case 6:
							$idsensors = "(100)";
							break;
						// Pressure
						case 8:
							$idsensors = "(102)";
							break;
					}
					break;
				// Meteo130
				case 11:
					switch ($idsensor) {
						// Humidity
						case 1:
							$idsensors = "(103)";
							break;
						// Temperature
						case 2:
							$idsensors = "(104)";
							break;
						// Dewpoint
						case 4:
							$idsensors = "(106)";
							break;
						// Wind Direction
						case 5:
							$idsensors = "(107)";
							break;
						// Wind Speed
						case 6:
							$idsensors = "(108)";
							break;
						// Pressure
						case 8:
							$idsensors = "(110)";
							break;
					}
					break;
				default:
					$idsensors = sprintf("(%d)", $idweatherstation*10 + $idsensor);
			}

			$sql = "select * from ( ";
			$sql = $sql ."select to_char(weather_data.timestamp, 'YYYY-MM-DD\"T\"HH24:MI:SS.FF3') as TimeStamp,";
			$sql = $sql . "weather_data.idsensor,";
			$sql = $sql . "weather_data.value ";
			$sql = $sql . "from weather_data inner join Sensor On weather_data.idSensor = Sensor.IdSensor ";
			$sql = $sql . "where Sensor.IdWeatherStation=" . $idweatherstation;
			$sql = $sql . " and weather_data.timestamp >= to_date('". trim($start) . " 00:00:00', 'YYYY-MM-DD HH24:MI:SS')";
			$sql = $sql . " and weather_data.timestamp <= to_date('". trim($end) . " 00:00:00', 'YYYY-MM-DD HH24:MI:SS') order by weather_data.timestamp, weather_data.IdSensor";
			$sql = $sql . " ) pivot ( MAX(value) for idsensor in " . $idsensors .") order by 1";

			$connection = oci_connect("weather","new2me", "WEATHER.OSF.CL");
			if (!$conn) {
				$e = oci_error();
				print $e;
			}

			$osql = oci_parse($connection, $sql);
			oci_execute($osql);			

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, sql executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			if($debug) 
				$headerData .= "# SQL=" . $sql . $carry_return;
			
			if($idsensor == 1)
				$sensorUnit = "%";
			elseif($idsensor == 2)
				$sensorUnit = "celsius";
			elseif($idsensor == 4)
				$sensorUnit = "celsius";
			elseif($idsensor == 5)
				$sensorUnit = "degree";
			elseif($idsensor == 6)
				$sensorUnit = "m/s";
			elseif($idsensor == 8)
				$sensorUnit = "hPA";
			else
				$sensorUnit = "unkown";

			$headerData .= "#" . $carry_return;
			$headerData .= "# Date [utc]; " . $sensor[$idsensor] . " [" . $sensorUnit . "]" . $carry_return;
			$headerData .= "#" . $carry_return;

			$rawData = "";
			while($row = oci_fetch_array($osql, OCI_BOTH+OCI_RETURN_NULLS))
			{
				$rawData .= sprintf("%s; %.3f\r\n", $row[0], $row[1]);
			}

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			oci_free_statement($osql);

			if($header)
				return $headerData . $rawData;
			else 
				return $rawData;
		}
	}

	function getWeatherDataMysql($start, $end, $format, $idweatherstation, $idsensor, $header = true)
	{
		$access = date("Y/m/d H:i:s");
		syslog(LOG_WARNING, "tshen performance test, started ... retrieving data for " . $start . " to " . $end . " : $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
	
		$sensor = array(1=> 'Humidity',
			2=> 'Temperature',
			4=> 'Dewpoint',
			5=> 'Wind Direction',
			6=> 'Wind Speed',
			8=> 'Pressure',
			99=> 'All');
		$debug = 1;

		//windows carry return
		$carry_return ="\r\n";
		$weatherstationName = "Meteo" . $idweatherstation;
		$headerData = "# weather station data of : " . $weatherstationName . $carry_return;

		if($idsensor == 99)
		{
			if($idweatherstation == 1) 
				$idsensors = array(11, 12, 14, 15, 16, 18);
			else if($idweatherstation == 2) 
				$idsensors = array(21, 22, 24, 25, 26, 28);
			else if($idweatherstation == 3) 
				$idsensors = array(31, 32, 34, 35, 36, 38);
			else
				return "Error, weather station id=". $idweatherstation . " is invalid.";

			$dbhost = "localhost";
			$dbuser = "weather";
			$dbpass = "new2me";
			$dbname = "WEATHER";

			$sql = "select date_format(timestamp, '%Y-%m-%dT%H:%i:%s.000') as Date,";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[0] . ", weather.Value, 0)) as 'Humidity',";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[1] . ", weather.Value, 0)) as 'Temperature',";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[2] . ", weather.Value, 0)) as 'Dew Point',";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[3] . ", weather.Value, 0)) as 'Wind Direction',";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[4] . ", weather.Value, 0)) as 'Wind Speed',";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensors[5] . ", weather.Value, 0)) as 'Pressure'";
			$sql = $sql . " from WEATHER_DATA weather";
			$sql = $sql . " where";
			$sql = $sql . " timestamp >= '" . trim($start) . "' and timestamp < '" . trim($end) . "'";
			$sql = $sql . " group by timestamp";
			//$sql = $sql . " limit 0,300";

			$connection = mysql_connect($dbhost, $dbuser, $dbpass) or die ("Error connecting to the database");
			mysql_select_db($dbname);
			$result = mysql_query($sql);

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, sql executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			if($debug) 
				$headerData .= "# SQL=" . $sql . $carry_return;

			$headerData .= "#" . $carry_return;
			$headerData .= "# Date [utc]; Humidity [%]; Temperature [celsius]; Dewpoint [celsius]; Wind Direction [degree]; Wind Speed [m/s]; Pressure [hPa]" . $carry_return;
			$headerData .= "#" . $carry_return;

			$rawData = "";
			while($row = mysql_fetch_array($result))
			{
				$rawData .= sprintf("%s; %.3f; %.3f; %.3f; %.3f; %.3f; %.3f\r\n", $row[0], $row[1], $row[2], $row[3],$row[4], $row[5], $row[6]);
			}

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			mysql_close($connection);

			if($header) 
				return $headerData . $rawData;
			else
				return $rawData;
		}
		else
		{
			//generate ISO8601 date format i.e: 2010-04-20T00:00:01.000, weather station doesn't have higher resolution than seconds.
			$dbhost = "localhost";
			$dbuser = "weather";
			$dbpass = "new2me";
			$dbname = "WEATHER";

			$idsensor_abs = sprintf("(%d)", $idweatherstation*10 + $idsensor);

			$sql = "select date_format(timestamp, '%Y-%m-%dT%H:%i:%s.000') as Date,";
			$sql = $sql . "SUM(IF(weather.idSensor = " . $idsensor_abs . ", weather.Value, 0))";
			$sql = $sql . " from WEATHER_DATA weather";
			$sql = $sql . " where";
			$sql = $sql . " timestamp >= '" . trim($start) . "' and timestamp < '" . trim($end) . "'";
			$sql = $sql . " group by timestamp";
//			$sql = $sql . " limit 0,300";

			$connection = mysql_connect($dbhost, $dbuser, $dbpass) or die ("Error connecting to the database");
			mysql_select_db($dbname);
			$result = mysql_query($sql);

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, sql executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			if($debug) 
				$headerData .= "# SQL=" . $sql . $carry_return;

			if($idsensor == 1)
				$sensorUnit = "%";
			elseif($idsensor == "Temperature")
				$sensorUnit = "celsius";
			elseif($idsensor == "Dewpoint")
				$sensorUnit = "celsius";
			elseif($idsensor == "Wind Direction")
				$sensorUnit = "degree";
			elseif($idsensor == "Wind Speed")
				$sensorUnit = "m/s";
			elseif($idsensor == "Pressure")
				$sensorUnit = "hPA";
			else
				$sensorUnit = "unkown";

			$headerData .= "#" . $carry_return;
			$headerData .= "# Date [utc]; " . $sensor[$idsensor] . " [" . $sensorUnit . "]" . $carry_return;
			$headerData .= "#" . $carry_return;
			
			$rawData ="";
			while($row = mysql_fetch_array($result))
			{
				$rawData .= sprintf("%s; %.3f\r\n", $row[0], $row[1]);
			}

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "tshen performance test, data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			mysql_close($connection);

			if($header)
				return $headerData . $rawData;
			else 
				return $rawData;
		}
	}

	function getWeatherDataCassandra($start, $end, $format, $idweatherstation, $idsensor, $header = true){
		$rawData = "";		
	
		$cluster = Cassandra::cluster()		
			->withContactPoints('10.195.62.172','10.195.62.173','10.195.62.174','10.195.62.175','10.195.62.176')
		        ->build();

		$session = $cluster->connect() or die ("Error connecting to the database");
	
		$access = date("Y/m/d H:i:s");
		syslog(LOG_WARNING, "Connected to JAO CLUSTER: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

		$debug = 1;

		$carry_return ="\r\n";

		$initial_year = substr($start, 0, 4);
                $initial_month = substr($start, 5, 2);
                $initial_day  = substr($start, 8, 2);

                $final_year = substr($end, 0 , 4);
                $final_month = substr($end, 5 , 2);
                $final_day = substr($end, 8 , 2);
	
		$keyspace = 'weatherstation';
		
		if ( !empty($idsensor) && is_array($idsensor) ) {
                        $sensors = array_shift($idsensor);
                        foreach ( $idsensor as $sensor ) {
                                $sensors = $sensors . ', ' . $sensor;
                        }
                        unset($sensor);
                }
		else
			die ("Sensors NULL");


		if ( !empty($idweatherstation) && is_array($idweatherstation) ) 
               	     foreach ( $idweatherstation as $meteo ) {
			
			$year = $initial_year;	
		        $month = round($initial_month,1);
		        $day = round($initial_day,1);

		        $less_year = $final_year - $initial_year;

		        $table = "data_" . $year;

			$query = "SELECT date_full, $sensors FROM $keyspace.$table WHERE weatherstation_id = ? AND date = ?";

			$select = $session->prepare("$query");
	
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
							
							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "CQL executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

							foreach ($result as $row) {
								$rawData .= sprintf("%s; %s; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f\r\n", $meteo,$row['date_full'],$row['temperature'],$row['humidity'],$row['dewpoint'], $row['windspeed'],$row['winddirection'], $row['pressure']);
        	                               		}

							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "Data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

	                               		}

	                                	####################### Only month < 10 ##########################
        	                	        elseif ( $month < 10 ) {
                		                        $date = 0 . $month . '-' . $day;

                	        	                $options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));

        	                        	        $result = $session->execute($select, $options);
							
							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "CQL executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");		
		
	                                        	foreach ($result as $row) {
								$rawData .= sprintf("%s; %s; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f\r\n", $meteo,$row['date_full'],$row['temperature'],$row['humidity'],$row['dewpoint'],$row['windspeed'],$row['winddirection'],$row['pressure']);
                                        		}

							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "Data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

                                		}

	                	                ####################### Only day < 10 ############################
        		                        elseif ( $day < 10 ) {
        	        	                        $date = $month . '-' . 0 . $day;
	
        	                	                $options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));
	
        	                        	        $result = $session->execute($select, $options);

							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "CQL executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

	                                        	foreach ($result as $row) {
        	                	               		$rawData .= sprintf("%s; %s; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f\r\n", $meteo,$row['date_full'],$row['temperature'],$row['humidity'],$row['dewpoint'],$row['windspeed'],$row['winddirection'],$row['pressure']); 
							}
				
							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "Data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
				
						}

	                        	        ####################### Month and day > 10 #######################
        	        	                else {
                		                        $date = $month . '-' . $day;
		
        		                                $options = new Cassandra\ExecutionOptions(array('arguments' => array ($meteo, $date)));
	
        	        	                        $result = $session->execute($select, $options);
	
							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "CQL executed: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

                        		                foreach ($result as $row) {
                        	        	                $rawData .= sprintf("%s; %s; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f; %0.3f\r\n", $meteo,$row['date_full'],$row['temperature'],$row['humidity'],$row['dewpoint'],$row['windspeed'],$row['winddirection'],$row['pressure']);
        	                                	}
							
							$access = date("Y/m/d H:i:s");
				                        syslog(LOG_WARNING, "Data retrieved and formatted: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");	
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
					
			$time_end = microtime (true);
			}

			else
				die ("WeatherStation NULL");	
		
                        $weatherstationName = array_shift($idweatherstation);
			foreach ( $idweatherstation as $weatherstation ) {
                                $weatherstationName = $weatherstationName . ', ' . $weatherstation;
                        }
                        unset($weatherstation);

	                $headerData = "# weather station data of : " . $weatherstationName . $carry_return;
	
			if($debug) 
				$headerData .= "# CQL= " . "SELECT " . $sensors . " FROM weatherstation.data_2015  WHERE weatherstationid = " . $weatherstationName . " AND date >= " . $start . " AND date <= " . $end . $carry_return;
			
			$headerData .= "#" . $carry_return;
			$headerData .= "# WeatherStation; Date ; Humidity [%]; Temperature [celsius]; Dewpoint [celsius]; Wind Direction [degree]; Wind Speed [m/s]; Pressure [hPa]" . $carry_return;
			$headerData .= "#" . $carry_return;
			
			$execution_time = round(($time_end - $time_start),3);

			$access = date("Y/m/d H:i:s");
			syslog(LOG_WARNING, "Execution Time: $execution_time [s], $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");

			if($header)
				return $headerData . $rawData;
			else 
				return $rawData;
			
	}

	function wind_direction_correction($val)
	{
		//wind direction offset due physical installation
		$_val = (float)$val - 136.45779;
		while($_val > 360)
			$_val = $_val - 360;
		while($_val < 0)
			$_val = $_val + 360;

		return $_val; 
	}
?>
