<?php 
	set_time_limit(600);
	include_once('weatherLib.php');
	$today = date("Y-m-j");	
	//var_dump($_GET);	
	if(isset($_GET['start'], $_GET['end']))
	{
		//$start = $_GET['start'];
		//$end = $_GET['end'];
		$start = '2015-04-01';		
		$end = '2015-04-01';

		//$end  = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
		//$start_date = new DateTime(trim($start));
		//$start_date->modify("+1 day");
		//$end = $start_date->format("Y-m-d");
		
		//$format = $_GET['format'];
		$format = 'txt';		

		//$idweatherstation = $_GET['meteos'];
		$idweatherstation = 'Meteo3';

		//$idsensor = $_GET['sensors']	
		$idsensor = 'humidity';

		//check value for parameters
		//echo $format . "<br>";
		//echo $idweatherstation . "<br>";
		//echo $idsensor . "<br>";
		//echo $start . "<br>";
		//echo $end  . "<br>";
		
		$header = true;
		
		$report = getWeatherData3($start, $end, $format, $idweatherstation, $idsensor, $header);
		
		if($format == 'matlab')
			$filename = "weather.m";
		elseif($format == 'cvs')
			$filename = "weather.cvs";
		else
			$filename = "weather.txt";

		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo $report;
	}

?>
