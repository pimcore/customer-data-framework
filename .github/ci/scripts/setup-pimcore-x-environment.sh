#!/bin/bash

set -eu

mkdir -p var/config
mkdir -p bin

cp -r .github/ci/pimcore_x/files/config/. config
cp -r .github/ci/pimcore_x/files/templates templates
cp -r .github/ci/pimcore_x/files/bin/console bin/console
cp -r .github/ci/pimcore_x/files/src src
cp -r .github/ci/pimcore_x/files/public public
cp .github/ci/pimcore_x/files/extensions.template.php var/config/extensions.php
cp .github/ci/pimcore_x/files/.env ./

# temp. until elasticsearch/elasticsearch 7.11 is released
composer config minimum-stability "dev"
composer config prefer-stable true
composer require pimcore/pimcore:dev-master --no-update
