FROM alpine:3.5

ENV IMAGE_VERSION 1.7

# Install nginx package and remove cache
RUN apk add --update --no-cache nginx bash wget tzdata
RUN cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

# Copy basic files
RUN rm -f /etc/nginx/sites-available/* && \
    rm -f /etc/nginx/sites-enabled/*
ADD files /etc/nginx
RUN chmod 600 /etc/nginx/certs/*
RUN chown -R nobody:nobody /var/lib/nginx

EXPOSE 80

# root user will run 'nginx: master process'
# nobody user will run 'nginx: worker process' as dictated in the nginx.non-root.conf
CMD ["nginx", "-g", "daemon off;"]
