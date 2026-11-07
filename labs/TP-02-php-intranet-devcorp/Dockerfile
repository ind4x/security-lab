FROM php:8.4-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev iputils-ping \
 && docker-php-ext-install pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
COPY app/ app/
COPY app_corrige/ app_corrige/
COPY start.sh /start.sh

RUN mkdir -p app/uploads app_corrige/uploads app/data app_corrige/data \
 && chmod -R 777 app/uploads app_corrige/uploads app/data app_corrige/data

EXPOSE 8000 8001
CMD ["sh", "/start.sh"]
