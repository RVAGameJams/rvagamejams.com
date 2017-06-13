<?php
	
	$events=scandir("events/");
	$events_body="";
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
				$previous[$event_date]=$e;
			else
				$upcoming[$event_date]=$e;
			
			/*$events_body.="
				<div class='card_img'>
					<img src=".$event_img.">
				</div>
			";*/
		}
		
	}
	
	ksort($upcoming);
	krsort($previous);
	
	if (count($upcoming)>0)
	{		
		$events_body.="
			<div class='card_info'>
				<h1>Upcoming Events</h1>
			</div>
		";
		
		foreach ($upcoming as $event)
		{
			$da=explode('-',rtrim($event->sdate));
			$event_date=mktime(0,0,0,$da[1],$da[2],$da[0]);	
			$events_body.="
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
		}
		
		$events_body.="
			<div class='bspacer20'>.</div>
		";
	}
	
	$events_body.="
		<div class='card_info'>
			<h1>Previous Events</h1>
		</div>
		<div class='thumbnails'>
	";
	
	foreach ($previous as $event)
	{
		$events_body.="
			<a href=event.php?title=".$event->id.">
				<div class='previous_event'>
					<p>".$event->title."</p>
					<img src=".$event->img.">
				</div>
			</a>
		";
	}
		
	$events_body="
		<div class='card'>
			".$events_body."
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
	
?>

<head>
  <link rel="stylesheet" href="include/style.css">
</head>

<body>
	<?php include 'include/header.php';?>
	<?php echo $events_body;?>
	<?php include 'include/footer.php';?>
</body>