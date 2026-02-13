FROM wordpress:6.7-php8.3-apache
RUN apt-get update && apt-get install -y ca-certificates && rm -rf /var/lib/apt/lists/*
