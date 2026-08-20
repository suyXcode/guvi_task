#!/bin/sh
set -e

: "${PORT:=10000}"

# Point Apache at whatever port Render assigned this instance
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
