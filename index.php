<?php
	
	$events=scandir("events/");
	$index_body="";
	$event_array=[];
	$upcoming=[];
	$previous=[];
	
	foreach($events as $event)
	{
		if (strpos($event,".txt")!==false)
		{
			$e = new Event;
			$e->id = str_replace(".txt","",$event);
			$e->img="events/images/404.png";
			if (file_exists('events/images/'.$e->id.'.png'))
				$e->img='events/images/'.$e->id.'.png';
			
			$txt_file = file_get_contents("events/".$event);
			$rows=explode("---",$txt_file);
			foreach($rows as $row)
			{
				$items=explode("::",$row);
				for ($i=0;$i<count($items);$i+=2)
				{
					if(strpos($items[$i],"event")!==false)
					{
						$e->title=rtrim($items[$i+1]);
					}
					elseif(strpos($items[$i],"datestart")!==false)
					{
						$e->sdate=rtrim($items[$i+1]);
					}
					elseif(strpos($items[$i],"venue")!==false)
					{
						$e->venue=rtrim($items[$i+1]);
					}
				}
			}
			
			//$event_array[$e->sdate]=$e;
			
			$da=explode('-',rtrim($e->sdate));
			$event_date=mktime(0,0,0,$da[1],$da[2],$da[0]);			
			$completed=false;
			if ($e->sdate!="")
				$completed = time() > $event_date;
			if ($completed)
				$previous[$e->sdate]=$e;
			else
				$upcoming[$e->sdate]=$e;
			
			/*$index_body.="
				<div class='card_img'>
					<img src=".$event_img.">
				</div>
			";*/
		}
		
	}
	
	ksort($upcoming);
	
	if (count($upcoming)>0)
	{
		$index_body.="
			<div class='card_info'>
				<h1>Next Event:</h1>
			</div>
		";
		
		foreach ($upcoming as $event)
		{
			$da=explode('-',rtrim($event->sdate));
			$event_date=mktime(0,0,0,$da[1],$da[2],$da[0]);	
			$index_body.="
				<a href=event.php?title=".$event->id.">
					<div class='upcoming_event'>
						<p>".$event->title."</p>
						<img src=".$event->img.">
						<div class='upcoming_date'>
							<h2>".date("M",$event_date)."</h2>
							<h1>".date("d",$event_date)."</h1>
						</div>
					</div>
				</a>
			";
			break;
		}
		
		$index_body.="
			<div class='bspacer20'>.</div>
			<div class='more_info'>
				<p><a href='events.php'>> View all events</a></p>
			</div>
			</div>
			
			<div class='features'>
				<ul>
					<li><a href='resources.php'>Resources</a></li>
					<li><a href='about.php'>About</a></li>
					<li><a href='donate.php'>Donate</a></li>
				</ul>
			</div>
			
			<div class='card'>
		";
	}
	
	$index_body.="
		<div class='card_info'>
			<h1>Most Recent Games</h1>
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
						$e->sdate=rtrim($items[$i+1]);
					}
				}
			}
			
			$da=explode('-',rtrim($e->sdate));
			$event_date=mktime(0,0,$di,$da[1],$da[2],$da[0]);	
			$games_list[$event_date]=$g;
			$di+=1;
		}
	}
	
	krsort($games_list);
	
	$game_links="<div class='thumbnails'>";
	$gi=0;
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
		$gi++;
		if ($gi==9)
		{
			break;
		}
	}
	$index_body.=$game_links;
	
	/*foreach ($previous as $event)
	{
		$index_body.="
			<a href=event.php?title=".$event->id.">
				<div class='previous_event'>
					<p>".$event->title."</p>
					<img src=".$event->img.">
				</div>
			</a>
		";
	}*/
		
	$index_body="
		<div class='card'>
			".$index_body."
			</div>
			</div>
			<div class='bspacer20'>.</div>
			<div class='more_info'>
				<p><a href='games.php'>> View all games</a></p>
			</div>
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
	<?php echo $index_body;?>
	<?php include 'include/footer.php';?>
</body>