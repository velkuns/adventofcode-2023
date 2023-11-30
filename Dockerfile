FROM php:8.2-cli

#~ Add php extention
RUN  apt update \
  && apt install -y \
         libzip-dev \
  && apt clean \
  && docker-php-ext-install zip

#~ Install composer
RUN  curl -fsSL 'https://getcomposer.org/download/latest-stable/composer.phar' -o 'composer' \
  && chmod 0755 composer \
  && mv composer /usr/local/bin/composer


COPY bin/          /var/application/bin
COPY config/       /var/application/config
COPY data/         /var/application/data
COPY src/          /var/application/src

WORKDIR /var/application

RUN composer install

#~ WIP - Don't really work for now
ENTRYPOINT ["/var/application/bin/aoc"]

CMD ["--help"]
