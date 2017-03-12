<?php

const SEPARATOR = '|';
const BACK_CHAR = "\n";
const BLUE = "\033[1;34m";
const GREEN = "\033[1;32m";
const DARK_MAGENTA = "\033[45;37m";
const COLOR_STOP = "\033[0m";

function blue($string) {
	return BLUE.$string.COLOR_STOP;
}
function blueLn($string) {
	return blue($string).BACK_CHAR;
}
function green($string) {
	return GREEN.$string.COLOR_STOP;
}
function greenLn($string) {
	return green($string).BACK_CHAR;
}
function purple($string) {
	return DARK_MAGENTA.$string.COLOR_STOP;
}
function purpleLn($string) {
	return purple($string).BACK_CHAR;
}

const TIMER = 20;


class NainwakLoger {

	protected $curl;

	protected $login;
	protected $pass;

	public function __construct($login, $pass) {
		$this->login = $login;
		$this->pass = $pass;
	}

	protected function connect() {

		$this->curl = curl_init();

		$data = array(
			'login' => $this->login,
			'password' => $this->pass
		);

		$options = array(
			CURLOPT_URL => 'http://nainwak.com/index.php',
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HEADER => TRUE,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $data,
		);

		curl_setopt_array($this->curl, $options);

		$output = curl_exec($this->curl);

		if(FALSE and $output === FALSE) {
			throw new Exception('Erreur curl : '.curl_error($this->curl));
		} else {

			list($header, $response) = $this->getHeaderResponse($output);
			preg_match('/IDS=(.*)&auto/', $response, $matches);
			$session_identifier = $matches[1];

			return $session_identifier;
		}
	}

	protected function exec(array &$output) {
		$result = curl_exec($this->curl);
		if($result === FALSE) {
			throw new Exception('Erreur curl : '.curl_error($this->curl));
			return FALSE;
		}
		$output = $this->getHeaderResponse($result);
		return TRUE;
	}

	protected function close() {
		curl_close($this->curl);
	}

	protected function getHeaderResponse($output) {
		$headerLength = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		return array(substr($output, 0, $headerLength), substr($output, $headerLength));
	}

	public function getSessionIdentifier() {
		$id = $this->connect();
		$this->close();
		return $id;
	}

}

class NainwakRetriever {

	private $sessionIdentifier;

	private $detection = NULL;
	private $inventaire = NULL;
	private $events = NULL;

	public function __construct($id) {
		$this->sessionIdentifier = $id;
	}

	public function getFullDetection() {

		if($this->detection === NULL) {

			$formatedContent = array();

			foreach(file('http://nainwak.com/jeu/detect.php?IDS='.$this->sessionIdentifier) as $oLine) {
				if(
					//strpos($oLine, 'var ') !== FALSE or
					strpos($oLine, 'tabavat') !== FALSE or
					strpos($oLine, 'tabobjet') !== FALSE 
				) {
					$formatedContent[] = $oLine;
				}
			}

			$this->detection = $formatedContent;
		}

		return $this->detection;
	}

	public function getInventaire() {
		if($this->inventaire === NULL) {
			$formatedContent = array();
			foreach(file('http://nainwak.com/jeu/invent.php?IDS='.$this->sessionIdentifier) as $oLine) {
				if(strpos($oLine, 'mip(') !== FALSE) {
					$formatedContent[] = $oLine;
				}
			}
			$this->inventaire = $formatedContent;
		}
		return $this->inventaire;
	}

	/** Genius */
	public function autoPickUp() {

		foreach($this->getFullDetection() as $element) {
			if(strpos($element, 'tabobjet') !== FALSE) {

				$matches = array();
	
				if(preg_match('/\[([0-9]*), ".*", "(.*)", ([0-9]*),/', $element, $matches)) {
					$id = $matches[1];
					$name = $matches[2];
					$dist = (int)$matches[3];
	
					if($dist == 0) {
						echo greenLn("Autopickup -> $name");
						// do pick up with $id
						file('http://nainwak.com/jeu/transfert.php?IDS='.$this->sessionIdentifier.'&action=ramasser&idsol='.$id);
						sleep(1);
					}
				}
			}
		}
	}

	public function highlightDetectionObjects() {
		foreach($this->getFullDetection() as $element) {
			if(strpos($element, 'tabobjet') !== FALSE) {
				if(contain($element, array(
					'boomerang classique',
					'Naindiana',
					'donut',
					'cyclope',
					'loupe',
					'main',
					'satellite',
					'pigeon',
					'desert',
					'pompe',
					'lance banane',
					'lance requet',
					'infra',
					'cri de',
					'cocktail',
					'feu',
					'reaction',
					'verte',
					'nasa',
					'calto',
					'exca',
					'revolver'))
				) {
					$matches = array();
					preg_match('/\[([0-9]*), ".*", "(.*)", ([0-9]*),/', $element, $matches);
					$name = $matches[2];
					$distance = (int)$matches[3];
					echo purpleLn("$name ($distance)");
				}
			}
		}
	}


	public function highlightDust() {
		foreach($this->getInventaire() as $element) {

			$matches = array();
			if(preg_match('/mip\(([0-9]*), "([^"]*)", .*, "([0-9]*)"\);/', $element, $matches)) {

				$id = $matches[1];
				$name = $matches[2];
				$timer = (int)$matches[3];

				if($timer < (5 * 24 * 60 * 60)) { // 5 jours
					echo purpleLn("/!\ un (une) $name va tomber en poussière");
				}
			
			}
		}
	}

	public function getEvents() {

		if($this->events === NULL) {
			$formatedContent = array();

			foreach(file('http://nainwak.com/jeu/even.php?IDS='.$this->sessionIdentifier) as $oLine) {
				if(strpos($oLine, 'ev(') === 0) {
					$formatedContent[] = $oLine;
				}
			}
			$this->events = $formatedContent;
		}

		return $this->events;
	}

	// public function getChatLines() {

	// 	$chatContent = array();

	// 	foreach(file('http://nainwak.com/jeu/guilde.php?IDS='.$this->sessionIdentifier.'&act=nainxpress') as $oLine) {

	// 		if(strpos($oLine, 'mep(16,\'<p') === 0) {


	// 			$strHtml = substr($oLine, 8);
	// 			$strHtml = substr($strHtml, 0, strlen($strHtml) - 4);
	// 			$html = str_get_html($strHtml);

	// 			foreach($html->find('p') as $p) {

	// 				$chunk = 0;

	// 				// debug
	// 				echo $p;
	// 				echo "\n";
	// 				// debug
	// 				// <span style="color: #0370C3">31/07/2014 11h48&nbsp;<b>nainspawn</b>&nbsp;:</span>

	// 				foreach($p->find('text') as $texte) {

	// 					$line = '';
	// 					// $currentMin = (int)date('i');
	// 					// $currentHour = date('d/m/Y H');

	// 					// Strip content
	// 					$texte = str_replace('&nbsp;', '', $texte);
	// 					$texte = str_replace('&#039;', '\'', $texte);
						
	// 					if($chunk === 0) {
	// 						$hour = $texte;
	// 						$messageMin = substr($hour, -2);

	// 					} else if($chunk === 1) {
	// 						$login = substr($texte, 0, 13);
	// 						$login = str_pad($login, 13);

	// 					} else if($chunk === 3) {
	// 						$message = $texte;

	// 						$new = FALSE;
	// 						if(
	// 							FALSE
	// 							// strpos($hour, $currentHour) !== FALSE
	// 							// and $messageMin >= ($currentMin - TIMER)

	// 						) {
	// 							$new = TRUE;
	// 						}

	// 						$line .= BACK_CHAR;
	// 						if($new) {
	// 							$line .= GREEN.substr($hour, 11).COLOR_STOP.SEPARATOR;
	// 							$line .= GREEN.$login.COLOR_STOP.SEPARATOR;
	// 							$line .= DARK_MAGENTA.$message.COLOR_STOP;
	// 						} else {
	// 							$line .= BLUE.substr($hour, 11).COLOR_STOP.SEPARATOR;
	// 							$line .= BLUE.$login.COLOR_STOP.SEPARATOR;
	// 							$line .= $message;
	// 						}
	// 					}

	// 					$chunk++;
	// 					$chunk %= 4;

	// 					if(!empty($line)) {
	// 						$chatContent[] = $line;
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}

	// 	$string = '';
	// 	foreach(array_reverse($chatContent) as $chatLine) {
	// 		$string .= $chatLine;
	// 	}

	// 	return $string;
	// }

}

function contain($haystack, $arrayneedles) {
	foreach ($arrayneedles as $needle) {
		if(stripos($haystack, $needle) !== FALSE) {
			//echo 'matching needle is '.$needle."\n";
			return TRUE;
		}
	}
	return FALSE;
}

