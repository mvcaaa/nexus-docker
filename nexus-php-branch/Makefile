GIT_COMMMIT_HASH    = $$(git --git-dir=files/nexus.git log -1 --pretty=%h)

WORKING_DIR 		= $(shell pwd)
BRANCH				= develop
NAME   				= mvcaaa/nexus-${BRANCH}
LATEST 				= ${NAME}:latest


build:
	@rm -rf files/nexus.branch*
	@git --git-dir=files/nexus.git fetch origin +refs/heads/*:refs/heads/* --prune
	@GIT_DIR=files/nexus.git git archive --format=tar.gz --prefix=nexus/ ${BRANCH} >files/nexus.branch.tar.gz
	@docker build --no-cache --rm --squash --build-arg BRANCH_NAME=${BRANCH} -t ${NAME}:${GIT_COMMMIT_HASH} .
	@docker tag ${NAME} ${LATEST}
	@rm -rf files/nexus.branch*

push:
	@docker push ${NAME}

login:
	@docker log -u ${DOCKER_USER} -p ${DOCKER_PASS}
