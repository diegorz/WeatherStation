#!/bin/bash
#
#Example: ./WeatherDataLoad.sh [-f Table initial_date final_date]

inicio_ns=`date +%s%N`
inicio_s=`date +%s`
meteo=1

for i in $(ls -C1 | grep Meteo)
do
	cat $i | awk -F, '{$3 $1 $4 $5 $6 $7 $8 $9}' 
	#$CQL -e "INSERT INTO weatherstation.data_2015 (weatherstation_id , date , date_full , dewpoint , humidity , pressure , temperature , winddirection , windspeed ) VALUES ( '$1', '$2', '$3' , $4 , $5 , $6 , $7 , $8 , $9 );" 10.195.62.174' 

	#$CQL -e "INSERT INTO weatherstation.data_2015 (weatherstation_id , date , date_full , dewpoint , humidity , pressure , temperature , winddirection , windspeed ) VALUES ( '$meteo', '$fecha', '$fecha_full' , $humidity , $temperature , $dewpoint , $winddirection , $windspeed , $pressure );" 10.195.62.174

done

fin_ns=`date +%s%N`	
fin_s=`date +%s`

let total_ns=$fin_ns-$inicio_ns
let total_s=$fin_s-$inicio_s
total_m=$(($total_s/60))

echo "INSERT: $total_ns [ns], $total_s [s], $total_m [min]" >> timeOnlyLoad.dat

#rm Meteo*
