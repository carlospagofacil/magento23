
Módulo de pago con PagoFácil para Magento 2

Este módulo permite realizar pagos a través de la plataforma PagoFácil.

How to install this extension?

Under your root folder, run the following command lines:

composer require php-cuong/magento2-currency-symbol-position
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:di:compile
php bin/magento cache:flush

How to see the results

1.- Go to the backend

On the Magento Admin Panel, you navigate to the Stores → Configuration → Sales → Payment Methods → PagoFácil Direct

2.- Go to the storefront

Check the Pagofacil Direct Payment Method
