<?php

include_once(dirname(__FILE__ ).'/client.php');
include_once(dirname(__FILE__ ).'/lib/simple_html_dom.php');

$sessionIdentifier = (new NainwakLoger('evilduckling', ''))->getSessionIdentifier();
$retriever = new NainwakRetriever($sessionIdentifier);

$retriever->autoPickUp();
$retriever->highlightDust();
$retriever->highlightDetectionObjects();

echo BACK_CHAR.blue("http://nainwak.com/jeu/index.php?IDS=".$sessionIdentifier).BACK_CHAR.BACK_CHAR;
