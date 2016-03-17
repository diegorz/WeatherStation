<?php 
	//set_time_limit(600000);
	include_once('weatherLib.php');
	$today = date("Y-m-j");	
	//var_dump($_POST);	
	if(isset($_POST['start'], $_POST['end']))
	{
		$start = $_POST['start'];
		$end = $_POST['end'];

		//$end  = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
		//$start_date = new DateTime(trim($start));
		//$start_date->modify("+1 day");
		//$end = $start_date->format("Y-m-d");
		
		$format = $_POST['format'];

		$idweatherstation = $_POST['meteos'];

		$idsensor = $_POST['sensors'];	

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
