FROM bitnami/laravel:13
COPY . /app
RUN composer install --no-dev --optimize-autoloader