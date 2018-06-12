
	
	/server
		/bin
			./solc		-The compiled Solidity interpreter binary file
					https://solidity.readthedocs.io/en/latest/installing-solidity.html#building-from-source


	/etc.d
		./php-webserver-check.sh	- is the server work now? Check it. 
							cron job
							* * * * * /bin/bash /{PROJECT_ROOT}/etc.d/php-webserver-check.sh /dev/null 2>&1
	
		./php-webserver-config.sh	- server settings
		./php-webserver-run.sh	- script is for server run


	/client
		./rpc_sol_client.php - It's class for compiling smart-contract by RPC request
	

