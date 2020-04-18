{*
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
*  @author Luc Vigato <luc.vigato@gmail.com>
*  @copyright  2020 Luc Vigato
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0) 
*}

<img src="{$qrcode}" />

<form action="{$action}" id="payment-form">

  <p>
    <label>{l s='Card number'}</label>
    <input type="text" size="20" autocomplete="off" name="card-number">
  </p>

  <p>
    <label>{l s='Firstname'}</label>
    <input type="text" autocomplete="off" name="firstname">
  </p>

  <p>
    <label>{l s='Lastname'}</label>
    <input type="text" autocomplete="off" name="lastname">
  </p>

  <p>
    <label>{l s='CVC'}</label>
    <input type="text" size="4" autocomplete="off" name="card-cvc">
  </p>

  <p>
    <label>{l s='Expiration (MM/AAAA)'}</label>
    <select id="month" name="card-expiry-month">
      {foreach from=$months item=month}
        <option value="{$month}">{$month}</option>
      {/foreach}
    </select>
    <span> / </span>
    <select id="year" name="card-expiry-year">
      {foreach from=$years item=year}
        <option value="{$year}">{$year}</option>
      {/foreach}
    </select>
  </p>
</form>