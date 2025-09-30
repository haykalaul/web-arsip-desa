## Final stage
FROM base AS final
WORKDIR /var/www/html

# Copy application code FIRST
COPY . .

# Copy built assets from node-builder
COPY --from=node-builder /app/public/build public/build

# Then run composer install
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public"]
