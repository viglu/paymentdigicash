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
class DigicashOperationLog extends ObjectModel
{

    /** @var int */
    public $id;

    /** @var int */
    public $id_cart;

    /** @var int */
    public $id_order;

    /** @var string */
    public $operation;

    /** @var float */
    public $amount;

    /** @var string */
    public $transaction_reference;

    /** @var string */
    public $transaction_id;

    /** @var string */
    public $user_id;

    /** @var date */
    public $date_add;

    /**
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'digicash_operation_log',
        'primary' => 'id',
        'fields' => array(
            'id' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isInt',
                'size' => 10
            ),
            'id_cart' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isInt',
                'size' => 10
            ),
            'id_order' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isInt',
                'size' => 10
            ),
            'operation' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size' => 12
            ),
            'amount' => array(
                'type' => ObjectModel::TYPE_FLOAT,
                'validate' => 'isFloat',
                'size' => 10,
                'scale' => 2
            ),
            'transaction_reference' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255
            ),
            'transaction_id' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255
            ),
            'user_id' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255
            ),
            'date_add' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDate'
            )
        )
    );

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCartId($id_cart)
    {
        $this->id_cart = $id_cart;
    }

    public function getCartId()
    {
        return $this->id_cart;
    }

    public function setOrderId($id_order)
    {
        $this->id_order = $id_order;
    }

    public function getOrderId()
    {
        return $this->id_order;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setTransactionReference($transaction_reference)
    {
        $this->transaction_reference = $transaction_reference;
    }

    public function getTransactionReference()
    {
        return $this->transaction_reference;
    }

    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setDateAdd($date_add)
    {
        $this->date_add = $date_add;
    }

    public function getDateAdd()
    {
        return $this->date_add;
    }

    public static function getLogByRefAndOp($transaction_reference, $operation)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(bqSQL(static::$definition['table']));
        $query->where('transaction_reference = \'' . pSQL($transaction_reference) . '\' and operation = \'' . pSQL($operation) . '\'');
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query->build());

        $result = new static();
        if (is_array($row)) {
            $result->hydrate($row);
        }

        return $result;
    }
}
