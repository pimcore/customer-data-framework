#!/bin/bash

set -eu

cp -r .github/ci/files/config config
cp -r .github/ci/files/templates templates
cp -r .github/ci/files/bin bin
chmod 755 bin/console
cp -r .github/ci/files/kernel kernel
cp -r .github/ci/files/public public
cp -r .github/ci/files/var var

cp .github/ci/files/.env ./