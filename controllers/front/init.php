
<?php

/*
 * 2021 Luc Vigato
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
require_once dirname(__FILE__) . '/../../classes/DigicashConst.php';

/**
 *
 * @since 1.0.0
 */
class PaymentDigicashInitModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();

        session_start();

        do {
            $reference = Order::generateReference();
        } while (Order::getByReference($reference)->count());
        $_SESSION['PAYMENTDIGICASH_ORDERREF'] = $reference;

        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . $_SESSION['PAYMENTDIGICASH_ORDERREF'];
        $cart = $this->context->cart;

        $initLog = new DigicashOperationLog();
        $initLog->setCartId($cart->id);
        $initLog->setTransactionReference($transactionReference);
        $initLog->setOperation('INIT');
        $initLog->setAmount($cart->getOrderTotal());
        $initLog->setDateAdd(date("Y-m-d H:i:s"));
        $initLog->add();

        $pageToView = $this->context->isMobile() ? 'mobile' : 'desktop';

        header('Location: ' . $this->context->link->getModuleLink('paymentdigicash', $pageToView, array(), Tools::usingSecureMode()));
        exit();
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
