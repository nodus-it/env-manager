FROM nodusit/laravel-env-manager:latest

RUN apk add --no-cache nginx supervisor

COPY .docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY .docker/nginx/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]



