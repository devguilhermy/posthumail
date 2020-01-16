<?php //include "../host.php"
$host = "//" . $_SERVER['HTTP_HOST'] . "/posthumail/"
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo $host ?>menu/menu.php"><strong class="sangrenta">Posthumail</strong></a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li class="active"><a href="<?php echo $host ?>menu/menu.php">Home<span class="sr-only">(current)</span></a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Messages&nbsp;<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $host ?>menu/message/new.php"><span class="glyphicon glyphicon-asterisk"></span>&nbsp;Add message</a></li>
						<li role="separator" class="divider"></li>
						<li><a href="<?php echo $host ?>menu/message/list.php"><span class="glyphicon glyphicon-th-list"></span>&nbsp;List messages</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a>Hello, <b>Guilhermy!</b></a></li>
				<li><a href="<?php echo $host ?>menu/manage.php"><span id="icon-nav" class="glyphicon glyphicon-cog"></span></a></li>
				<li><a href="#" id="logout"><span id="icon-nav" class="glyphicon glyphicon-log-out"></span></a></li>
			</ul>
		</div>
		<!--/.nav-collapse -->
	</div>
</nav>