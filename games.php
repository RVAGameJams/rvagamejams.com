<?php
		
	$events_body="
		<div class='card'>
			<div class='card_info'>
				<h1>Games Created at Our Events</h1>
			</div>
			<div class='thumbnails'>
	";
	
	$games_list=[];
	$games=scandir("games/");
	$di=0;
	foreach($games as $key => $game)
	{
		if (strpos($game,".txt")!== false)
		{
			$txt_file=file_get_contents("games/".$game);
			$g=new Game;
			$g->id=str_replace(".txt","",$game);
			$g->img="games/images/404.png";
			if (file_exists('games/images/'.$g->id.'.png'))
				$g->img='games/images/'.$g->id.'.png';
			if (file_exists('games/images/'.$g->id.'.gif'))
				$g->img='games/images/'.$g->id.'.gif';
			
			$rows=explode("---",$txt_file);
			foreach($rows as $row)
			{
				$items=explode("::",$row);
				for ($i=0;$i<count($items);$i+=2)
				{
					if(strpos($items[$i],"title")!==false)
					{
						$g->title=rtrim($items[$i+1]);
					}
					elseif(strpos($items[$i],"author")!==false)
					{
						$g->author=rtrim($items[$i+1]);
					}
					elseif(strpos($items[$i],"event")!==false)
					{
						$g->event=rtrim($items[$i+1]);
					}
				}
			}
			
			$txt_file = file_get_contents("events/".$g->event.".txt");
			$rows=explode("---",$txt_file);
			foreach($rows as $row)
			{
				$items=explode("::",$row);
				for ($i=0;$i<count($items);$i+=2)
				{
					if(strpos($items[$i],"datestart")!==false)
					{
						$edate=rtrim($items[$i+1]);
					}
				}
			}
			
			$da=explode('-',rtrim($edate));
			$event_date=mktime(0,0,$di,$da[1],$da[2],$da[0]);	
			$games_list[$event_date]=$g;
			$di+=1;
		}
	}
	
	krsort($games_list);
	
	$game_links="<div class='thumbnails'>";
	foreach ($games_list as $g)
	{
		$game_links.="
			<a href=game.php?title=".$g->id.">
			<div class='thumbnail'>
				<img src=".$g->img.">
				<p>".$g->title."<br/>by ".$g->author."</p>
			</div>
			</a>
			";
	}
	$events_body.=$game_links;
	
	/*foreach ($previous as $event)
	{
		$events_body.="
			<a href=event.php?title=".$event->id.">
				<div class='previous_event'>
					<p>".$event->title."</p>
					<img src=".$event->img.">
				</div>
			</a>
		";
	}*/
		
	$events_body.="
			</div>
			</div>
			<div class='bspacer20'>.</div>
		</div>
	";
	
	class Event
	{
		public $id = "";
		public $img = "";
		public $title = "";
		public $sdate = "";
		public $venue = "";
	}
	
	class Game
	{
		public $id="";
		public $img="";
		public $title="";
		public $author="";
		public $event="";
	}
	
?>

<head>
  <link rel="stylesheet" href="include/style.css">
</head>

<body>
	<?php include 'include/header.php';?>
	<?php echo $events_body;?>
	<?php include 'include/footer.php';?>
</body>