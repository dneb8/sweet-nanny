FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Instalar todas las dependencias del sistema en un solo RUN
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    nano \
    build-essential \
    poppler-utils \
    npm \
    default-mysql-client \
    locales \
    zip \
    curl \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    libxml2-dev \
    libxslt-dev \
    python-dev-is-python3 \
    libonig-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar locale
ENV LANG=C.UTF-8

# Instalar extensiones de PHP
RUN docker-php-ext-install soap xsl sockets \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd intl

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar Node.js 18
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Crear usuario para Laravel
ARG WWW_USER_ID
ARG WWW_GROUP_ID

RUN groupadd -g ${WWW_GROUP_ID} www \
    && useradd -u ${WWW_USER_ID} -ms /bin/bash -g www www

# Copiar archivos de Node
COPY ./package*.json ./

# Copiar el resto de la aplicación y asignar permisos
COPY --chown=www:www . /var/www

# Cambiar usuario
USER www

# Exponer puerto y arrancar PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
