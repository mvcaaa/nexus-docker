# nexus-docker

## Description
This is source of images for running nexus stack on local dev environment.

Images are pre-built with composer and node packages and hosted at DockerHub.

Every service is running at separate container. 
There is five of them: ng-serve(angular client running ng serve), nginx, php-fpm, redis, mysql.

## Achtung !
Please do not include any secure data or any code in container images !!! 
 
## Basic usage
1. Clone repo
2. Create file `docker-compose.override.yml` with correct paths(it is left part before :, right part will be ok until you change it) and parameters. DO NOT TOUCH `docker-compose.yml` !!!11. Example file:
```yml
version: '2'
services:
  web-data:
    volumes:
      - E:\Work\nexus:/var/www/installations/nexus
      - E:\Work\backups:/var/backups
      - ./nexus-php-fpm/files/boot:/var/www/boot
  mysql:
    command: --skip-grant-tables
    volumes:
      - E:\Work\mysql-data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=ScheissePasswortEinZweiDrei
  
  redis:
    volumes:
      - E:\Work\enkora\redis-data:/data
```
3. Run `docker-compose up` 
4. Wait for the message ` ng-serve_1  | webpack: Compiled successfully. `. Ng must compile its crap. It takes ~ 2-10 mins depends computer, moon and stars.
5. Configure nexus boot parameters any time you want - its shared from docker host to php-fpm container.
 ```diff
 + TODO: move `nexus-php-fpm/files/boot` from here to nexus repo.
 ```


 

## Advanced usage (How to break things)
If you need some changes to container(composer.json or package.json is changed)  - make a new branch, break it, test it locally and fail, commit and push this to GitHub, create Pull Request. 
Once PR is accepted and branch is merged with master(no) - all images will rebuilt and pushed to DockerHub. 
   
Please do not push to master branch directly - every push will trigger image rebuild process(~10 min).
 ```diff
 + TODO: it must be protected
 ```

NOTE: You must change image version manually, see `ENV IMAGE_VERSION 1.0` in corresponding Dockerfile. 
 ```diff
 + TODO: automate image tagging from Git tag or/and commit revision number.
 ```