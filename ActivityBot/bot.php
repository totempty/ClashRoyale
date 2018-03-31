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

date_default_timezone_set('America/Los_Angeles');
$now = strtotime('now');
if (date('N', $now) == 7){
	if (date('G', $now) < 17){
		for($x = 0; $x < $arrlength; $x++) {
			$db->query("DELETE FROM tblActivity");
			$url = 'https://api.royaleapi.com/clan/'.$clans[$x];
			$results = file_get_contents($url,true, $context);
			$json = json_decode($results,true);
			if($json !== null) {				
				foreach($json['members'] as $member) {
					$db->query("INSERT INTO tblActivity(CLAN_TAG,CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,USR_RANK) VALUES('".$clans[$x]."','".$json['name']."','".$member['tag']."','".$member['name']."','".$member['donations']."','".$member['donationsPercent']."','".$member['clanChestCrowns']."','".$member['role']."')");
				}
			}
		}	
	} else {
		for($x = 0; $x < $arrlength; $x++) {
			$url = 'https://api.royaleapi.com/clan/'.$clans[$x];
			$results = file_get_contents($url,true, $context);
			$json = json_decode($results,true);
			if($json !== null) {				
				foreach($json['members'] as $member) {
					$db->query("UPDATE tblActivity SET CROWNS='".$member['clanChestCrowns']."' WHERE USR_TAG='".$member['tag']."'");
				}
			}
		}
	}
} else if (date('N', $now) == 1){	
	$result2 = $db->query("SELECT ID FROM tblPostHistory WHERE DATE_FORMAT(DT_POSTED, '%m/%d/%Y')=DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -7 HOUR), '%m/%d/%Y')");
	if($result2->num_rows == 0){
		$msg = "";
		for($x = 0; $x < $arrlength; $x++) {
			if($clans[$x]=='29RUQP8L'){
				$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,USR_RANK,DONATIONS+CROWNS as ACTVTY FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND (DONATIONS < 200 AND CROWNS < 20) OR (USR_RANK='elder' AND DONATIONS < 300 AND CROWNS < 30) ORDER BY ACTVTY LIMIT 10";
			} else if($clans[$x]=='292UCRGU'){
				$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,USR_RANK,DONATIONS+CROWNS as ACTVTY FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND DONATIONS < 50 AND CROWNS < 10 ORDER BY ACTVTY LIMIT 10";
			} else {
				$sql = "SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,DONATIONS+CROWNS as ACTVTY FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' AND  DONATIONS < 200 AND CROWNS < 20 ORDER BY ACTVTY LIMIT 10";
			}
			$result = $db->query($sql);
			$y = 1;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($y==1){
						$msg = $msg."__**The Bottom 10 of ".$row['CLAN_NM']."**__\n";
					}
					$crowns='0';
					if(strlen($row['CROWNS'])>0){
						$crowns=$row['CROWNS'];
					}
					$msg = $msg.$y.".  ".$row['USR_NM']." (".$row['USR_RANK'].") :gift: -> ".$row['DONATIONS']." (".$row['DONATION_PER']."%)  :crown: -> ".$crowns."\n";					
					$y++;
				}
			}
		}		
		if(strlen($msg)){			
			DiscordHook::send(new Message(new User($webhook, "ClanActivityBot"), $msg));
			$db->query("INSERT INTO tblPostHistory(DT_POSTED) VALUES (CURRENT_TIMESTAMP)");	
		}
		
	}

	$result2 = $db->query("SELECT ID FROM tblPostHistory WHERE DATE_FORMAT(DT_POSTED, '%m/%d/%Y')=DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -7 HOUR), '%m/%d/%Y')");
	if($result2->num_rows == 0){
		$msg="";
		for($x = 0; $x < $arrlength; $x++) {
			$result = $db->query("SELECT CLAN_NM,USR_TAG,USR_NM,DONATIONS,DONATION_PER,CROWNS,DONATIONS+CROWNS as ACTVTY FROM tblActivity WHERE CLAN_TAG='".$clans[$x]."' ORDER BY ACTVTY DESC LIMIT 10");
			$y = 1;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($y==1){
						$msg = $msg."__**The Top 10 of ".$row['CLAN_NM']."**__\n";
					}
					$crowns='0';
					if(strlen($row['CROWNS'])>0){
						$crowns=$row['CROWNS'];
					}
					$msg = $msg.$y.".  ".$row['USR_NM']." :gift: -> ".$row['DONATIONS']." (".$row['DONATION_PER']."%)  :crown: -> ".$crowns."\n";					
					$y++;
				}
			}
		}
		if(strlen($msg)){		 
			DiscordHook::send(new Message(new User($webhook, "ClanActivityBot"), $msg));
			$db->query("INSERT INTO tblPostHistory(DT_POSTED) VALUES (CURRENT_TIMESTAMP)");
		}		
	}	
}
if (isset($db))
		terminate($db);
?>