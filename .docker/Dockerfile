FROM php:8.3-alpine

RUN apk --no-cache add \
        $PHPIZE_DEPS \
        curl \
        git \
        icu-dev \
        less \
        libzip-dev \
        linux-headers \
        nano \
        openssh-client \
        sudo \
        supervisor \
        vim \
        libpng-dev \
        zsh
RUN docker-php-ext-install -j5 zip pcntl intl gd
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

#COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN adduser -u 1000 -G wheel -D application -s /bin/zsh

COPY config/sudoers /etc/sudoers
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY config/php.ini /usr/local/etc/php/conf.d/php.ini
COPY config/.zshrc /root/.zshrc

USER application

COPY config/.zshrc /home/application/.zshrc

WORKDIR /home/application/projects

#Zsh minimal installation
RUN sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

ENV TERM xterm-256color
