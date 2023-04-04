FROM php:8.0

WORKDIR /app

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN composer install

EXPOSE 8000

CMD ["symfony", "server:start"]