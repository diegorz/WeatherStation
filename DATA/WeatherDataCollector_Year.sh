#!/bin/bash
# ALMA WEATHER DATA COLLECTOR

inicio_ms=`date +%s%3N`
inicio_s=`date +%s`
meteo=1

while [ "$1" != "" ]; do
    case $1 in

######################## BY YEAR ##################################
	-y | --year )		shift
				ip_cluster=$1
				table=$2
				initial_date=$3
				final_date=$4
				
				initial_year=`echo ${initial_date:6:4}`
				initial_month=`echo ${initial_date:3:2}`
				initial_day=`echo ${initial_date:0:2}`
				final_year=`echo ${final_date:6:4}`
				final_month=`echo ${final_date:3:2}`
				final_day=`echo ${final_date:0:2}`
				
				initial_month=$((10#$initial_month))
				final_month=$((10#$final_month))
				initial_day=$((10#$initial_day))
				final_day=$((10#$final_day))

				year=$initial_year
				month=$((10#$initial_month))
				day=$((10#$initial_day))
				final_month1=$((10#$final_month + 1))
				final_day=$((10#$final_day + 1))

				let less_year=final_year-initial_year
				let less_month=final_month-initial_month
				let less_day=final_day-initial_day
				
	
				if [ $less_year -lt 0 ] 
				then
					echo "Entered wrong dates"
					exit 1
				else 
					if [ $less_year -eq 0 ]
					then 
						if [ $less_month -lt 0 ] 
						then 
							echo "Entered wrong dates"
							exit 1
						else
							if [ $less_month -eq 0 ] 
							then
								if [ $less_day -lt 0 ]
								then
									echo "Entered wrong dates"
									exit 1
								fi
							fi
						fi				
					fi
				fi
				;;

############################# DEFAULT : TODAY DATA FOR TABLE ######################################
        -t | -today )		ip_cluster=$2
				table=$3		
				today=`date +%F`
				year=`date +%Y`
				month=`date +%m`
				day=`date +%d`
				while [ $meteo -le 11 ]; do

					wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$today.dat
				
					sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo','$month'-'$day',/' Meteo"$meteo"_$today.dat > Meteo"$meteo"_$today.csv

					$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$today.csv' ;" $ip_cluster	
	
					let meteo=meteo+1

				done	

				fin_ms=`date +%s%3N`	
				fin=`date +%s`
				
				let total_ns=$fin_ns-$inicio_ns
				let total_s=$fin_s-$inicio_s
				let total_m=$total_s/60
			
				echo "COPY weatherstation.$table for $today, it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeLoad.dat
				
				rm  Meteo*

				exit 1
				;;

############################# HELP ####################################	

	-h | --help )		echo "[-y IP_Cluster Table Initial_Date Final_Date] [-t IP_Cluster Table] [-h Help]"
				exit 1
				

    esac
    shift
done


############################ MAIN FULL ######################################
#while [ $less_year -ge 0 ]; do
#	echo "YEAR: $year"
#	echo "LESS_YEAR: $less_year"	
	
	if [ $month -eq 13 ]
	then
		month="1"
	fi

	while [ $month -le 12 ] ; do
#		echo
#		echo "MONTH: $month"		
#		echo

		if [ "$day" -eq 32 ]
		then
			day="1"
		fi
		
		while [ $day -le 31 ] ; do
		#	echo
		#	echo "DAY: $day"		
		#	echo
		
			if [ $meteo -eq 12 ]
			then 
				meteo="1"
			fi

			while [ $meteo -le 11 ]; do
				
				############### If month and day < 10 ####################
				if [ $month -lt 10 ] && [ $day -lt 10 ]
				then 

					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-0$day.dat
						
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo',0'$month'-0'$day',/' Meteo"$meteo"_$year-0$month-0$day.dat > Meteo"$meteo"_$year-0$month-0$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-0$month-0$day.csv' ;" $ip_cluster
					

					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-0$day.dat.tgz
						
						tar -zxvf Meteo"$meteo"_$year-0$month-0$day.dat.tgz
						
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo',0'$month'-0'$day',/' Meteo"$meteo"_$year-0$month-0$day.dat > Meteo"$meteo"_$year-0$month-0$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-0$month-0$day.csv' ;" $ip_cluster
						
					fi

				####################### Only month < 10 ###################
				elif [ $month -lt 10 ] 
				then
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-$day.dat
						
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo',0'$month'-'$day',/' Meteo"$meteo"_$year-0$month-$day.dat > Meteo"$meteo"_$year-0$month-$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-0$month-$day.csv' ;" $ip_cluster
					
					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-$day.dat.tgz
			
						tar -zxvf Meteo"$meteo"_$year-0$month-$day.dat.tgz
					
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo',0'$month'-'$day',/' Meteo"$meteo"_$year-0$month-$day.dat > Meteo"$meteo"_$year-0$month-$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-0$month-$day.csv' ;" $ip_cluster					
					
					fi


				###################### Only day < 10 ###################
				elif [ $day -lt 10 ]
				then 
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-0$day.dat
	
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo','$month'-0'$day',/' Meteo"$meteo"_$year-$month-0$day.dat > Meteo"$meteo"_$year-$month-0$day.csv					
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-$month-0$day.csv' ;" $ip_cluster

					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-0$day.dat.tgz

						tar -zxvf Meteo"$meteo"_$year-$month-0$day.dat.tgz
						
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo','$month'-0'$day',/' Meteo"$meteo"_$year-$month-0$day.dat > Meteo"$meteo"_$year-$month-0$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-$month-0$day.csv' ;" $ip_cluster				

					fi

				##################### Month and day > 10 ####################
				else
					echo "Meteo"$meteo"_$year-$month-$day"
						
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then	
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-$day.dat
					
						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo','$month'-'$day',/' Meteo"$meteo"_$year-$month-$day.dat > Meteo"$meteo"_$year-$month-$day.csv	
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-$month-$day.csv' ;" $ip_cluster
				
					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-$day.dat.tgz
			
						tar -zxvf Meteo"$meteo"_$year-$month-$day.dat.tgz

						sed -e '1,4d' -e 's/; /,/g' -e 's/T/ /' -e 's/^/Meteo'$meteo','$month'-'$day',/' Meteo"$meteo"_$year-$month-$day.dat > Meteo"$meteo"_$year-$month-$day.csv
						
						$CQL -e "COPY weatherstation.$table (weatherstation_id, date, date_full, humidity, temperature , dewpoint , winddirection , windspeed , pressure ) FROM 'Meteo"$meteo"_$year-$month-$day.csv' ;" $ip_cluster
						
					fi
				

				fi

				let meteo=meteo+1
			
			done

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
#done

echo
echo "FIN"

fin_ms=`date +%s%3N`	
fin_s=`date +%s`

let total_ms=$fin_ms-$inicio_ms
let total_s=$fin_s-$inicio_s
total_m=$(($total_s/60))

echo "COPY weatherstation.$table"_ALLMeteo" between $initial_date and $final_date, it has taken: $total_ms [ms], $total_s [s], $total_m [min] from CLUSTER: $ip_cluster" >> timeLoad.dat

rm Meteo*

	
