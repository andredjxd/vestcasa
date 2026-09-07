# Runtime PHP 8.1 + Apache replicando o ambiente de producao original.
# Build context = raiz do projeto (nao a pasta docker/).
# O codigo e copiado da propria build context; quando usado com bind mount
# (docker-compose local de teste), o volume simplesmente sobrepoe essa copia.
FROM php:8.1-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libxslt1-dev \
        libonig-dev \
        libffi-dev \
        libssl-dev \
        locales \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        mysqli \
        pdo_mysql \
        gd \
        zip \
        xsl \
        exif \
        ftp \
        gettext \
        calendar \
        sockets \
        pcntl \
        sysvmsg \
        sysvsem \
        sysvshm \
        shmop \
        mbstring \
        ffi \
    && sed -i '/pt_BR.UTF-8/s/^# //' /etc/locale.gen \
    && locale-gen \
    && a2enmod rewrite ssl \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/vestcasa.ini /usr/local/etc/php/conf.d/zz-vestcasa.ini
COPY docker/apache/vestcasa.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/vestcasa
RUN chown -R www-data:www-data /var/www/html/vestcasa
