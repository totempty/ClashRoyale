<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Reddit Ascenders Clash Royale Activity Bot</title>
</head>
<body>
<h2 align="center">This is a bot for Clash Royale's Reddit Ascenders clans to generate the weekly activity of its members.</h2>
<h3 align="center">Join us on <a href="https://discord.gg/a9dTevt">Discord</a> ask for Munsterlander or Totem.</h3>
<?php
//certain connection information is in other files not on github.
include 'discord.php';
include 'db.php';
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

$db = db_connect();	
$clans = array("29RUQP8L", "292UCRGU", "289RYYGU", "9JULYGJV");
$arrlength = count($clans);
$opts = [
    "http" => [
        "header" => "auth:" . $token
    ]
];

$context = stream_context_create($opts);
$msg = "";
date_default_timezone_set('America/Los_Angeles');
if (date('N', strtotime('now')) == 7){
	$sun = strtotime('sunday 5:00 pm');
} else {	
	$sun = strtotime('sunday this week 5:00 pm');
	
}
$now = strtotime('now');
$diff = $sun-$now;
$diff = $diff/3600;
$hours = floor($diff);
$mins = floor(($diff-$hours)*60);
echo '<h4 align="center">The donation reset will happen in '.$hours.':'.str_pad($mins,2,'0',STR_PAD_LEFT).'</h4>';

for($x = 0; $x < $arrlength; $x++) {
	if($clans[$x]=='29RUQP8L'){
		$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,USR_RANK,DONATIONS+CROWNS as ACTVTY,USR_RANK FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND (DONATIONS < 200 AND CROWNS < 20) OR (USR_RANK='elder' AND DONATIONS < 300 AND CROWNS < 30) ORDER BY ACTVTY";
	} else if($clans[$x]=='292UCRGU'){
		$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,USR_RANK,DONATIONS+CROWNS as ACTVTY,USR_RANK FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND DONATIONS < 50 AND CROWNS < 10 ORDER BY ACTVTY";
	} else {
		$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,DONATIONS+CROWNS as ACTVTY,USR_RANK FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND  DONATIONS < 200 AND CROWNS < 20 ORDER BY ACTVTY";
	}
	$result = $db->query($sql);
	$y = 1;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			if($y==1){
				$msg = $msg."<br /><b>The Rule Breakers of ".$row['CLAN_NM']."</b><br />";
			}
			$crowns='0';
			if(strlen($row['CROWNS'])>0){
				$crowns=$row['CROWNS'];
			}
			$msg = $msg.$y.".  ".$row['USR_NM']." (".$row['USR_RANK'].") :gift: -> ".$row['DONATIONS']." (".$row['DONATION_PER']."%)  :crown: -> ".$crowns."<br />";					
			$y++;
		}
	}
}

echo $msg;
$msg = "<br />";

for($x = 0; $x < $arrlength; $x++) {
	$result = $db->query("SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,DONATIONS+CROWNS as ACTVTY,USR_RANK FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' ORDER BY ACTVTY DESC LIMIT 10");
	$y = 1;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			if($y==1){
				$msg = $msg."<br /><b>The Top 10 of ".$row['CLAN_NM']."</b><br />";
			}
			$crowns='0';
			if(strlen($row['CROWNS'])>0){
				$crowns=$row['CROWNS'];
			}
			$msg = $msg.$y.".  ".$row['USR_NM']." (".$row['USR_RANK'].") :gift: -> ".$row['DONATIONS']." (".$row['DONATION_PER']."%)  :crown: -> ".$crowns."<br />";					
			$y++;
		}
	}
}
echo $msg;	

if (isset($db))
		terminate($db);
?>
</body>
</html>