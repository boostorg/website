#!/bin/sh

if [ $# != 1 ]
then
    echo "Usage: fetch.sh url"
    exit 0
fi

path=$(echo $1 | sed -n -E "s/https?:\/\/(www)?\.boost\.org\/doc\/libs\///p")

if [ x$path = x ]
then
    echo "Not a boost documentation URL: $1"
    exit 0
fi

cd $(dirname $0)
mkdir -p $(dirname $path)
cd $(dirname $path)

curl -O $1