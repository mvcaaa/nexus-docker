FROM mhart/alpine-node:8.7

ENV IMAGE_VERSION 1.7
RUN echo fs.inotify.max_user_watches=524288 | tee -a /etc/sysctl.conf && sysctl -p

RUN apk update
RUN apk add --no-cache bash tzdata && \
    apk add --no-cache --update nodejs
RUN cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

RUN npm install -g gulp @angular/cli

EXPOSE 4200
