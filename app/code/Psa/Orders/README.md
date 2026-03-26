# BalloonGroup_Orders

### Installation

```sh
$ php bin/magento module:enable BalloonGroup_Orders --clear-static-content
$ php bin/magento setup:upgrade
$ rm -rf var/di var/view_preprocessed var/cache generated/*
$ php bin/magento setup:static-content:deploy
```

### Information

- Process for orders
- Cron to cancel orders pending greather than 2 hours created
- Cron to set status Finalizado after 10 days in confirmado status
- Create button in order view to confirm shipment by distribute

### Author

[![N|Solid](https://www.balloon-group.com/es/images/BLN_isologo-horizontal_2-color-copy.svg)](https://www.www.balloon-group.com)
