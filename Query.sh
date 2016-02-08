#!/bin/bash
#
#Example: ./WeatherDataCollector.sh --initial_time [YYYY-MM-DD] --final_time[YYYY-MM-DD] --weatherstation_id[Meteo10] --Sensor[humidity]

inicio_ns=`date +%s%N`
inicio_s=`date +%s`
inicio_m=`date +%M`
meteo=1

while [ "$1" != "" ]; do
    case $1 in

	-f | --full )		table=$2
				weatherstation_id=$3
				sensor=$4
				initial_date=$5
				final_date=$6
				
				initial_year=`echo ${initial_date:0:4}`
				initial_month=`echo ${initial_date:5:2}`
				initial_day=`echo ${initial_date:8:2}`
				final_year=`echo ${final_date:0:4}`
				final_month=`echo ${final_date:5:2}`
				final_day=`echo ${final_date:8:2}`
				
				let less_year=$final_year-$initial_year
				let less_month=$final_month-$initial_month
				let less_day=$final_day-$initial_day

				if [ "$less_year" -lt 0 ] 
				then
					echo "Entered wrong dates"
					exit 1
				else 
					if [ "$less_year" -eq 0 ]
					then 
						if [ "$less_month" -lt 0 ] 
						then 
							echo "Entered wrong dates"
							exit 1
						else
							if [ "$less_month" -eq 0 ] 
							then
								if [ "$less_day" -lt 0 ]
								then
									echo "Entered wrong dates"
									exit 1
								fi
							fi
						fi				
					fi
				fi
				;;

	-h | --help )		echo "[-f Table Weather_Station_ID Sensor_Type Initial_Date Final_Date] [-h Help] [-a Table]"
				exit 1
				;;

         -a | -all )		table=$2
				cqlsh -e "SELECT * FROM weatherstation.$table ;" 10.200.117.244 > ALL_Data_from_"$table".dat
				fin_ns=`date +%s%N`
				fin=`date +%s`

				let total_ns=$fin_ns-$inicio_ns
				let total=$fin-$inicio
				echo "SELECT * FROM $table, it has taken: $total_ns [ns], $total [s]" >> timeQuery.dat

				exit 1

    esac
    shift
done

if [ "$sensor" = "all" ]
then	
	sensor=\*
fi

#echo $table
#echo $weatherstation_id
#echo $sensor
#echo $initial_date
#echo $final_date

if [ "$weatherstation_id" = "all" ]
then 
	while [ $meteo -le 11 ]; do
	
	cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date >= '$initial_date' AND date <= '$final_date' ORDER BY date_full ASC ;" 10.200.117.244  > Data_"$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
	
	let meteo=meteo+1

	done	

else
	cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = '$weatherstation_id' AND date >= '$initial_date' AND date <= '$final_date' ORDER BY date_full ASC ;" 10.200.117.244  > Data_"$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat

fi

fin_ns=`date +%s%N`
fin_s=`date +%s`
fin_m=`date +%M`

let total_ns=$fin_ns-$inicio_ns
let total_s=$fin_s-$inicio_s
total_m=$(($total_s/60))

echo "SELECT $sensor FROM $table.$weatherstation_id WHERE date >= $initial_date AND date <= $final_date, it has taken: $total_ns [ns], $total [s], $total_m [min]" >> timeQuery.dat
