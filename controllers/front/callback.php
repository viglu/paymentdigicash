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
class PaymentDigicashCallbackModuleFrontController extends ModuleFrontController
{

    private const DEBUG_MODE = false;

    public function initContent()
    {
        parent::initContent();
        if (self::DEBUG_MODE) {
            openlog("paymentdigicash", LOG_PID | LOG_PERROR, LOG_LOCAL0);
        }

        header('Content-Type: text/plain');
        $result = 'nok internal error';

        $operation = Tools::getValue('operation');
        $transactionReference = urldecode(Tools::getValue('transactionReference'));
        $transactionId = Tools::getValue('transactionId');
        $amount = Tools::getValue('amount');
        $userId = Tools::getValue('userId');

        if (empty($operation) || empty($transactionReference) || empty($transactionId) || empty($amount) || empty($userId)) {

            $result = 'nok Not all parameters set';
        } elseif ($operation == 'VALIDATE') {

            $initLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'INIT');
            if (empty($initLog) || empty($initLog->getTransactionReference())) {

                $result = 'nok Transaction not initialized';
            } elseif (($initLog->getTransactionReference() == $transactionReference) || (bccomp($initLog->getAmount(), $amountFloat, 4) == 0)) {

                $amountFloat = (int) $amount / 100;

                $validateLog = new DigicashOperationLog();
                $validateLog->setTransactionReference($transactionReference);
                $validateLog->setOperation($operation);
                $validateLog->setTransactionId($transactionId);
                $validateLog->setAmount($amountFloat);
                $validateLog->setUserId($userId);
                $validateLog->setDateAdd(date("Y-m-d H:i:s"));
                $validateLog->setCartId($initLog->getCartId());
                $validateLog->add();
                $result = 'ok';
            } else {
                $result = 'nok Transaction not confirmed';
            }
        } elseif ($operation == 'CONFIRM') {

            $initLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'INIT');
            $validateLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');

            if ((bccomp($initLog->getAmount(), $validateLog->getAmount(), 4) == 0) && ($validateLog->getTransactionId() == $transactionId)) {

                $amountFloat = (int) $amount / 100;
                $confirmLog = new DigicashOperationLog();
                $confirmLog->setTransactionReference($transactionReference);
                $confirmLog->setOperation($operation);
                $confirmLog->setTransactionId($transactionId);
                $confirmLog->setAmount($amountFloat);
                $confirmLog->setUserId($userId);
                $confirmLog->setDateAdd(date("Y-m-d H:i:s"));
                $confirmLog->setCartId($initLog->getCartId());
                $confirmLog->add();
                $this->validateOrder($confirmLog);
                $result = 'ok';
            } else {
                $result = 'nok CONFIRM requeset not correct';
            }
        } else {
            $result = 'nok Wrong request';
        }

        header('Content-Type: text/plain');
        echo $result;

        if (self::DEBUG_MODE) {
            syslog(LOG_WARNING, "callback result = " . $result);
            closelog();
        }
        exit();
    }

    // try to validate here; some customer to not come back to the site; so we have to validate here the order
    private function validateOrder($confirmLog)
    {
        $trans_ref = $confirmLog->getTransactionReference();
        $pos = strrpos($trans_ref, " ");
        $order_ref = trim(substr($trans_ref, $pos, strlen($trans_ref)));

        if (Order::getByReference($order_ref)->count() == 0) {
            if (self::DEBUG_MODE) {
                syslog(LOG_WARNING, "Order '$order_ref' will be validated now");
            }
            $this->context->cart = $cart = new Cart($confirmLog->getCartId());
            $this->context->customer = new Customer($cart->id_customer);
            $this->context->currency = new Currency($this->context->cart->id_currency);
            $this->module->currentOrder = $order_ref;
            $cart = $this->context->cart;
            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
            $extra_vars = [
                "transaction_id" => $confirmLog->getTransactionId()
            ];
            $this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $extra_vars, (int) $cart->id_currency, false, $this->context->customer->secure_key, null, $order_ref);
            
            $successLog = new DigicashOperationLog();
            $successLog->setTransactionReference($confirmLog->getTransactionReference());
            $successLog->setOperation('SUCCESS');
            $successLog->setTransactionId($confirmLog->getTransactionId());
            $successLog->setAmount($confirmLog->getAmount());
            $successLog->setUserId($confirmLog->getUserId());
            $successLog->setDateAdd(date("Y-m-d H:i:s"));
            $successLog->setCartId($confirmLog->getCartId());
            $successLog->add();
        }
    }
}
