<?php
/*
The MIT License (MIT)
Copyright (c) 2015 Mario-Luca Hoffmann for files: whd.php & whd_form.html
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
	if($_POST)
	{
	$asin = get_asin($_POST['url']);
	$urlm = prepare_url_mobile($asin);
	$urld = prepare_url_desktop($asin);
	$data_arr = get_data($urlm);
	echo '<textarea cols="50" rows="10" >';
	echo '[url='.$urld.']';
	echo '[img]'.$data_arr[2].'[/img]';
	echo "\n";
	echo $data_arr[0];
	echo "\n";
	echo "\n";
	
	// Convert to number in order to compare it properly
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
if(strpos($url,"/dp/") != false)
{return substr($url, strpos($url, 'dp/') + strlen('dp/'), 10);}
if(strpos($url,"/gp/product/") != false)
{return substr($url, strpos($url, 'product/') + strlen('product/'), 10);}
}
function get_data($url) {
// Use with mobile links only
get_data:
$html = file_get_html($url)->plaintext;
$name =  substr($html, 0, strpos($html, "Amazon.de")-1);
$name = trim($name," ");
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
				goto refetch_outer;
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
$arr = [
0 => $name,
1 => $offers,
2 => $finalimgurl,
3 => $price
];
return $arr;
}
?>