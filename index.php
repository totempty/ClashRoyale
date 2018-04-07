<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Reddit Ascenders Clash Royale Leaderboard Bot</title>
</head>

<body>
<h2 align="center">This is a bot for Clash Royale's Reddit Ascenders clans to generate the weekly leaderboard.</h2>
<h3 align="center">Join us on <a href="https://discord.gg/a9dTevt">Discord</a> ask for Munsterlander or Totem.</h3>
<h4 align="center">
<?php
include 'db.php';

function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

date_default_timezone_set('America/Los_Angeles');
$now = strtotime('now');
if (date('N', strtotime('now')) > 1 && date('N', strtotime('now')) < 5){
	$fri = strtotime('last friday');
	$sun = strtotime('last sunday');
	$chest_fri  = strtotime('friday this week');
	$diff = $chest_fri - $now;
} else {	
	$fri = strtotime('friday this week');
	$sun = strtotime('sunday this week');
	$diff = $now-$fri;
}
$diff = $diff/3600;
$hours = floor($diff);
$mins = floor(($diff-$hours)*60);
$suntext = '';
if(date("F",$fri)!=date("F",$sun)){
	$suntext = date("F",$sun).' ';
}
if (date('N', $now) > 4){
		echo 'The chest has started! The leaderboard is updated every 10 minutes.';
		echo '<br />The chest has been active for '.$hours.':'.str_pad($mins,2,'0',STR_PAD_LEFT);
} else {
	echo 'The chest will begin in '.$hours.':'.str_pad($mins,2,'0',STR_PAD_LEFT);
}
?>
</h4>
<br />
<div align="left">
:crossed_swords: :crown: __**Clan Chest Leaderboard**__ :crown: :crossed_swords: <br />
<?php
$db = db_connect();	

$result = $db->prepare("SELECT CLAN_TTL,TM, LVL FROM tblClanResults ORDER BY TM*1 ASC, LVL DESC");
$result->execute();
$result->bind_result($clan_ttl,$tm,$lvl);
$result->store_result();
$x = 1;
if($result->num_rows > 0){
	while ($result->fetch()) {
		echo '**'.$x.'. '.$clan_ttl.' - '.$tm.' hrs';
		$x++;
		if($lvl != 10){echo ' '.$lvl.'/10 cc';}
		echo '**<br />';
	}
}
echo '<br />:crossed_swords: :crown: __**Top 10 Crowns Grinders**__ :crown: :crossed_swords:<br />';
$result = $db->prepare("SELECT CLAN_TTL,USR_NM,CROWNS FROM `tblResults` LEFT JOIN tblClanResults ON tblClanResults.CLAN_NM=tblResults.CLAN_NM ORDER BY CROWNS DESC Limit 10");
$result->execute();
$result->bind_result($clan_ttl,$usr_nm,$crowns);
$result->store_result();
$x = 1;
if($result->num_rows > 0){
	while ($result->fetch()) {
		echo '**'.ordinal($x).' '.$usr_nm.' from @Cr-'.$clan_ttl.' with '.$crowns.'** :crown:<br />';
		$x++;
	}
}

echo '<br />';
echo "<br />:crossed_swords: :crown: __**Honorable Mentions**__ :crown: :crossed_swords:<br />";
$result = $db->prepare("SELECT DISTINCT tblClanResults.CLAN_TTL, t2.USR_NM, t2.CROWNS FROM tblResults2 t2 LEFT JOIN tblResults t1 ON t2.USR_TAG=t1.USR_TAG LEFT JOIN tblClanResults ON tblClanResults.CLAN_NM=t2.CLAN_NM WHERE t1.USR_TAG IS NULL ORDER BY t2.CROWNS DESC");
$result->execute();
$result->bind_result($clan_nm,$usr_nm,$crowns);
$result->store_result();
$x = 1;
if($result->num_rows > 0){			
	while ($result->fetch()) {
		echo "**".ordinal($x)." ".$usr_nm." supported ".$clan_nm." with ".$crowns."** :crown:<br />";
		$x++;
	}
} else {
	echo "**There were no honorable mentions this clan chest.**<br />";
}

echo '<br />';
echo '*Thank you everyone for contributing! (This clan chest was from '.date("F",$fri).' '.ordinal(date("d",$fri)).' - '.$suntext.ordinal(date("d",$sun)).'.)*';
?>
</div>
</body>
</html>