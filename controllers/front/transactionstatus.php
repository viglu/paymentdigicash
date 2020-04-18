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

/**
 *
 * @since 1.0.0
 */
class PaymentDigicashTransactionstatusModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json');

        $result = array();
        $result['status'] = 'ko';
        $result['message'] = 'Internal error';

        // rebuild transactionReference PREFIX + CART ID
        $cart = $this->context->cart;
        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . strval($cart->id);

        // check if transaction is already validated
        $log = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');
        if (empty($log) || empty($log->getTransactionReference())) {
            $result['status'] = 'ko';
            $result['message'] = 'Transaction not validated yet';
        } else {
            $result['status'] = 'ok';
            $result['message'] = 'Transaction validated';
        }

        echo json_encode($result);
        exit();
    }
}
