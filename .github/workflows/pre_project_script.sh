#!/bin/bash

echo "Configure Hyva repo"
composer config repositories.private-packagist composer https://hyva-themes.repo.packagist.com/m248-test-367mmnuc7j/
composer config --global --auth http-basic.hyva-themes.repo.packagist.com token $HYVA_PACKAGIST_TOKEN
