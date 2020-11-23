#!/usr/bin/env bash
cd ~/apps/dev.surfcaptain
git checkout master
git pull origin master
git merge develop
git push origin master
git checkout develop
cd ~/apps/surfcaptain
git pull
php artisan config:cache
php artisan view:cache
