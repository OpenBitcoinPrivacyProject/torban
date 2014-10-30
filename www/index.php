<?php

include_once('IntersectionStatusHTMLWriter.php');
?>

<html>
<header>
	<title>TorBan - Stats on Tor exit nodes used to connect to the Bitcoin network</title>
</header>
<body>
	<p>
		A historical record of Tor exit nodes used to connect to the Bitcoin network. Updated every 5 minutes.
	</p>
	<p>
		<?
		$writer = new IntersectionStatusHTMLWriter();
		$writer->write();
		?>
	</p>
	<p>
		Inspired by:<br/>
		<a href="http://arxiv.org/abs/1410.6079 ">"Bitcoin over Tor isn't a good idea"</a>
	</p>
	<p>
		"It turns out that by exploiting a Bitcoin built-in reputation based DoS protection an attacker is able to force specific Bitcoin peers to ban Tor Exit nodes of her choice. Combining it with some peculiarities of how Tor handles data streams a stealthy and low-resource attacker with just 1-3% of overall Tor Exit bandwidth capacity and 1000-1500 cheap lightweight Bitcoin peers (for example, a small Botnet) can force all Bitcoin Tor traffic to go either through her Exit nodes or through her peers. This opens numerous attack vectors. First it simplifies a traffic correlation attack since the attacker controls one end of the communication. Second, the attacker can can glue together different Bitcoin addresses (pseudonyms) of the same user. Third, it opens possibilities of double spending attacks for the mobile SPV clients, those which it was supposed to protect from such attacks. The estimated cost of the attack is below 2500 USD per month."
	</p>
	<p>
		Data sources:<br/>
		<a href="https://getaddr.bitnodes.io/api/">BitNodes.io</a><br/>
		<a href="http://torstatus.blutmagie.de/">TorStatus</a>
	</p>
	<p>
		<a href="https://github.com/OpenBitcoinPrivacyProject/torban">GitHub source</a> for this page.
	</p>
	
</body>