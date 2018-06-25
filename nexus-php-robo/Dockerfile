FROM mvcaaa/nexus-php-fpm:1.5

ENV IMAGE_VERSION 0.2

RUN wget http://robo.li/robo.phar && \
    chmod +x robo.phar && \
    mv robo.phar /usr/bin/robo

RUN robo --version

ENTRYPOINT ["/usr/bin/robo"]
