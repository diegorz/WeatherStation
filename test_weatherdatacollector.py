#!/usr/bin/python

import subprocess

p = subprocess.Popen(["/home/weather/CQL/./Query.sh", "-q", "localhost", "data_2015", "Meteo1", "all", "04-06-2015", "04-06-2015"], stdout=subprocess.PIPE)

output, err = p.communicate()

print  output
