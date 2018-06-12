#!/bin/bash
: <<'License'
The MIT License
Copyright 2017 ANATOLII LYTVYNENKO 

www:	http://aizo.club
email:	dev[at]aizo.club

Permission is hereby granted, free of charge, to any person obtaining 
a copy of this software and associated documentation files (the "Software"), 
to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, 
and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies 
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

License


run_home=$(dirname $0)
if [ $run_home == '.' ]; then
    run_home=`pwd`
fi

exec_home=$(dirname $run_home)
project_root=`cd ${exec_home}/;pwd`

. ${run_home}/php-webserver-config.sh

${php_bin} -c "${php_ini}" \
-d open_basedir="/tmp:/home/users/ondemand:/opt/lang/java:${project_root}:${exec_home}" \
-S ${listen_address}:${listen_port} \
-t ${project_root} \
${exec_home}/ondemand-router.php  > /dev/null 2>&1

echo "runned"