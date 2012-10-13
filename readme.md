chatwall
========

The source code is distributed under New BSD licence.

Demo: http://chatwall.dev8.ru


Installation
-----------

1. Set db settings in `app/config.php`
2. Set 777 to: `/cache`, `/www/media`, `/www/media/preview`
3. Run `php doctrine.php orm:generate-proxies` and `php doctrine.php orm:schema-tool:create`
4.Enjoy