FROM node:8.9-alpine

ENV IMAGE_VERSION 1.9.14
# Not working in Windows host containers
# RUN echo fs.inotify.max_user_watches=524288 | tee -a /etc/sysctl.conf && sysctl -p

RUN apk update && \
	apk --no-cache add --virtual \
		native-deps  \
		g++ \
		gcc \
		libgcc \
		libstdc++ \
		linux-headers \
		make \
		python \
		bash \
		ca-certificates \
		wget

# npm replaced here by yarn - too buggy
RUN yarn global add npm@5 && \
	npm --version
RUN yarn global add gulp @angular/cli@^1.7.3 && \
	ng --version
RUN apk del native-deps
RUN apk --no-cache add bash tzdata && \
	cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

EXPOSE 4200
