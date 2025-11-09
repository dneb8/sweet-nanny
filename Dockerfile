FROM php:8.2-fpm

# Establecer directorio de trabajo
WORKDIR /var/www

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    nano \
    build-essential \
    poppler-utils \
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
    libonig-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar locale
ENV LANG=C.UTF-8

# Instalar extensiones PHP necesarias para Laravel
RUN docker-php-ext-install soap xsl sockets \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd intl

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar Node.js 18
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Crear usuario no root
ARG WWW_USER_ID=1000
ARG WWW_GROUP_ID=1000

RUN groupadd -g ${WWW_GROUP_ID} www \
    && useradd -u ${WWW_USER_ID} -ms /bin/bash -g www www

# Copiar composer.json y package.json primero (para cacheo eficiente)
COPY ./composer.json ./composer.lock ./
COPY ./package*.json ./

# Instalar dependencias de producción PHP y JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Copiar el resto del proyecto
COPY --chown=www:www . /var/www

# Cambiar a usuario www
USER www

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Comando de arranque
CMD ["php-fpm"]
