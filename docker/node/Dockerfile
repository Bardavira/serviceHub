FROM node:20-alpine

WORKDIR /var/www

RUN apk add --no-cache bash su-exec

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]