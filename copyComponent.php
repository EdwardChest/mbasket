<?php
exec('cp -r ../../../local/components/sotbit/multibasket.multibasket/ install/components/');
exec('rm -r install/components/multibasket.multibasket/src/node_modules/ install/components/multibasket.multibasket/src/package-lock.json');
$pathFrom = '../../../local/templates/.default/components/bitrix/sale.basket.basket/multibasket';
$pathTo = 'install/templates/.default/components/bitrix/sale.basket.basket';
exec("cp -r {$pathFrom} {$pathTo}");
