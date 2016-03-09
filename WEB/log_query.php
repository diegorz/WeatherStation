<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>ALMA Weather Station - log query</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="/all/weather.css" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
</head>
<body> 

<?php include("../Common/header.php"); ?>
<div id="content"> 
   <script type='text/JavaScript' src='scripts/scw.js'></script> 
	<br/><br/> 
	<form id="meteo_form" action='advanced_data.php' method='get'> 
           <table cellpadding='5' cellspacing='0' > 
	      <tr> 
		 <th class='primary'> Range of Dates</th> 
		 <th></th>
		 <th></th>
		 <th class='primary'> Sensor</th> 
		 <th class='primary'> Weather Station</th> 
		 <th class='primary'> Format</th> 
	      </tr> 
	      <tr> 
		<td><input width="50" type="text" name="start" value=' 2015-01-01' onclick='scwShow(this, event); return false;'/></td> 
		<td> 
		<td><input width="50" type="text" name="end" value=' 2015-12-31' onclick='scwShow(this, event); return false;'/></td>
		<td>
	
			<input type="checkbox" name='sensors[]' value="humidity" >Humidity
			<br>
			<input type="checkbox" name='sensors[]' value="temperature" >Temperature
			<br>
			<input type="checkbox" name='sensors[]' value="dewpoint" >Dewpoint
			<br>
			<input type="checkbox" name='sensors[]' value="winddirection" >Wind Direction
			<br>
			<input type="checkbox" name='sensors[]' value="windspeed" >Wind Speed
			<br>
			<input type="checkbox" name='sensors[]' value="pressure" >Pressure
			
			<!--	<select name="idsensor"> 
				<option value="1">Humidity</option> 
				<option value="8">Pressure</option> 
				<option value="4">Dewpoint</option> 
				<option value="2">Temperature</option> 
				<option value="5">Wind Direction</option> 
				<option value="6">Wind Speed</option> 
				<option value="99">All</option> 
			</select>	--> 
		 </td> 
	    	 <td> 
			<input type="checkbox" name='meteos[]' value="Meteo1" >MeteoTB1
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo2" >MeteoTB2
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo4" >MeteoItinerant
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo5" >Meteo201
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo6" >MeteoCentral
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo7" >Meteo309
			<br>
			<input type="checkbox" name='meteos[]' value="Meteo8" >Meteo410
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo9" >Meteo131
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo10" >Meteo129
                        <br>
                        <input type="checkbox" name='meteos[]' value="Meteo11" >Meteo201
			<br>
			<input type="checkbox" name='meteos[]' value="Meteo3" >MeteoOSF

			<!--	<select name="idweatherstation"> 
				<option value="1">MeteoTB1</option> 
				<option value="2">MeteoTB2</option> 
				<option value="4">MeteoItinerant</option>
				<option value="5">Meteo201</option>
				<option value="6">MeteoCentral</option>
				<option value="7">Meteo309</option>
				<option value="8">Meteo410</option>
				<option value="9">Meteo131</option>
				<option value="10">Meteo129</option>
				<option value="11">Meteo130</option>
				<option value="3">MeteoOSF</option>
			</select>	-->
		 </td> 
	    	 <td> 
			<select name="format"> 
				<option value="txt">TXT</option> 
				<!-- <option value="matlab">MATLAB</option>
				<option value="cvs">Excel</option>
				--> 
			</select> 
		 </td> 
	      </tr>	
	      <tr> 
		<td colspan='4' style='text-align: left'> <input type='submit' value='Submit'/> </td> 
	      </tr> 
 	   </table>
	</form> 
<br/>
<br/>
	<form id="oxygen_form" action='oxygen_data.php' method='get'> 
           <table cellpadding='5' cellspacing='0' > 
	      <tr> 
		 <th class='primary'> Date</th> 
		 <th class='primary'> Sensor</th> 
		 <th class='primary'> Weather Station</th> 
		 <th class='primary'> Format</th> 
	      </tr> 
	      <tr> 
	    	 <td ><input type="text" name="start" value=' 2013-01-21' onclick='scwShow(this, event); return false;'/></td> 
	    	 <td> 
			<select name="idsensor"> 
				<option value="1">Rcvr0</option> 
				<option value="2">Rcvr1</option> 
				<option value="3">TkBB</option>
				<option value="4">Tamb</option> 
				<option value="5">Rh</option> 
				<option value="6">Tir</option> 
				<option value="7">Rain</option> 
				<option value="99">All</option> 
			</select> 
		 </td> 
	    	 <td> 
			<select name="idweatherstation"> 
				<option value="1">Oxygen Sounder</option>
			</select> 
		 </td> 
	    	 <td> 
			<select name="format"> 
				<option value="txt">TXT</option> 
				<!-- <option value="matlab">MATLAB</option>
				<option value="cvs">Excel</option>
				--> 
			</select> 
		 </td> 
	      </tr>	
	      <tr> 
		<td colspan='4' style='text-align: left'> <input type='submit' value='Submit'/> </td> 
	      </tr> 
 	   </table>
	</form> 
	   <h5 class="warning">*Only daily logs can be downloaded through the web interface, if you need data for a longer period of time please contact Computing group at OSF</h5> 
	   <a href="/data/data/files/index.php?dir=2015%2F">historical weather data for 2015</a>
	   <br/>
	   <a href="/data/data/files/index.php?dir=2014%2F">historical weather data for 2014</a>
	   <br/>
       <a href="/data/data/files/index.php?dir=2013%2F">historical weather data for 2013</a>
	   <br/>
	   <a href="/data/data/files/index.php?dir=2012%2F">historical weather data for 2012</a> 
<br/> 
	   <a href="/data/data/files/index.php?dir=2011%2F">historical weather data for 2011</a> 
<br/> 
	   <a href="/data/data/files/index.php?dir=2010%2F">historical weather data for 2010</a> 
<br/> 
	   <a href="/data/data/files/index.php?dir=2009%2F">historical weather data for 2009</a>
</div>	
<?php include("../Common/footer.php"); ?>
 
</body> 
 
</html> 
