#!/bin/bash
#
#Example: ./WeatherDataCollector.sh --initial_time [YYYY-MM-DD] --final_time[YYYY-MM-DD] --weatherstation_id[Meteo10] --Sensor[humidity]

inicio_ns=`date +%s%N`
inicio=`date +%s`

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

cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = '$weatherstation_id' AND date >= '$initial_date' AND date <= '$final_date' ;" 10.200.117.244  > Data_"$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat

fin_ns=`date +%s%N`
fin=`date +%s`

let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo
echo "SELECT $sensor FROM $table.$weatherstation_id WHERE date >= $initial_date AND date <= $final_date, it has taken: $total_ns [ns], $total [s]" >> timeQuery.dat
