#!/bin/sh

DEFAULT_BASEPATH="../.."
BASEPATH="${1:-${DEFAULT_BASEPATH}}"

find ${BASEPATH}/www/application ${BASEPATH}/library \
-name '*.phtml' -or -name '*.php' | \
xgettext -L php -o messages.po --from-code=utf-8 \
--keyword=translate \
--keyword=headTitle \
-f -
