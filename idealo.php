<?php

	require_once("simple_html_dom.php");

	function get_idealo($productname)
	{
	
		$productname = str_replace(' ','+',$productname);
		$idealourl = 'http://www.idealo.de/preisvergleich/MainSearchProductCategory.html?q='.$productname;
		$idealohtml =  file_get_html($idealourl);
		$prices = $idealohtml -> find('span[class="price bold nobr block fs-18"]');
		$names = $idealohtml -> find('a[class="offer-title link-2 webtrekk"]');
		
		//echo $prices[0];
		// echo $names[0];
		
		$arr = [
					0 => $names,
					1 => $prices,
				];
		

		return $arr;
		}


	//get_idealo("Jura ENA Micro Easy Kaffee Vollautomat schwarz");
?>