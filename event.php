 <?php
	
	$event_links="<div class='eventlinks'><ul>";
	$event_id=$_GET['title'];
	$event_img='game_images/holder.gif';
	
	$event_title="title needed";
	$event_venue="venue tba";
	$event_dates="date tba";
	$event_pdate="";
	$event_stime="start time tba";
	$event_descr="description needed";
	$event_sdate="";
	$event_edate="";
	
	$date=time();
	
	/*
		Get game image
	*/
	if (file_exists('events/images/'.$event_id.'.png'))
		$event_img='events/images/'.$event_id.'.png';
	
	/*
		Get text to parse
	*/
    $txt_file = file_get_contents('events/'.$event_id.'.txt');
	$rows     = explode("---", $txt_file);

	foreach($rows as $row)
	{
		$items = explode("::", $row);
		
		for ($i=0;$i<count($items);$i+=2)
		{
			if(strpos($items[$i],"event")!==false)
			{
				$event_title=rtrim($items[$i+1]);
			}
			elseif(strpos($items[$i],"venue")!==false)
			{
				$event_venue=rtrim($items[$i+1]);
			}
			elseif(strpos($items[$i],"vlink")!==false)
			{
				$event_venue="<a href=".rtrim($items[$i+1]).">".$event_venue."</a>";
			}
			elseif(strpos($items[$i],"directions")!==false)
			{
				$event_links.="<li><a href=".rtrim($items[$i+1])."><i class='material-icons'>place</i>directions</a></li>";
			}
			elseif(strpos($items[$i],"infolink")!==false)
			{
				$event_links.="<li><a href=".rtrim($items[$i+1])."><i class='material-icons'>info</i>more info</a></li>";
			}
			elseif(strpos($items[$i],"rsvplink")!==false)
			{
				$event_links.="<li><a href=".rtrim($items[$i+1])."><i class='material-icons'>mail</i>rsvp</a></li>";
			}
			elseif(strpos($items[$i],"opentime")!==false)
			{
				$event_stime='doors open at '.rtrim($items[$i+1]);
			}
			elseif(strpos($items[$i],"datestart")!==false)
			{
				$da=explode('-',rtrim($items[$i+1]));
				$event_sdate=mktime(0,0,0,$da[1],$da[2],$da[0]);
				$event_dates=date("M j",$event_sdate);
				$event_pdate=date("M Y", $event_sdate);
			}
			elseif(strpos($items[$i],"dateend")!==false)
			{
				$da=explode('-',rtrim($items[$i+1]));
				$event_edate=mktime(0,0,0,$da[1],$da[2],$da[0]);
				$event_dates.='-'.date("j",$event_edate);
			}
			elseif(strpos($items[$i],"description")!==false)
			{
				$event_descr=rtrim($items[$i+1]);
			}
		}
	}
	
	$completed=false;
	if ($event_sdate!="")
		$completed = $date > $event_sdate;
	if ($event_edate!="")
		$completed = $date > $event_edate;
	if ($completed)
	{
		$gamelist=get_games();
		$event_body="
			<div class='card'>
				<div class='card_img'>
					<img src=".$event_img.">
				</div>
				<div class='card_info'>
					<h1>".$event_title."</h1>
					<h2>".$event_pdate." at ".$event_venue."</h2>
				</div>
				".get_games()."
			</div>
		";
		//todo - add games
	}
	else
	{
		$event_links.="</ul></div>";
		$event_body="
			<div class='card'>
				<div class='card_img'>
					<img src=".$event_img.">
				</div>
				<div class='card_info'>
					<h1>".$event_title."</h1>
					<h2>".$event_dates." at ".$event_venue."</h2>
					<h2>".$event_stime."</h2>
					<p>".$event_descr."</p>
					
				</div>
				".$event_links."
			</div>
		";
	}
	
	function get_games()
	{
		$game_links="<div class='thumbnails'>";
		
		$games=scandir("games/");
		foreach($games as $key => $game)
		{
			if (strpos($game,".txt")!==false)
			{
				$game_id=str_replace(".txt","",$game);
				$game_ti="";
				$game_au="";
				$game_ev="";
				
				$txt_file=file_get_contents("games/".$game);
				
				$game_img="games/images/404.png";
				if (file_exists('games/images/'.$game_id.'.png'))
					$game_img='games/images/'.$game_id.'.png';
				if (file_exists('games/images/'.$game_id.'.gif'))
					$game_img='games/images/'.$game_id.'.gif';
				
				$rows=explode("---",$txt_file);
				foreach($rows as $row)
				{
					$items=explode("::",$row);
					for ($i=0;$i<count($items);$i+=2)
					{
						if(strpos($items[$i],"title")!==false)
						{
							$game_ti=rtrim($items[$i+1]);
						}
						elseif(strpos($items[$i],"author")!==false)
						{
							$game_au=rtrim($items[$i+1]);
						}
						elseif(strpos($items[$i],"event")!==false)
						{
							$game_ev=rtrim($items[$i+1]);
						}
					}
				}
				
				if($game_ev==$_GET['title'])
				{
					//todo add game
					//$game_links.="<a href=game.php?title=".$game_id.">".$game_ti." - ".$game_au."</a><br/>";
					$game_links.="
					<a href=game.php?title=".$game_id.">
					<div class='thumbnail'>
						<img src=".$game_img.">
						<p>".$game_ti."<br/>by ".$game_au."</p>
					</div>
					</a>
					";
				}
			}
		}
		$game_links.="</div><div class='bspacer20'>.</div>";
		return $game_links;
	}
?>

<head>
  <link rel="stylesheet" href="include/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">


</head>

<body>
	<?php include 'include/header.php';?>
	<?php echo $event_body;?>
	<?php include 'include/footer.php';?>
</body>