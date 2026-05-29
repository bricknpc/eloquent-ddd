FROM php:8.5-cli

# System deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configure Xdebug for code coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer 2.9.2
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.9.2 --install-dir=/usr/local/bin --filename=composer

# Create a user with your host UID/GID
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m appuser

# Create app directory
WORKDIR /app

# Switch to non-root user
USER appuser
