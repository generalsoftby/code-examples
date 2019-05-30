#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
../../bin/dev --test down >> /dev/null 2> /dev/null

TAGS=( latest contacts-100k contacts-homer )

for TAG in ${TAGS[@]}; do
    echo
    echo --------${TAG}---------
    export TEST_DATA_TAG=$TAG
    $DIR/../../bin/dev --test up -d >> /dev/null 2> /dev/null
    sleep 5s
    $DIR/../../bin/php bin/console project:stats
    $DIR/../../bin/dev --test down >> /dev/null 2> /dev/null
    echo
done
