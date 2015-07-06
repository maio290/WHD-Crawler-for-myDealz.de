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
echo '
';
echo $data_arr[0];
echo '
';
foreach($data_arr[1] as &$value)
{
$value = strip_tags($value);
echo $value;
echo '
';
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
return 'http://www.amazon.de/dp/'.$item_id.'/';
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

$priceurl = str_replace('/d','/ol',$url);
$priceurl .= '?o=Used&op=1';

$pricehtml = file_get_html($priceurl);

$pricehtml2 = $pricehtml ->find('a[href]');

$offers = array();

$state_acceptable = false;
$state_good = false;
$state_very_good = false;
$state_as_new = false;


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
];

return $arr;

}
?>