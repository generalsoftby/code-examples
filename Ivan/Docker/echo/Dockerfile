FROM node:10-alpine

WORKDIR /app

RUN apk add --update --no-cache openssl curl \
 && apk add --update --no-cache --virtual .build-deps \
        binutils-gold \
        g++ \
        gcc \
        gnupg \
        libgcc \
        linux-headers \
        make \
        python \
 && yarn global add --prod --no-lockfile laravel-echo-server \
 && apk del .build-deps \
 && yarn cache clean

COPY laravel-echo-server.json /app/
COPY docker-entrypoint.sh /usr/local/bin/

EXPOSE 6001

CMD ["laravel-echo-server", "start"]
