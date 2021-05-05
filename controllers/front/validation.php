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
class PaymentDigicashValidationModuleFrontController extends ModuleFrontController
{

    private const DEBUG_MODE = false;

    /**
     *
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (self::DEBUG_MODE) {
            openlog("paymentdigicash", LOG_PID | LOG_PERROR, LOG_LOCAL0);
        }

        session_start();

        $cart = $this->context->cart;

        $this->module->currentOrder = $_SESSION['PAYMENTDIGICASH_ORDERREF'];

        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . $_SESSION['PAYMENTDIGICASH_ORDERREF'];
        $validateLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');
        $confirmLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'CONFIRM');
        if (! empty($validateLog) && ! empty($confirmLog) /* && empty($cart->id) */) {
            $orderCart = new Cart($confirmLog->getCartId());
            $orderCustomer = new Customer($orderCart->id_customer);
            $redirect_url = 'index.php?controller=order-confirmation&id_cart=' . $confirmLog->getCartId() . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $orderCustomer->secure_key;

            if (self::DEBUG_MODE) {
                syslog(LOG_WARNING, "Order already confirmed redirect to order-confirmation = " . $redirect_url);
                closelog();
            }

            Tools::redirect($redirect_url);
            exit();
        }

        if (self::DEBUG_MODE) {
            syslog(LOG_WARNING, "cart.id = " . $cart->id);
            syslog(LOG_WARNING, "this.module.currentOrder = " . $this->module->currentOrder);
            syslog(LOG_WARNING, "_SESSION.PAYMENTDIGICASH_ORDERREF = " . $_SESSION['PAYMENTDIGICASH_ORDERREF']);
        }

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || ! $this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'paymentdigicash') {
                $authorized = true;
                break;
            }
        }
        if (! $authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        // check if Digicash VALIDATE and CONFIRM was received
        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . $_SESSION['PAYMENTDIGICASH_ORDERREF'];
        $validateLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');
        $confirmLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'CONFIRM');
        if (empty($validateLog) || empty($validateLog->getTransactionReference()) || empty($confirmLog) || empty($confirmLog->getTransactionReference())) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);
        if (! Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        if (Order::getByReference($this->module->currentOrder)->count() == 0) {
            $this->context->customer = new Customer($cart->id_customer);
            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
            $extra_vars = [
                'transaction_id' => $confirmLog->getTransactionId()
            ];
            $this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $extra_vars, (int) $cart->id_currency, false, $this->context->customer->secure_key, null, $_SESSION['PAYMENTDIGICASH_ORDERREF']);
        }

        $url = 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key;

        if (self::DEBUG_MODE) {
            syslog(LOG_WARNING, "redirect to order-confirmation = " . $url);
            closelog();
        }

        Tools::redirect($url);
    }
}
