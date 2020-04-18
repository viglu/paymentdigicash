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

    /**
     *
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
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

        // check if Digicash VALIDATE and CONFIRM was received
        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . strval($cart->id);
        $validateLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');
        $confirmLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'VALIDATE');
        if (empty($validateLog) || empty($validateLog->getTransactionReference()) || empty($confirmLog) || empty($confirmLog->getTransactionReference())) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (! $authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        /*
         * $this->context->smarty->assign([
         * 'params' => $_REQUEST
         * ]);
         */

        // $this->setTemplate('payment_return.tpl');
        // $this->setTemplate('module:paymentdigicash/views/templates/front/payment_return.tpl');

        $customer = new Customer($cart->id_customer);
        if (! Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $mailVars = NULL;
        // $mailVars = array(
        // '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
        // '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
        // '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
        // );

        $this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $mailVars, (int) $currency->id, false, $customer->secure_key);
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
    }
}
