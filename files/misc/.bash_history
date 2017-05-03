cd /var/www/installations
cd /var/www/boot
cd /home/enkorascripts/scripts
vim /var/www/boot/defaults.toml
vim /var/www/boot/configs/nexus.toml
vim /etc/hosts
echo -e "10.0.0.15\tmysql" >> /etc/hosts
su - enkorascripts
tail -f /var/log/nginx/*
sh scripts/test-docker.sh tests/folder/SomeTest.php test
bash scripts/phinx.sh status --environment=dev
bash scripts/phinx.sh migrate --environment=dev
bash scripts/phinx.sh status --environment=nexus
bash scripts/phinx.sh migrate --environment=nexus
bash scripts/phinx.sh status --environment=nexus_base
bash scripts/phinx.sh migrate --environment=nexus_base
mysql -hmysql -uwww -p
