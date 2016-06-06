<?php 
$jobs = json_decode(file_get_contents('../data/2016-06-05.json'));
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>trampos</title>
	<style>
		td{
			border:1px solid black;
			padding:15px 0;
		}
	</style>
</head>
<body>
	<table>
		<?php foreach ($jobs as $job): ?>
			<tr>
				<td class="mark"><?=$job->description?></td>
			</tr>
		<?php endforeach ?>
	</table>
	<script>
		document.addEventListener('click', function(e){
			var color = e.target.style.background == 'yellow' ? 'white':'yellow';
			console.log(e.target.style.background == 'yellow' ? 'white':'yellow');
			e.target.style.background = color;
		});
	</script>
</body>
</html>