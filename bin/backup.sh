#!/bin/bash
SERVER=""
USER=""
PASSWORD=""
TARGETS=(
"/mnt/backup/01/data/" 
"/mnt/backup/02/data/"
)
NOW=$(date +"%Y%m%d%H%M%S")

# sshpass
if ! [ -x "$(command -v sshpass)" ]; then
	echo "Error: sshpass missing. Please run: sudo apt -y install sshpass"
	exit
fi
# rsync
if ! [ -x "$(command -v rsync)" ]; then
	echo "Error: rsync missing. Please run: sudo apt -y install rsync"
	exit
fi

for ix in ${!TARGETS[*]}
do
	if ! [ -d "${TARGETS[$ix]}" ]; then
		mkdir ${TARGETS[$ix]}
	fi
	if ! [ -f "${TARGETS[$ix]}../log.txt" ]; then
		touch "${TARGETS[$ix]}../log.txt"
		sleep 1
		NOW=$(date +"%Y%m%d%H%M%S")
	fi

	DATE=$(date -r "${TARGETS[$ix]}../log.txt" +"%Y%m%d%H%M%S")
	if [ "$DATE" -lt "$NOW" ]; then
		NOW="$DATE"
		TARGET="${TARGETS[$ix]}"
	fi
done

echo "Target: $TARGET"
if [ -v TARGET ]; then
	sshpass -p "$PASSWORD" rsync --delete --stats --exclude={'class','logs'} -av $USER@$SERVER:/var/www/ "$TARGET" 3>&1 1>"$TARGET../log.txt" 2>&1
fi
