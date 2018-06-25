#!/bin/bash
docker run  -it --rm -v D:\\Work\\enkora\\docker\\ansible-docker-controller\\ansible\\:/etc/ansible/  -v D:\\Work\\enkora\\docker\\ansible-docker-controller\\.ansible\\:/root/.ansible/ mvcaaa/nexus-ansible-docker-controller $@
