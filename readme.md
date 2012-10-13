chatwall
========

The source code is distributed under New BSD licence.

Demo: http://chatwall.dev8.ru


Installation
-----------

Set db settings in app/config.php

Set 777 to:
/cache
/www/media
/www/media/preview

run
php doctrine.php orm:generate-proxies
php doctrine.php orm:schema-tool:create

Enjoy