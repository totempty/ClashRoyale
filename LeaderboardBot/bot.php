<?php
// certain connection information is in this file not on github.
include 'db.php';
include 'discord.php';

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

date_default_timezone_set('America/Los_Angeles');
if (date('N', strtotime('now')) > 1 && date('N', strtotime('now')) < 5){
	$fri = strtotime('last friday');
	$sun = strtotime('last sunday');
} else {	
	$fri = strtotime('friday this week');
	$sun = strtotime('sunday this week');
	
}
$now = strtotime('now');
$diff = $now-$fri;
$diff = $diff/3600;
$hours = floor($diff);
$mins = floor(($diff-$hours)*60);
$suntext = '';
if(date("F",$fri)!=date("F",$sun)){
	$suntext = date("F",$sun).' ';
}

if (date('N', $now) > 4){
	$db->query("DELETE FROM tblResults");
	for($x = 0; $x < $arrlength; $x++) {
		$url = 'https://api.royaleapi.com/clan/'.$clans[$x];
		$results = file_get_contents($url,true, $context);
		$json = json_decode($results,true);
		if($json != null) {
			if($json['clanChest']['crowns']<1600){
				$db->query("UPDATE tblClanResults SET TM='".$hours.':'.str_pad($mins,2,'0',STR_PAD_LEFT)."',LVL=".$json['clanChest']['level'].",CRWNS=".$json['clanChest']['crowns']." WHERE CLAN_NM='".$clans[$x]."'");
			} else if($json['clanChest']['crowns']==1600){
				$db->query("UPDATE tblClanResults SET LVL=".$json['clanChest']['level'].",CRWNS=".$json['clanChest']['crowns']." WHERE CLAN_NM='".$clans[$x]."' AND LVL<>10");
			}
			
			foreach($json['members'] as $member) {
				$db->query("INSERT INTO tblResults(CLAN_NM,USR_NM,CROWNS) VALUES('".$clans[$x]."','".$member['name']."','".$member['clanChestCrowns']."')");
			}
		}
	}
	
} else if (date('N', $now) == 1){
	$db->query("UPDATE tblClanResults SET TM='72:00' WHERE LVL<>10");
	$result2 = $db->query("SELECT ID FROM tblPostHistory WHERE DATE_FORMAT(DT_POSTED, '%m/%d/%Y')=DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -7 HOUR), '%m/%d/%Y')");
	if($result2->num_rows == 0){
		$msg = ":crossed_swords: :crown: __**Clan Chest Leaderboard**__ :crown: :crossed_swords:\n";
		$result = $db->prepare("SELECT CLAN_TTL,TM, LVL FROM tblClanResults ORDER BY STR_TO_DATE(TM,'%h%i') ASC, LVL DESC");
		$result->execute();
		$result->bind_result($clan_ttl,$tm,$lvl);
		$result->store_result();
		$x = 1;
		if($result->num_rows > 0){
			while ($result->fetch()) {
				$msg = $msg."**".$x.". ".$clan_ttl." - ".$tm." hrs";
				$x++;
				if($lvl != 10){$msg = $msg." ".$lvl."/10 cc";}
				$msg = $msg."**\n";
			}
		}
		$msg = $msg."\n:crossed_swords: :crown: __**Top 10 Crowns Grinders**__ :crown: :crossed_swords:\n";
		$result = $db->prepare("SELECT CLAN_ID,CLAN_TTL,USR_NM,CROWNS FROM `tblResults` LEFT JOIN tblClanResults ON tblClanResults.CLAN_NM=tblResults.CLAN_NM ORDER BY CROWNS DESC Limit 10");
		$result->execute();
		$result->bind_result($clan_id,$clan_ttl,$usr_nm,$crowns);
		$result->store_result();
		$x = 1;
		if($result->num_rows > 0){
			while ($result->fetch()) {
				$msg = $msg."**".ordinal($x)." ".$usr_nm." from <@&".$clan_id."> with ".$crowns."** :crown:\n";
				$x++;
			}
		}
		$msg = $msg."\n";
		$msg = $msg."*Thank you everyone for contributing! (This clan chest was from ".date("F",$fri)." ".ordinal(date("d",$fri))." - ".$suntext.ordinal(date("d",$sun)).".)*";
		$db->query("INSERT INTO tblPostHistory(DT_POSTED) VALUES (CURRENT_TIMESTAMP)");			
		DiscordHook::send(new Message(new User($webhook, "LeaderboardBot"), $msg));
	}
}
if (isset($db))
		terminate($db);
?>
