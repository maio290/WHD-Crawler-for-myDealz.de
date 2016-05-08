<?php
/*
The MIT License (MIT)
Copyright (c) 2015 Mario-Luca Hoffmann for files: whd.php, whd_form.html & idealo.php
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
require_once("simple_html_dom.php");
require_once("idealo.php");
	if($_POST)
	{
	$asin = get_asin($_POST['url']);
	$urlm = prepare_url_mobile($asin);
	$urld = prepare_url_desktop($asin);
	$data_arr = get_data($urlm);
	echo '<!DOCTYPE html>
		<html lang="de">
		<head>
		<meta charset="utf-8">
		<title> mydealz.de - WHD Crawler</title>
		</head>
		<body>
		  ';
	echo '<textarea rows="20" cols="50">';
	echo '[url='.$urld.']';
	echo '[img]'.$data_arr[2].'[/img]';
	echo "\n";
	echo $data_arr[0];
	echo "\n\n";
	
	// Convert to number in order to compare it properly
	// 4 = names, 5 = prices (idealo)
	$tmp = str_replace("EUR ","",$data_arr[3]);
	$tmp = str_replace(",",".",$tmp);
	$cmp = floatval($tmp);
		if($cmp != 0)
		{
			echo 'Neupreis auf Amazon.de: '.number_format($cmp, 2, ',', '').' EUR';
			echo "\n";
		}
			else
			{
				echo 'Kein Neupreis auf Amazon.de vorhanden!';
				echo "\n";
			}
			
			if(count($data_arr[4]) == 1)
			{
				$name_idealo_arr  = $data_arr[4];
				$name_idealo = $name_idealo_arr[0];
				$price_idealo_arr = $data_arr[5];
				$price_idealo = strip_tags($price_idealo_arr[0]);
				$price_idealo = substr($price_idealo,0,strpos($price_idealo, "-"));
				$price_idealo = str_replace("€","EUR",$price_idealo);
				$name_idealo = substr($name_idealo, (strpos($name_idealo, ">",1)+1), -1);
				$name_idealo = strip_tags($name_idealo);
				$name_idealo = str_replace(20,"",$name_idealo);
				$name_idealo = preg_replace('/[^a-zA-Z0-9_ äöüß %\[\]\.\(\)%&-]/s', '', $name_idealo);
				echo ("Idealo (".$name_idealo."): " . $price_idealo);
				echo "\n";
			}
		foreach($data_arr[1] as &$value)
		{
			$value = strip_tags($value);
			$value = str_replace('EUR',"",$value);
			$value = str_replace('  ',' ',$value);
			$value .= " EUR";
			echo $value;
			echo "\n";
		};
		
	echo '[/url]';
	echo '</textarea>';
	
		if(count($data_arr[4]) > 1)
		{
			$name_idealo_arr  = $data_arr[4];
			$price_idealo_arr = $data_arr[5];
			echo '<br>';
			echo '<br>';
			echo '<br>';
			echo '<br>';
			echo '<textarea rows="20" cols="50">';
		
				for($i = 0; $i<count($data_arr[4]); $i++)
				{
				$name_idealo = $name_idealo_arr[$i];
				$price_idealo = strip_tags($price_idealo_arr[$i]);
				$price_idealo = substr($price_idealo,0,strpos($price_idealo, "-"));
				$price_idealo = str_replace("€","EUR",$price_idealo);
				$name_idealo = substr($name_idealo, (strpos($name_idealo, ">",1)+1), -1);
				$name_idealo = strip_tags($name_idealo);
				$name_idealo = str_replace(20,"",$name_idealo);
				$name_idealo = preg_replace('/[^a-zA-Z0-9 _äöüß%\[\]\.\(\)%&-]/s', '', $name_idealo);
				echo ("Idealo (".$name_idealo."): " . $price_idealo);
				echo "\n";
				}
			
		echo '</textarea>';
	
	
	}
		echo '</body>
		</html>';
	}
function prepare_url_mobile($item_id)
{
return 'http://www.amazon.de/gp/aw/d/'.$item_id.'/';
}
function prepare_url_desktop($item_id)
{
return 'http://www.amazon.de/dp/'.$item_id.'/?me=A8KICS1PHF7ZO';
}
function get_asin($url)
{
	$possASINprefixes = ["dp/", "gp/product/", "gp/aw/d/"];
	foreach ($possASINprefixes as $prefix) 
	{
		if(strpos($url, "/" . $prefix) != false)
		{
			return substr($url, strpos($url, $prefix) + strlen($prefix), 10);
		}
	}
function get_data($url) {
// Use with mobile links only
get_data:
$html = file_get_html($url);
$name = $html ->find('b[id="product-title"]');
$name = $name[0];
$name = strip_tags($name);
// Priceurl: http://www.amazon.de/gp/aw/ol/B00OBIXRSY?o=Used&op=1
// Common Url: http://www.amazon.de/gp/aw/d/B00OBIXRSY 
// MerchantID: A8KICS1PHF7ZO
// Fetch new price, if exists
$price = substr($html, strpos($html, "Preis:") + strlen("Preis:"), 23);
	if(strpos($price,"+") != false)
	{
	$price_1 = substr($html, strpos($html, "Preis:") + strlen("Preis:"), 23);
	$price_1 = preg_replace("/[^0-9,.]/", "", $price_1);
	$price_1 = str_replace(",",".",$price_1);
	$price_tmp = substr($html, strpos($html, "Preis:") + strlen("Preis:"), 37);
	$price_2 = substr($price_tmp, 18);
	$price_2 = preg_replace("/[^0-9,.]/", "", $price_2);
	$price_2 = str_replace(",",".",$price_2);
	$price = doubleval($price_1) + doubleval($price_2); 
	$price = "EUR ".$price;
	}
		else
		{
		$price = preg_replace("/[^0-9,.EUR ]/", "", $price);
		}
		
	
$priceurl = str_replace('/d','/ol',$url);
$priceurl .= '?o=Used&op=1';
refetch_outer:
$pricehtml = file_get_html($priceurl);
		if($pricehtml == false)
		{
				//goto refetch_outer;
		}
$pricehtml2 = $pricehtml ->find('a[href]');

$qty = $pricehtml ->find('a[name="Used"]');
$values = substr($qty[0], strpos($qty[0], "/") + strlen("/"), 23);


$entries_per_page = 10;

	if(intval($values) % 10  != 0)
	{
		$pages = intval((intval($values) / $entries_per_page )) + 1;
	}
	else
	{
		$pages = intval((intval($values) / $entries_per_page ));
	}


$offers = array();
$state_acceptable = false;
$state_good = false;
$state_very_good = false;
$state_as_new = false;

		for($i = 1; $i<=intval($pages); $i++)
		{
			if($i == 1)
			{$pricehtml2 = $pricehtml ->find('a[href]');}
			else
			{
			refetch_inner:
			$priceurl = str_replace('/d','/ol',$url);
			$priceurl .= '?o=Used&op='.$i;
			$pricehtml = file_get_html($priceurl);
			if($pricehtml == false)
			{
				$i = $i-1;
				goto refetch_inner;
			}
			$pricehtml2 = $pricehtml ->find('a[href]');
			}
				foreach($pricehtml2 as &$value)
				{
					if(strpos($value,'A8KICS1PHF7ZO') != false)
					{
						
						if($state_acceptable == false && strpos($value,'Akzeptabel') != false)
						{
							$value = substr($value, (strpos($value, ">",1)+1), -1);
							array_push($offers,$value);
							$state_acceptable = true;
							continue;
						}
						
						if($state_good == false && strpos($value,'Gut') != false)
						{
							$value = substr($value, (strpos($value, ">",1)+1), -1);
							array_push($offers,$value);
							$state_good = true;
							continue;
						}
						
						if($state_very_good == false && strpos($value,'Sehr gut') != false)
						{
							$value = substr($value, (strpos($value, ">",1)+1), -1);
							array_push($offers,$value);
							$state_very_good = true;
							continue;
						}	
						
						if($state_as_new == false && strpos($value,'Wie neu') != false)
						{
							$value = substr($value, (strpos($value, ">",1)+1), -1);
							array_push($offers,$value);
							$state_as_new = true;
							continue;
						}		
					}
				}
		
		}
	
		
	

// IMG:  http://www.amazon.de/gp/aw/d/B00OBIXRSY/ref=mw_dp_img_2_z?in=2&is=m&qid=1436192036&sr=8-1 
// Imagesize: m: 160x160 px, s: 110x110 px, l: 500x500 px.
$imagesize = "m";
	if(strcmp($imagesize,"s"))
	{
	$dim = 110;
	}
	if(strcmp($imagesize,"m"))
	{
	$dim = 160;
	}
	if(strcmp($imagesize,"l"))
	{
	$dim = 500;
	}
$imgurl = $url.'?is='.$imagesize;
$imghtml = file_get_html($imgurl);
$imgsrc = $imghtml ->find('img');
$img = $imgsrc[0];
$imgurl = substr($img, strpos($img, "src=") + strlen("src="), 120);
preg_match('~"(.*?)"~', $imgurl, $imgurl2);
$finalimgurl = str_replace('"','', $imgurl2[0]);


$pvl = get_idealo($name);
$name = utf8_encode($name);

$arr = [
0 => $name,
1 => $offers,
2 => $finalimgurl,
3 => $price,
4 => $pvl[0],
5 => $pvl[1],
];
return $arr;
}
?>