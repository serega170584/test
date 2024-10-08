ARG PHP_VERSION=8.2
ARG LOCAL="local"

ARG BASE="base"

FROM harbor.dev.ecom.corp/dockerhub/php:${PHP_VERSION}-cli-buster as base

ARG INFRA_DIR=infra/

ENV TZ=Europe/Moscow
ENV DEBIAN_FRONTEND=noninteractive
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY certs/* /usr/local/share/ca-certificates/
RUN apt-get update && apt-get install -y ca-certificates && update-ca-certificates && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y gcc make g++ curl librabbitmq-dev
RUN apt-get install -y ca-certificates \
    tzdata \
    vim \
    nano
RUN apt-get install -y wget \
    unzip \
    git \
    bash \
    autoconf
RUN apt-get install -y libpng-dev libzip-dev libxml2-dev libpq-dev libcurl4-openssl-dev \
    libcurl4 libonig-dev libssl-dev gnupg gnupg1 gnupg2


RUN curl -L https://github.com/roadrunner-server/roadrunner/releases/download/v2023.1.2/roadrunner-2023.1.2-linux-amd64.tar.gz | tar -xz
RUN mv roadrunner-2023.1.2-linux-amd64/rr /usr/local/bin/rr && chmod +x /usr/local/bin/rr && rm -rf roadrunner-2023.1.2-linux-amd64

RUN apt-get install libyaml-dev -y
RUN  pecl install yaml && echo "extension=yaml.so" > /usr/local/etc/php/conf.d/ext-yaml.ini && docker-php-ext-enable yaml
RUN pecl install amqp && echo "extension=amqp.so" > /usr/local/etc/php/conf.d/ext-amqp.ini && docker-php-ext-enable amqp

RUN docker-php-ext-configure intl \
    && docker-php-ext-configure pdo \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-configure pdo_pgsql \
    && docker-php-ext-configure phar \
    && docker-php-ext-configure xml \
    && docker-php-ext-configure curl \
    && docker-php-ext-configure mbstring \
    && docker-php-ext-configure opcache \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd \
    && docker-php-ext-configure bcmath \
    && docker-php-ext-configure soap \
    && docker-php-ext-configure pcntl \
    && docker-php-ext-configure calendar \
    && docker-php-ext-configure sockets \
    && docker-php-ext-configure mysqli \
    && docker-php-ext-configure pgsql \
#######
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install phar \
    && docker-php-ext-install xml \
    && docker-php-ext-install curl \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install opcache \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install soap \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install calendar \
    && docker-php-ext-install sockets \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pgsql

#RUN pecl update-channels && pecl install grpc
#RUN docker-php-ext-enable grpc

RUN pecl update-channels && pecl install -o -f redis pcov apcu \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis pcov apcu


ENV PATH $PATH:/var/www/vendor/bin:/var/www/bin

RUN php --ini

COPY ${INFRA_DIR}images/php8/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www

RUN echo PATH="$PATH:/var/www/vendor/bin" >> /etc/profile
RUN echo 'alias artisan="php /var/www/artisan"' >> /etc/profile

ARG CURRENT_UID=1000
ARG CURRENT_GID=1000
RUN addgroup --gid $CURRENT_GID professional2 || true
RUN adduser --shell /bin/bash --disabled-login --gid $CURRENT_GID --uid $CURRENT_UID professional2

RUN mkdir -p /home/professional2/.config/psysh && chown -R professional2 /home/professional2/
RUN chmod -R 777 /var/www

RUN apt-get update && apt-get install -y bc
RUN wget -qO /usr/local/bin/yq https://github.com/mikefarah/yq/releases/latest/download/yq_linux_amd64
RUN chmod a+x /usr/local/bin/yq


FROM ${BASE} as local
ARG CURRENT_UID=1000
ARG CURRENT_GID=1000
ARG INFRA_DIR=infra/
USER root

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chmod +x /usr/local/bin/composer

RUN apt-get update && \
 apt-get install -y \
    nodejs npm

RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs

COPY ${INFRA_DIR}images/php8/ssh_config /etc/ssh/ssh_config

USER $CURRENT_UID


FROM ${LOCAL} as local-xdebug
ARG CURRENT_UID=1000
ARG CURRENT_GID=1000
ARG INFRA_DIR=infra/
USER root

RUN pecl install xdebug-3.2.1 \
    && docker-php-ext-enable xdebug

COPY ${INFRA_DIR}images/php8/10-xdebug.ini /usr/local/etc/php/conf.d/10-xdebug.ini
USER $CURRENT_UID

FROM local as builder
ARG COMPOSER_KEY
USER 1000
RUN echo "{" > /var/www/auth.json
RUN echo '"gitlab-token": {' >> /var/www/auth.json
RUN echo '"gitlab.platform.corp": "'${COMPOSER_KEY}'"'>> /var/www/auth.json
RUN echo "}" >> /var/www/auth.json
RUN echo "}" >> /var/www/auth.json

COPY --chown=1000:1000 app/bin /var/www/bin
COPY --chown=1000:1000 app/config /var/www/config
COPY --chown=1000:1000 app/migrations /var/www/migrations
COPY --chown=1000:1000 app/phpGenerated /var/www/phpGenerated
COPY --chown=1000:1000 app/public /var/www/public
COPY --chown=1000:1000 app/src /var/www/src
COPY --chown=1000:1000 app/templates /var/www/templates
COPY --chown=1000:1000 app/translations /var/www/translations
COPY --chown=1000:1000 app/proto /var/www/proto

COPY --chown=1000:1000 app/.env /var/www/.env
COPY --chown=1000:1000 app/composer.json /var/www/composer.json
#убрать composer lock впоследствии
COPY --chown=1000:1000 app/composer.lock /var/www/composer.lock
COPY --chown=1000:1000 infra/Makefiles/10-Makefile-symfony /var/www/infra/Makefiles/10-Makefile-symfony
COPY --chown=1000:1000 Makefile /var/www/Makefile
COPY --chown=1000:1000 app/symfony.lock /var/www/symfony.lock
RUN cd /var/www && composer install

#RUN cd /var/www && bin/console cache:clear


#COPY --chown=1000:1000 /var/www/var /var/www/var #not needed because we will build container below
#RUN cache clear to build container

FROM builder as checks
USER 1000

COPY --chown=1000:1000 app/.env.test /var/www/.env.test
COPY --chown=1000:1000 app/phpunit.xml.dist /var/www/phpunit.xml.dist
COPY --chown=1000:1000 app/.php-cs-fixer.dist.php /var/www/.php-cs-fixer.dist.php
COPY --chown=1000:1000 app/tests /var/www/tests

RUN rm -rf /var/www/vendor
RUN cd /var/www && composer install
RUN rm -rf /var/www/auth.json
RUN cd /var/www && bin/console cache:clear

FROM ${BASE} as application

COPY --chown=1000:1000 --from=builder /var/www/bin /var/www/bin
COPY --chown=1000:1000 --from=builder /var/www/config /var/www/config
COPY --chown=1000:1000 --from=builder /var/www/migrations /var/www/migrations
COPY --chown=1000:1000 --from=builder /var/www/phpGenerated /var/www/phpGenerated
COPY --chown=1000:1000 --from=builder /var/www/public /var/www/public
COPY --chown=1000:1000 --from=builder /var/www/src /var/www/src
COPY --chown=1000:1000 --from=builder /var/www/templates /var/www/templates
COPY --chown=1000:1000 --from=builder /var/www/translations /var/www/translations

COPY --chown=1000:1000 --from=builder /var/www/proto /var/www/proto

COPY --chown=1000:1000 --from=builder /var/www/.env /var/www/.env
COPY --chown=1000:1000 --from=builder /var/www/composer.json /var/www/composer.json
COPY --chown=1000:1000 --from=builder /var/www/composer.lock /var/www/composer.lock
COPY --chown=1000:1000 --from=builder /var/www/infra/Makefiles/10-Makefile-symfony /var/www/infra/Makefiles/10-Makefile-symfony

COPY --chown=1000:1000 --from=builder /var/www/Makefile /var/www/Makefile
COPY --chown=1000:1000 --from=builder /var/www/symfony.lock /var/www/symfony.lock
COPY --chown=1000:1000 --from=builder /var/www/vendor /var/www/vendor

USER 1000
# создадим конфиг rr
COPY --chown=1000:1000 infra/roadrunner /var/www/roadrunner
COPY --chown=1000:1000 app/.rr.custom.yaml /var/www/.rr.custom.yaml
RUN yq eval-all '. as $item ireduce ({}; . * $item)' /var/www/roadrunner/* /var/www/.rr.custom.yaml > /var/www/.rr.yaml
RUN rm -rf /var/www/roadrunner && rm -rf /var/www/.rr.custom.yaml
# создадим конфиг rr

RUN rm -rf /var/www/auth.json
RUN cd /var/www && bin/console cache:clear

ENTRYPOINT ["rr"]
CMD [ "serve", "-d", "-c", "/var/www/.rr.yaml", "--debug", "-w", "/var/www" ]