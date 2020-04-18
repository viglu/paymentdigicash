<?php
/*
 * 2020 Luc Vigato
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Luc Vigato <luc.vigato@gmail.com>
 * @copyright 2020 Luc Vigato
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
if (! defined('_PS_VERSION_'))
    exit();

class DigicashInstall
{

    public function createTables()
    {
        if (! Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ps_digicash_operation_log(
			  id int unsigned NOT NULL AUTO_INCREMENT,
			  id_cart int unsigned NOT NULL,
		          id_order int unsigned,
			  transaction_reference varchar(255) NOT NULL,
			  operation varchar(12) NOT NULL,
			  transaction_id varchar(255),
			  amount numeric(10,4) NOT NULL,
			  user_id varchar(255),
			  date_add datetime NOT NULL,
			PRIMARY KEY(id),
			INDEX (transaction_reference, operation),
			INDEX (id_cart),
			INDEX (id_order)
			) ENGINE =' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
		'))
            return false;
    }
}
