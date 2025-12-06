#!/usr/bin/env sh
composer setup
chmod -R 777 storage

exec "$@"
