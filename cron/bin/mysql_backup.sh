#!/usr/bin/env bash
# https://gist.github.com/dapplebeforedawn/7733a6486f02d21f68053c0c1e43431f

# Create the exec
#
EXEC_CREATE=`curl --silent --unix-socket /var/run/docker.sock "http://localhost/containers/${MYSQL_CONTAINER_NAME}/exec" -XPOST \
  -H "Content-Type: application/json" \
  -d '{
    "AttachStdout": true,
    "Tty": true,
    "Cmd": [ "/usr/local/bin/backup.sh"]
  }'
`
# Run the exec
#
EXEC_ID=$(echo $EXEC_CREATE | jq -r '.Id')

curl --silent --unix-socket /var/run/docker.sock "http://localhost/exec/${EXEC_ID}/start" -XPOST \
  -H "Content-Type: application/json" \
  -d '{
    "Detach": false,
    "Tty": true
  }'
