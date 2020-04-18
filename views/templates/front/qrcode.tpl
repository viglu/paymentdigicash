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
	<p>Saviez-vous qu'avec l'App DIGICASH, vous pouvez payer votre commande sur bitzerella.com en 6 seconde sans saisir vos coordonnées bancaires? C'est plus simple et c'est sécurisé! </p>
	
	<p class="text-center">
		<h3>Détails de paiement</h3>
		<img src="data:image/jpeg;base64,{$qrCodeBase64}" /><br>
		Déscription: <strong>{$transactionReference}</strong><br>
	        Montant: <strong>{number_format($orderTotal, 2)} €</strong>
	</p>

	<p><button id="btnCancelPayment" onclick="history.back()" class="btn btn-secondary center-block">Annuler le paiement et retour à votre commande</button><p>
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
