torban
======

Code useful for detecting systematic attacks on Tor exit nodes used to connect to the Bitcoin network.

By Kristov Atlas 2014
Open Bitcoin Privacy Project

See the live site at:
http://www.openbitcoinprivacyproject.org/torban/www

## Usage

### Fetching new data and updating database
```
touch common/torbandb.txt
php db-update/cron-update-db.php
```
### Displaying data from database in HTML format

```
include_once('IntersectionStatusHTMLWriter.php');
$writer = new IntersectionStatusHTMLWriter();
$writer->write();
```

or view www/index.php

## More Info

Inspired by:
http://arxiv.org/abs/1410.6079 "Bitcoin over Tor isn't a good idea"

"It turns out that by exploiting a Bitcoin built-in reputation based DoS protection an attacker is able to force specific Bitcoin peers to ban Tor Exit nodes of her choice. Combining it with some peculiarities of how Tor handles data streams a stealthy and low-resource attacker with just 1-3% of overall Tor Exit bandwidth capacity and 1000-1500 cheap lightweight Bitcoin peers (for example, a small Botnet) can force all Bitcoin Tor traffic to go either through her Exit nodes or through her peers. This opens numerous attack vectors. First it simplifies a traffic correlation attack since the attacker controls one end of the communication. Second, the attacker can can glue together different Bitcoin addresses (pseudonyms) of the same user. Third, it opens possibilities of double spending attacks for the mobile SPV clients, those which it was supposed to protect from such attacks. The estimated cost of the attack is below 2500 USD per month."

Data sources:<br/>
[https://getaddr.bitnodes.io/api/](https://getaddr.bitnodes.io/api/)<br/>
[http://torstatus.blutmagie.de/](http://torstatus.blutmagie.de/)

A Bitcoin node is considered "connected" merely if it is listed by Bitnodes.io. The API permits queries for additional information about the state of a node -- this may be added in a future commit.