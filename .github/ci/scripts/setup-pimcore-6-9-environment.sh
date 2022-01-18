#!/bin/bash

set -eu

cp -r .github/ci/files/app app
cp -r .github/ci/pimcore_6_9/files/app/config app/config
cp -r .github/ci/files/bin bin
cp -r .github/ci/pimcore_6_9/files/web web
cp -r .github/ci/files/kernel kernel
cp -r .github/ci/files/var var

mkdir var/config
cp .github/ci/files/extensions.template.php var/config/extensions.php
cp app/config/parameters.example.yml app/config/parameters.yml

composer require codeception/codeception:2.4.5 --no-update
composer require php-http/guzzle6-adapter:^2 --no-update
# Fix: Wrong dependency version myclabs/deep-copy:^1.3 from pimcore 6.9
composer require myclabs/deep-copy:^1.5

if [ ${DEPENDENCIES:-lowest} = "highest" ]; then
    composer require pimcore/pimcore:6.9.x-dev --no-update
fi
