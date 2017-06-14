<head>
  <link rel="stylesheet" href="include/style.css">
</head>

<body>
	<?php include 'include/header.php';?>
	
	<div class='card'>
		<div class='card_info'>
			<h1>Our Mission</h1>
			<p><?php echo file_get_contents('include/mission.txt') ?></p>
		</div>
	</div>
		
	<?php include 'include/footer.php';?>
</body>