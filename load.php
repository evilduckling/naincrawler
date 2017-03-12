<?php

include_once(dirname(__FILE__ ).'lib/simple_html_dom.php');

session_start();

$egLine = file('http://nainwak.com/jeu/guilde.php?IDS='.$_SESSION['sessionIdentifier'].'&act=nainxpress');

foreach($egLine as $eLine) {

	if(strpos($eLine, 'mep(16,\'<p') === 0) {

		$strHtml = substr($eLine, 8);
		$strHtml = substr($strHtml, 0, strlen($strHtml) - 4);

		$html = str_get_html($strHtml);

		echo '<table cellpadding="0" cellspacing="0">';

		foreach($html->find('p') as $p) {

			$chunk = 0;
			foreach($p->find('text') as $texte) {

				$now = date('d/m/Y H\hi');
				$now .= '&nbsp;'; // hack

				if($chunk === 0) {
					$heure = $texte;
				} else if($chunk === 1) {
					$pseudo = $texte;
				} else if($chunk === 3) {
					$message = $texte;

					$style = '';
					if($now == ($heure.'')) {
						$style = 'color:red;';
					}

					echo '
						<tr>
							<td style="padding:3px; width:40px; '.$style.'">'.substr($heure, 11).'</td>
							<td style="padding:3px; width:140px; '.$style.'"><b>'.$pseudo.'</b></td>
							<td style="padding:3px; '.$style.'">'.$message.'</td>
						</tr>';

				}

				$chunk++;
				$chunk %= 4;
			}
		}

		echo '</table>';
	}
}

?>