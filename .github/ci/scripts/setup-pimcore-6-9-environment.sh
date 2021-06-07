#!/bin/bash

set -eu

mkdir -p var/config
mkdir -p bin

cp -r .github/ci/pimcore_6_9/files/app app
cp -r .github/ci/pimcore_6_9/files/bin/console bin/console
chmod 755 bin/console
cp -r .github/ci/pimcore_6_9/files/web web
cp -r .github/ci/pimcore_6_9/files/kernel kernel

cp .github/ci/pimcore_6_9/files/extensions.template.php var/config/extensions.php
cp app/config/parameters.example.yml app/config/parameters.yml

# temp. until elasticsearch/elasticsearch 7.11 is released
composer config minimum-stability "dev"
composer config prefer-stable true
composer require pimcore/pimcore:6.9.x-dev --no-update
composer require symfony/symfony:4.3 --no-update
composer require codeception/codeception:2.4.5 --no-update

# move that to composer.json when only pimcore X
#composer require codeception/module-symfony:^1.6.0 --no-update
#composer require codeception/phpunit-wrapper:^9 --no-update
#composer require codeception/module-asserts --no-update