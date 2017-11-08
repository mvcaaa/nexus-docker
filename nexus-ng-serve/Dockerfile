FROM node:6.11-alpine

ENV IMAGE_VERSION 1.9.7
#RUN echo fs.inotify.max_user_watches=524288 | tee -a /etc/sysctl.conf && sysctl -p

RUN apk update && \
  apk --no-cache add --virtual native-deps g++ gcc libgcc libstdc++ linux-headers make python 

RUN npm config set user 0 && \
	npm config set unsafe-perm true && \
	npm install --quiet -unsafe-perm gulp @angular/cli@1.3.2 -g
  
RUN apk del native-deps
RUN apk --no-cache add bash tzdata && \ 
	cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

EXPOSE 4200
