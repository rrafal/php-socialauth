============================================================
License 
============================================================

Distributed under The MIT License. See LICENSE.txt


============================================================
Installation
============================================================

The installation was tested on Ubuntu 12.08

Install the following packages:
a) http://www.php.net/manual/en/book.oauth.php

pecl install oauth
echo "extension=oauth.so" > /etc/php5/conf.d/oauth.ini
apache2ctl restart

b) https://github.com/openid/php-openid
 
apt-get install php-openid



See "example" to see how to use this library. Copy 
example/config-example.php to example/config.php and edit 
as needed.


