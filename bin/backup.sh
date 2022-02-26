#!/bin/bash
SERVER=""
USER=""
PASSWORD=""
TARGETS=(
"/mnt/backup/01/" 
"/mnt/backup/02/"
)
NOW=$(date +"%Y%m%d%H%M%S")

# install sshpass
if ! [ -x "$(command -v sshpass)" ]; then
	apt -y install sshpass
	

for ix in ${!TARGETS[*]}
do
	DATE=$(date -r "${TARGETS[$ix]}../log.txt" +"%Y%m%d%H%M%S")
	if [ "$DATE" -lt "$NOW" ]; then
		NOW="$DATE"
		TARGET="${TARGETS[$ix]}"
	fi
done

echo "$TARGET"
if [ -v TARGET ]; then
	sshpass -p "$PASSWORD" rsync --delete --stats --exclude 'logs' -av $USER@$SERVER:/var/www/ "$TARGET" 3>&1 1>"$TARGET../log.txt" 2>&1
fi
