#!/bin/bash

set -eu

cp -r .github/ci/files/app app
cp -r .github/ci/pimcore_x/files/config config
cp -r .github/ci/pimcore_x/files/templates templates
cp -r .github/ci/files/bin bin
cp -r .github/ci/files/kernel kernel
cp -r .github/ci/pimcore_x/files/public public
cp -r .github/ci/files/var var

mkdir var/config
cp .github/ci/files/extensions.template.php var/config/extensions.php
cp .github/ci/pimcore_x/files/.env ./

# move that to composer.json when only pimcore X
composer require codeception/module-symfony:^1.6.0 --no-update
composer require codeception/phpunit-wrapper:^9 --no-update
composer require codeception/module-asserts:^2 --no-update
composer require php-http/guzzle7-adapter:^0.1.1 --no-update

if [ ${DEPENDENCIES:-lowest} = "highest" ]; then
    composer require pimcore/pimcore:10.x-dev --no-update
fi
