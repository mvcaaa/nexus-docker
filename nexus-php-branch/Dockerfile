FROM mvcaaa/nexus-php-fpm:2.1
RUN echo Building image for branch: $BRANCH_NAME

ARG BRANCH_NAME=develop
ARG GITHUB_TOKEN="please_override_at_runtime"

COPY files/boot /var/www/boot
ADD files/nexus.branch.tar.gz /var/www/installations

WORKDIR "/var/www/installations/nexus/lib"
RUN composer --prefer-dist --dev --verbose --optimize-autoloader install
