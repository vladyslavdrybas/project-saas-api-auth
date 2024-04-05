#!/usr/bin/env bash

declare -A envVars=()

function getVarsFromFile () {
	for fileName in $1;
	do
		while read -r LINE; do
			if [[ $LINE == *'='* ]] && [[ $LINE != '#'* ]]; then
			  	key=$(echo "$LINE" | cut -d '=' -f 1)
			  	value=$(echo "$LINE" | cut -d '=' -f 2-)

			  	envVars[$key]="${value}";
			fi
		done < "${fileName}"
	done
}

getVarsFromFile .env.local

envFile=".env.${envVars[APP_ENV]}"

envVars=()

getVarsFromFile .env.dist

if [ -f "${envFile}" ]; then
	getVarsFromFile "${envFile}"
fi

getVarsFromFile .env.local

if [ -f ".env" ]; then
	rm .env
fi

for key in "${!envVars[@]}";
do
	echo "${key}=${envVars[${key}]}" >> .env
done

chmod 755 .env
