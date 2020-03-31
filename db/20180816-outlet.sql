ALTER TABLE `outlet` ADD `AllowNewAndEdit` TINYINT UNSIGNED NOT NULL AFTER `AllowPurchase`;
ALTER TABLE `outlet` CHANGE `AllowNewAndEdit` `AllowPurchaseNewAndEdit` TINYINT(3) UNSIGNED NOT NULL;
