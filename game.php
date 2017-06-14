 <?php
	
	$game_links="<div class='infolinks'><ul>";
	$game_id=$_GET['title'];
	$game_img='game_images/holder.gif';
	
	$game_title="title needed";
	$game_author="author needed";
	$game_event="event needed";
	$game_descr="description needed";
	
	/*
		Get game image - gif prefered
	*/
	if (file_exists('games/images/'.$game_id.'.png'))
		$game_img='games/images/'.$game_id.'.png';
	if (file_exists('games/images/'.$game_id.'.gif'))
		$game_img='games/images/'.$game_id.'.gif';
	
	/*
		Get text to parse
	*/
    $txt_file = file_get_contents('games/'.$game_id.'.txt');
	$rows     = explode("---", $txt_file);

	foreach($rows as $row)
	{
		$items = explode("::", $row);
		
		for ($i=0;$i<count($items);$i+=2)
		{
			if(strpos($items[$i],"title")!==false)
			{
				$game_title=rtrim($items[$i+1]);
			}
			elseif(strpos($items[$i],"author")!==false)
			{
				$game_author=rtrim($items[$i+1]);
			}
			elseif(strpos($items[$i],"playlink")!==false)
			{
				$game_links.="<li><a href=".rtrim($items[$i+1]).">
					<i class='material-icons'>videogame_asset</i>play</a></li>";
			}
			elseif(strpos($items[$i],"jamlink")!==false)
			{
				$game_links.="<li><a href=".rtrim($items[$i+1]).">
					<i class='material-icons'>link</i>jam page</a></li>";
			}
			elseif(strpos($items[$i],"event")!==false)
			{
				$game_event='<a href=event.php?title='.rtrim($items[$i+1]).'>'.rtrim(get_event_node($items[$i+1],"event")).'</a>';
			}
			elseif(strpos($items[$i],"description")!==false)
			{
				$game_descr=rtrim($items[$i+1]);
			}
		}
	}
	
	/*
		get node from event text
	*/
	function get_event_node($e_title, $e_node_string)
	{
		$event_text=file_get_contents('events/'.rtrim($e_title).'.txt');
		$event_rows=explode("---",$event_text);
		foreach($event_rows as $row)
		{
			$items=explode("::",$row);
			for ($i=0;$i<count($items);$i+=2)
			{
				if(strpos($items[$i],$e_node_string)!==false)
				{
					return $items[$i+1];
				}
			}
		}
		return "UH OH! Node ".$e_node_string." is missing!";
	}
	
	$game_links.="</ul></div>";
	$game_body="
		<div class='card'>
			<div class='card_img'>
				<img src=".$game_img.">
			</div>
			<div class='card_info'>
				<h1>".$game_title."&nbsp;-&nbsp;".$game_author."</h1>
				<h2>".$game_event."</h2>
				<p>".$game_descr."</p>
				
			</div>
			".$game_links."
		</div>
	";
	
?>

<head>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="include/style.css">
</head>

<body>
	<?php include 'include/header.php';?>
	<?php echo $game_body;?>
	<?php include 'include/footer.php';?>
</body>