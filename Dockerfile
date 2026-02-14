FROM wordpress:6.9-php8.3-apache
RUN apt-get update && apt-get install -y ca-certificates && rm -rf /var/lib/apt/lists/*

# Suppress PHP notices/warnings on screen — logs go to /var/log instead
RUN echo "display_errors = Off" > /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_log = /var/log/php_errors.log" >> /usr/local/etc/php/conf.d/errors.ini