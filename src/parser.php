<?php
// Max Base
// https://github.com/BaseMax/CoronaVirusOutbreakAPI
include "_netphp.php";
$file=get("https://www.worldometers.info/coronavirus/")[0];
// print $file;
$file=remove_comments($file);
file_put_contents("_page.html", $file);
// $file=file_get_contents("_page.html");

function remove_comments($html) {
	return preg_replace_callback('/<!--[\s\S]*?-->/', 'remove_comments_check', $html);
}
function remove_comments_check($search) {
	// copy from stackoverflow...
	list($l) = $search;
	if(mb_eregi("\[if",$l) || mb_eregi("\[endif",$l)) {
		return $l;
	}
}
function parseData($content) {
	$regex=explode("\n", file_get_contents("regex.txt"));
	if($content == "" || $content == null) {
		return [];
	}
	preg_match($regex[0], $content, $table);
	if(preg_match_all($regex[1], $table["content"], $matches)) {
		print_r($matches);
		foreach($matches as $key=>$array) {
			if(!is_string($key)) {
				unset($matches[$key]);// To remove extra list, arrays!
			}
			else {
				// Why we do it?
				// we can remove below code if use this example in the regex query
				// e.g: (\s*|)(?<name>[^\<]+)(\s*|)
				// But we did it as below
				// e.g: (?<name>[^\<]+)
				foreach($matches[$key] as $i=>$value) {
					$matches[$key][$i]=trim($value);
				}
			}
		}
		return $matches;
	}
	return [];
}

function prepareData($matches) {
	if(!is_array($matches) || $matches == null) {
		return [];
	}
	$result=[];
	foreach($matches["name"] as $i=>$name) {
		$totalCase=$matches["totalCase"][$i];
		$newCase=$matches["newCase"][$i];
		$totalDeath=$matches["totalDeath"][$i];
		$newDeath=$matches["newDeath"][$i];
		$totalRecovered=$matches["totalRecovered"][$i];
		$seriousUser=$matches["seriousUser"][$i];
		$result[]=[
			"name"=>strtolower($name),
			"totalCase"=>$totalCase,
			"newCase"=>$newCase,
			"totalDeath"=>$totalDeath,
			"newDeath"=>$newDeath,
			"totalRecovered"=>$totalRecovered,
			"seriousUser"=>$seriousUser,
		];
	}
	return $result;
}

$matchs=parseData($file);
print_r($matchs);
$items=prepareData($matchs);
//////////////////////////////////////////////////////////
print_r($items);
//////////////////////////////////////////////////////////
$CREATE_MD_TABLE=true;
$CREATE_JSON=true;
$CREATE_HTML=true;
//////////////////////////////////////////////////////////
if($CREATE_MD_TABLE) {
	$table="";
	$table.="| Name | Total Case | New Case | Total Death | New Death | Total Recovered | seriousUser\n";
	$table.="| ---- | --------- | ------- | ---------- | -------- | -------------- | ---------- |\n";
	foreach($items as $item) {
		// name, totalCase, newCase, totalDeath, newDeath, totalRecovered, seriousUser
		$table.="| ".$item["name"]." | ".$item["totalCase"]." | ".$item["newCase"]." | ".$item["totalDeath"]." | ".$item["newDeath"]." | ".$item["totalRecovered"]." | ".$item["seriousUser"]." |\n";
	}
	$table.="\n";
	file_put_contents("../output.md", $table);
}
//////////////////////////////////////////////////////////
if($CREATE_JSON) {
	file_put_contents("../output.json", json_encode($items));
}
//////////////////////////////////////////////////////////
if($CREATE_HTML) {
	$html="<!doctype html>\n<html>\n\t<head>\n\t\t<meta charset=\"utf-8\">\n\t\t<title>Corona Virus Outbreak</title>\n\t</head>\n\t<body>\n\t\t<h1>Max Base - Corona Virus Outbreak</h1>\n\t\t<table width=\"100%\" border=\"1\">\n";
	// name, totalCase, newCase, totalDeath, newDeath, totalRecovered, seriousUser
	$html.="\t\t\t<tr align=\"center\"><td>Name</td><td>Total Case</td><td>New Case</td><td>Total Death</td><td>New Death</td><td>Total Recovered</td><td>Serious User</td></tr>\n";
	foreach($items as $item) {
		$html.="\t\t\t<tr><td>".$item["name"]."</td><td>".$item["totalCase"]."</td><td>".$item["newCase"]."</td><td>".$item["totalDeath"]."</td><td>".$item["newDeath"]."</td><td>".$item["totalRecovered"]."</td><td>".$item["seriousUser"]."</td></tr>\n";
	}
	$html.="\t\t</table>\n\t</body>\n</html>\n";
	file_put_contents("../output.html", $html);
}
