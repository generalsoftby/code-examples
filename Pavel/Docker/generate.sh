#!/usr/bin/env bash

IMAGE_NAME=demo/crm-mongo-test-data:$1
CONTACTS_ITERATION=$2
CONTEXT=5bff845abf3bf6004031c911

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

export USER_ID=`id -u`
export GROUP_ID=`id -g`

# Stopping running containers
$DIR/../../bin/dev --test down
docker-compose -f $DIR/docker-compose.yml down

# Preparing folders
rm -rf $DIR/_data
mkdir $DIR/_data

# Starting services
docker-compose -f $DIR/docker-compose.yml up -d
sleep 5s

$DIR/../../bin/php bin/console project:generate-context --id $CONTEXT --name MainTestContext --reset
$DIR/../../bin/php bin/console project:generate-field-and-label-settings -c100 --context $CONTEXT --reset
$DIR/../../bin/php bin/console project:generate-performance-test-setup --context $CONTEXT

counter=1
while [ $counter -le $CONTACTS_ITERATION ]
do
    echo "Iteration: " $counter
    bin/php bin/console project:generate-contacts -c 10000 --context $CONTEXT
    ((counter++))
done
echo All done

# Shutting down
docker-compose -f $DIR/docker-compose.yml down

# Building the image
docker image rm $IMAGE_NAME
docker build -t $IMAGE_NAME $DIR

# Pushing the image
docker push $IMAGE_NAME
