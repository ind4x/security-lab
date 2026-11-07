#!/bin/sh
# Lance l'application vulnerable (8000) et la version corrigee (8001)
php -S 0.0.0.0:8000 -t /var/www/app &
php -S 0.0.0.0:8001 -t /var/www/app_corrige
