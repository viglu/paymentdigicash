
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
require_once dirname(__FILE__) . '/../../classes/DigicashConst.php';

/**
 *
 * @since 1.0.0
 */
class PaymentDigicashDesktopModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();

        session_start();

        $transactionReference = strval(Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX)) . ' ' . $_SESSION['PAYMENTDIGICASH_ORDERREF'];

        $initLog = DigicashOperationLog::getLogByRefAndOp($transactionReference, 'INIT');

        $amount = strval(intval($initLog->getAmount() * 100));

        $urlAlias = Configuration::get(DigicashConst::URL_ALIAS);
        $merchantId = Configuration::get(DigicashConst::MERCHANT_ID);

        $qrCodeImageURL = 'https://pos.digica.sh/qrcode/generator?merchantId=' . $merchantId . '&amount=' . $amount . '&transactionReference=' . urlencode($transactionReference);
        if (! empty($urlAlias)) {
            $qrCodeImageURL .= '&urlAlias=' . $urlAlias;
        }

        $qrCodeBase64 = base64_encode(file_get_contents($qrCodeImageURL));
        $transactionStatusURL = $this->context->link->getModuleLink('paymentdigicash', 'transactionstatus', array(), Tools::usingSecureMode());
        $validationURL = $this->context->link->getModuleLink('paymentdigicash', 'validation', array(), Tools::usingSecureMode());

        $this->context->smarty->assign([
            'qrCodeBase64' => $qrCodeBase64,
            'transactionReference' => $transactionReference,
            'orderTotal' => $initLog->getAmount(),
            'transactionStatusURL' => $transactionStatusURL,
            'validationURL' => $validationURL
        ]);

        $this->setTemplate('module:paymentdigicash/views/templates/front/desktop.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
