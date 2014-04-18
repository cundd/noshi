#!/bin/sh

HOST=$1
PORT=$2

if [[ "$HOST" == "" ]]; then
    HOST=localhost
fi
if [[ "$PORT" == "" ]]; then
    PORT=9000
fi
php -S $HOST:$PORT -t .