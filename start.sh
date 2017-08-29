#!/bin/bash

set -e

mkdir -p /run/php
mkdir -p /run/nginx

#mkdir -p /var/lib/nginx/logs
mkdir -p /var/log/image-host
mkdir -p /var/log/nginx
mkdir -p /var/log/php7
mkdir -p /var/log/supervisor

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
