#!/bin/bash
#
#Example: ./WeatherDataCollector.sh [-f Table initial_date final_date]

inicio_ns=`date +%s%N`
inicio_s=`date +%s`
meteo=1

while [ "$1" != "" ]; do
    case $1 in

######################## BY YEAR ##################################
	-y | --year )		shift
				table=$1
				initial_date=$2
				final_date=$3

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
        -t | -today )		table=$2		
				today=`date +%F`
				year=`date +%Y`
				month=`date +%m`
				day=`date +%d`
				while [ $meteo -le 11 ]; do

					wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$today.dat
				
					let meteo=meteo+1

				done	

				fin_ns=`date +%s%N`	
				fin=`date +%s`
				
				let total_ns=$fin_ns-$inicio_ns
				let total_s=$fin_s-$inicio_s
				let total_m=$total_s/60
			
				echo "Data downloaded for $today, it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeQuery.dat
				
				rm  Meteo*

				exit 1
				;;

############################# HELP ####################################	

	-h | --help )		echo "[-y Table Initial_Date Final_Date] [-t Table] [-h Help]"
				exit 1
				

    esac
    shift
done


############################ MAIN FULL ######################################
while [ $less_year -ge 0 ]; do
	echo "YEAR: $year"
	echo "LESS_YEAR: $less_year"	
	
	if [ $month -eq 13 ]
	then
		month="1"
	fi

	while [ $month -le 12 ] ; do
		echo
		echo "MONTH: $month"		
		echo

		if [ "$day" -eq 32 ]
		then
			day="1"
		fi
		
		while [ $day -le 31 ] ; do
			echo
			echo "DAY: $day"		
			echo
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
						

					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-0$day.dat.tgz
						
						tar -zxvf Meteo"$meteo"_$year-0$month-0$day.dat.tgz
						
					fi

				####################### Only month < 10 ###################
				elif [ $month -lt 10 ] 
				then
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-$day.dat
						
					
					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/0$month/Meteo"$meteo"_$year-0$month-$day.dat.tgz
			
						tar -zxvf Meteo"$meteo"_$year-0$month-$day.dat.tgz
					
					fi


				###################### Only day < 10 ###################
				elif [ $day -lt 10 ]
				then 
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-0$day.dat
	

					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-0$day.dat.tgz

						tar -zxvf Meteo"$meteo"_$year-$month-0$day.dat.tgz
						
					fi

				##################### Month and day > 10 ####################
				else
					echo "Meteo"$meteo"_$year-$month-$day"
						
					################### If year > 2011 ############	
					if [ $year -gt 2011 ]
					then	
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-$day.dat
					
				
					############ else: year < 2011 (dat.tgz) ###########
					else
						wget http://weather.aiv.alma.cl/data/data/files/$year/$month/Meteo"$meteo"_$year-$month-$day.dat.tgz
			
						tar -zxvf Meteo"$meteo"_$year-$month-$day.dat.tgz

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
done

echo
echo "FIN"

fin_ns=`date +%s%N`	
fin_s=`date +%s`

let total_ns=$fin_ns-$inicio_ns
let total_s=$fin_s-$inicio_s
total_m=$(($total_s/60))

echo "Data downloaded between $initial_date and $final_date, it has taken: $total_ns [ns], $total_s [s], $total_m [min]" >> timeQuery.dat			
rm Meteo*

	
