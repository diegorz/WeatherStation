#!/bin/bash
#
#Example: ./WeatherDataCollector.sh --initial_time [YYYY-MM-DD] --final_time[YYYY-MM-DD] --weatherstation_id[Meteo10] --Sensor[humidity]

inicio_ns=`date +%s%N`
inicio_s=`date +%s`
meteo=1

while [ "$1" != "" ]; do
    case $1 in

####################### IF TABLE AND WEATHER STATION ##########################

	-tw | --table_weather )	table=$2
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
			
				initial_month=$((10#$initial_month))
                                final_month=$((10#$final_month))
                                initial_day=$((10#$initial_day))
                                final_day=$((10#$final_day))
				
				year=$initial_year
                                month=$((10#$initial_month))
                                day=$((10#$initial_day))
                                final_month1=$((10#$final_month + 1))
                                final_day=$((10#$final_day + 1))	

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


################### IF TABLE_WEATHERSTATION #################################### 

	 -t | --table )		table=$2
                                sensor=$3
                                initial_date=$4
                                final_date=$5

                                initial_year=`echo ${initial_date:0:4}`
                                initial_month=`echo ${initial_date:5:2}`
                                initial_day=`echo ${initial_date:8:2}`
                                final_year=`echo ${final_date:0:4}`
                                final_month=`echo ${final_date:5:2}`
                                final_day=`echo ${final_date:8:2}`
		
				year=$initial_year
                                month=$((10#$initial_month))
                                day=$((10#$initial_day))
                                final_month1=$((10#$final_month + 1))
                                final_day=$((10#$final_day + 1))

				initial_month=$((10#$initial_month))
                                final_month=$((10#$final_month))
                                initial_day=$((10#$initial_day))
                                final_day=$((10#$final_day))

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
                               
				if [ "$sensor" = "all" ]
				then
        				sensor=\*
				fi
				
				cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE date_full >= '$initial_date' AND date_full <= '$final_date' ALLOW FILTERING ;" 10.200.117.244  > "$table"_"$sensor"_"$initial_date"_"$final_date".dat

				fin_ns=`date +%s%N`
				fin_s=`date +%s`

				let total_ns=$fin_ns-$inicio_ns
				let total_s=$fin_s-$inicio_s
				total_m=$(($total_s/60))

				echo "SELECT $sensor FROM $table WHERE date >= $initial_date AND date <= $final_date, it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeQuery.dat
				exit 1
				;;


############################# ALL SENSOR DATA FOR ONE TABLE #################################### 

         -a | -all )		table=$2
				cqlsh -e "SELECT * FROM weatherstation.$table ;" 10.200.117.244 > ALL_Data_from_"$table".dat

				fin_ns=`date +%s%N`
				fin_s=`date +%s`

				let total_ns=$fin_ns-$inicio_ns
				let total_s=$fin_s-$inicio_s
				total_m=$(($total_s/60))
					
				echo "SELECT * FROM $table, it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeQuery.dat

				exit 1
				;;

############################# HELP #################################### 

	-h | --help )           echo "[-tw Table Weather_Station_ID Sensor_Type Initial_Date Final_Date] [-t Table_WeatherStationID Sensor_Type Initial_Date Final_Date] [-a Table]"
                                exit 1

    esac
    shift
done



############### IF TABLE WEATHER AND STATION  ####################
if [ "$sensor" = "all" ]
then	
	sensor=\*
fi

while [ $less_year -ge 0 ]; do
        #echo "YEAR: $year"
        #echo "LESS_YEAR: $less_year"    

        if [ $month -eq 13 ]
        then
                month="1"
        fi

        while [ $month -le 12 ] ; do
	   #	echo
           #    echo "MONTH: $month"            
           #    echo

                if [ "$day" -eq 32 ]
                then
                        day="1"
                fi

                while [ $day -le 31 ] ; do
           #             echo
           #             echo "DAY: $day"                
           #             echo


		############### IF ALL WEATHER STATIONS ####################
		if [ "$weatherstation_id" = "all" ]
		then 

			while [ $meteo -le 11 ]; do
				#echo "meteo $meteo"

				############### If month and day < 10 ####################
				if [ $month -lt 10 ] && [ $day -lt 10 ]
				then
					cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '0$month"-"0$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
				#echo "month and day < 10"		

				####################### Only month < 10 ###################
				elif [ $month -lt 10 ]
				then
					 cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '0$month"-"$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
				#echo "month < 10"
			
				####################### Only day < 10 #####################
				elif [ $day -lt 10 ]
				then
					cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '$month"-"0$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
				#echo "day < 10"

				##################### Month and day > 10 ####################
				else
					cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '$month"-"$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
				#echo "month and day > 10"
				fi
	
			fin_ns=`date +%s%N`
	        	fin_s=`date +%s`

		        let total_ns=$fin_ns-$inicio_ns
		        let total_s=$fin_s-$inicio_s
		        total_m=$(($total_s/60))

			let meteo=meteo+1

			done	
			
		############### If ONLY ONE WEATHER STATION  ####################
		else
			############### If month and day < 10 ####################
                                if [ $month -lt 10 ] && [ $day -lt 10 ]
                                then
                                        cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '0$month"-"0$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
                                #echo "month and day < 10"               

                                ####################### Only month < 10 ###################
                                elif [ $month -lt 10 ]
                                then
                                         cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '0$month"-"$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
                                #echo "month < 10"

                                ####################### Only day < 10 #####################
                                elif [ $day -lt 10 ]
                                then
                                        cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '$month"-"0$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
                                #echo "day < 10"

                                ##################### Month and day > 10 ####################
                                else
                                        cqlsh -e "SELECT $sensor FROM weatherstation.$table WHERE weatherstation_id = 'Meteo$meteo' AND date = '$month"-"$day' ;" 10.200.117.244 >> "$table"_"$weatherstation_id"_"$sensor"_"$initial_date"_"$final_date".dat
                                #echo "month and day > 10"
                                fi
		

		fin_ns=`date +%s%N`
		fin_s=`date +%s`

		let total_ns=$fin_ns-$inicio_ns
		let total_s=$fin_s-$inicio_s
		total_m=$(($total_s/60))

		fi      
         	
		let day=day+1
		
		if [ $month -eq $final_month ]
		then
			if [ $day -eq $final_day ]
			then
				day="32"
			fi
		fi

		done

		let month=month+1

		if [ $year -eq $final_year ]
		then
			if [ $month -eq $final_month1 ]
			then
				month="13"
			fi
		fi

	done

        let year=year+1
        let less_year=less_year-1
        
done

echo "SELECT $sensor FROM $table WHERE weatherstation_id = '$weatherstation_id' AND date = '$initial_date' AND date = '$final_date', it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeQuery.dat
