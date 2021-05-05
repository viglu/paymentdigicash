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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Digicash object model
 */
require_once dirname(__FILE__) . '/classes/DigicashOperationLog.php';

require_once dirname(__FILE__) . '/classes/DigicashConst.php';

require_once (dirname(__FILE__) . '/install.php');

if (! defined('_PS_VERSION_')) {
    exit();
}

class paymentdigicash extends PaymentModule
{

    protected $_html = '';

    protected $_postErrors = array();

    /**
     * List of objectModel used in this Module
     *
     * @var array
     */
    public $objectModels = array(
        'DigicashOperationLog'
    );

    public function __construct()
    {
        $this->name = 'paymentdigicash';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );
        $this->author = 'Luc Vigato';
        $this->controllers = array(
            'validation'
        );
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Digicash by Payconiq');
        $this->description = $this->l('Digicash is only available in Luxembourg');

        if (! count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        $digicash_install = new DigicashInstall();
        $digicash_install->createTables();

        return parent::install() && $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        if (! Configuration::deleteByName(DigicashConst::MERCHANT_ID) || ! Configuration::deleteByName(DigicashConst::URL_ALIAS) || ! Configuration::deleteByName(DigicashConst::DESCRIPTION_STATEMENT_PREFIX) || ! parent::uninstall())
            return false;
        return true;
    }

    /**
     * Load the configuration form
     *
     * @return string HTML
     */
    public function getContent()
    {
        $this->makeModuleTrusted();

        $output = '';
        $output .= $this->postProcess();

        $this->moduleUrl = Context::getContext()->link->getAdminLink('AdminModules', false) . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&' . http_build_query(array(
            'configure' => $this->name
        ));

        $this->baseUrl = $this->context->link->getAdminLink('AdminModules', true) . '&' . http_build_query(array(
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'module_name' => $this->name
        ));

        return $output . $this->renderSettingsPage();
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submitOptionsconfiguration')) {
            Configuration::updateValue(DigicashConst::MERCHANT_ID, Tools::getValue(DigicashConst::MERCHANT_ID));
            Configuration::updateValue(DigicashConst::URL_ALIAS, Tools::getValue(DigicashConst::URL_ALIAS));
            Configuration::updateValue(DigicashConst::DESCRIPTION_STATEMENT_PREFIX, Tools::getValue(DigicashConst::DESCRIPTION_STATEMENT_PREFIX));
        }
    }

    /**
     * Render the general settings page
     *
     * @return string HTML
     * @throws Exception
     * @throws SmartyException
     */
    protected function renderSettingsPage()
    {
        $notifyURL = $this->context->link->getModuleLink($this->name, 'callback', array(), Tools::usingSecureMode());

        $output = '';
        $output .= $this->renderGeneralOptions();
        $output .= 'Notify URL: <strong>' . $notifyURL . '</strong>';

        return $output;
    }

    /**
     * Render the General options form
     *
     * @return string HTML
     */
    protected function renderGeneralOptions()
    {
        $helper = new HelperOptions();
        $helper->id = 1;
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->table = 'configuration';
        $helper->show_toolbar = false;
        // $helper->submit_action = 'btnSubmit';

        return $helper->generateOptions(array_merge($this->getGeneralOptions()));
    }

    /**
     * Get available general options
     *
     * @return array General options
     */
    protected function getGeneralOptions()
    {
        return array(
            'api' => array(
                'title' => $this->l('API Settings'),
                'icon' => 'icon-server',
                'fields' => array(
                    DigicashConst::MERCHANT_ID => array(
                        'title' => $this->l('Merchant Id'),
                        'type' => 'text',
                        'name' => DigicashConst::MERCHANT_ID,
                        'value' => Configuration::get(DigicashConst::MERCHANT_ID),
                        'validation' => 'isString',
                        'cast' => 'strval',
                        'size' => 64
                    ),
                    DigicashConst::URL_ALIAS => array(
                        'title' => $this->l('URL Alias'),
                        'type' => 'text',
                        'name' => DigicashConst::URL_ALIAS,
                        'value' => Configuration::get(DigicashConst::URL_ALIAS),
                        'validation' => 'isString',
                        'cast' => 'strval',
                        'size' => 64
                    ),
                    DigicashConst::DESCRIPTION_STATEMENT_PREFIX => array(
                        'title' => $this->l('Statement prefix'),
                        'type' => 'text',
                        'name' => DigicashConst::DESCRIPTION_STATEMENT_PREFIX,
                        'value' => Configuration::get(DigicashConst::DESCRIPTION_STATEMENT_PREFIX),
                        'validation' => 'isString',
                        'cast' => 'strval',
                        'size' => 64
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button'
                )
            )
        );
    }

    /**
     * Make this module trusted and add it to the active payments list
     *
     * @return void
     */
    protected function makeModuleTrusted()
    {
        if (version_compare(_PS_VERSION_, '1.6.0.7', '<') || ! @filemtime(_PS_ROOT_DIR_ . Module::CACHE_FILE_TRUSTED_MODULES_LIST) || ! @filemtime(_PS_ROOT_DIR_ . Module::CACHE_FILE_UNTRUSTED_MODULES_LIST) || ! @filemtime(_PS_ROOT_DIR_ . Module::CACHE_FILE_TAB_MODULES_LIST) || ! class_exists('SimpleXMLElement')) {
            return;
        }
        // Remove untrusted
        $untrustedXml = @simplexml_load_file(_PS_ROOT_DIR_ . Module::CACHE_FILE_UNTRUSTED_MODULES_LIST);
        if (! is_object($untrustedXml)) {
            return;
        }
        $module = $untrustedXml->xpath('//module[@name="' . $this->name . '"]');
        if (empty($module)) {
            // Module list has not been refreshed, return
            return;
        }
        unset($module[0][0]);
        @$untrustedXml->saveXML(_PS_ROOT_DIR_ . Module::CACHE_FILE_UNTRUSTED_MODULES_LIST);

        // Add untrusted
        $trustedXml = @simplexml_load_file(_PS_ROOT_DIR_ . Module::CACHE_FILE_TRUSTED_MODULES_LIST);
        if (! is_object($trustedXml)) {
            return;
        }
        /** @var SimpleXMLElement $modules */
        @$modules = $trustedXml->xpath('//modules');
        if (! empty($modules)) {
            $modules = $modules[0];
        }
        if (empty($modules)) {
            return;
        }
        /** @var SimpleXMLElement $module */
        $module = $modules->addChild('module');
        $module->addAttribute('name', $this->name);
        @$trustedXml->saveXML(_PS_ROOT_DIR_ . Module::CACHE_FILE_TRUSTED_MODULES_LIST);

        // Add to active payments list
        $modulesTabXml = @simplexml_load_file(_PS_ROOT_DIR_ . Module::CACHE_FILE_TAB_MODULES_LIST);
        if (! is_object($modulesTabXml)) {
            return;
        }

        $moduleFound = $modulesTabXml->xpath('//tab[@class_name="AdminPayment"]/module[@name="' . $this->name . '"]');
        if (! empty($moduleFound)) {
            return;
        }

        // Find highest position
        /** @var array $modules */
        $modules = $modulesTabXml->xpath('//tab[@class_name="AdminPayment"]/module');
        $highestPosition = 0;
        foreach ($modules as $module) {
            /** @var SimpleXMLElement $module */
            foreach ($module->attributes() as $name => $attribute) {
                if ($name == 'position' && $attribute[0] > $highestPosition) {
                    $highestPosition = (int) $attribute[0];
                }
            }
        }
        $highestPosition ++;
        /** @var SimpleXMLElement $modules */
        @$modules = $modulesTabXml->xpath('//tab[@class_name="AdminPayment"]');
        if (! empty($modules)) {
            $modules = $modules[0];
        }
        if (empty($modules)) {
            return;
        }
        $module = $modules->addChild('module');
        $module->addAttribute('name', $this->name);
        $module->addAttribute('position', $highestPosition);
        @$modulesTabXml->saveXML(_PS_ROOT_DIR_ . Module::CACHE_FILE_TAB_MODULES_LIST);
    }

    public function hookPaymentOptions($params)
    {
        if (! $this->active) {
            return;
        }

        if (! $this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getIframePaymentOption()
        ];

        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getIframePaymentOption()
    {
        $iframeOption = new PaymentOption();
        $iframeOption->setCallToActionText($this->l(''))
            ->setAction($this->context->link->getModuleLink($this->name, 'init', array(), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:paymentdigicash/views/templates/front/payment_infos.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/payment.png'));

        return $iframeOption;
    }
}
