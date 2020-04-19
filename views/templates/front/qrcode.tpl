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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*  
*}

{extends "$layout"}
{block name="content"}
  <section>
        <h2>Digicash by Payconiq</h2>
	<p>
		{l s='Did you know that with the DIGICASH App, you can pay your order in seconds with your smartphone? It\'s easy and secure!' mod='paymentdigicash'}		
	</p>
	
	<p>
		<ul>
			<li>{l s='Scan the QrCode with your DIGICASH App!' mod='paymentdigicash'}</li>		
			<li>{l s='After you scanned the QrCode wait until the page will refresh automatically!' mod='paymentdigicash'}</li>
		</ul>		
	</p>
	
	<p class="text-center">
		<h3>{l s='Payment details' mod='paymentdigicash'}:</h3>
		<img src="data:image/jpeg;base64,{$qrCodeBase64}" /><br>
		{l s='Description' mod='paymentdigicash'}: <strong>{$transactionReference}</strong><br>
	    {l s='Amount' mod='paymentdigicash'}: <strong>{number_format($orderTotal, 2)} €</strong>
	</p>

	<p>
		<button id="btnCancelPayment" onclick="history.back()" class="btn btn-secondary center-block">
			{l s='Do not pay with Digicash, return to your order' mod='paymentdigicash'}	
		</button>
	</p>
  </section>
  <form action="{$validationURL}" id="validationForm">
  </form>


<script>
function timerFunc() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {

		if (xhr.readyState === 4) {

			var json = JSON.parse(xhr.responseText);
			if (json.status == 'ok') {
				
				clearTimeout(timer);
				
				// deactivate cancel button				
				document.getElementById("btnCancelPayment").disabled = true;
				                
				// redirect to confirmation
				document.getElementById("validationForm").submit();				
			}
		}
	};

	xhr.open('GET', '{$transactionStatusURL}');
	xhr.send();
}

var timer = setInterval(timerFunc, 2500);
</script>
{/block}
