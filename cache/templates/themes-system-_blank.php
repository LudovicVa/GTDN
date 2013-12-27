<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $this->tpl_vars['page_title']; ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!-- Bootstrap -->
		<link href="/libraries/bootstrap-3.0.0/css/bootstrap.min.css" rel="stylesheet" />
		<?php if (isset($this->tpl_vars['css'])): ?><?php echo $this->tpl_vars['css']; ?><?php endif; ?>

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="/libraries/IE-compatibility/html5shiv.js"></script>
			<script src="/libraries/IE-compatibility/respond.min.js"></script>
		<![endif]-->

		<script type="text/javascript">
			var require = {
				deps: ['bootstrap']
			};
		</script>
		<?php if (isset($this->tpl_vars['js'])): ?><?php echo $this->tpl_vars['js']; ?><?php endif; ?>
	</head>
	<body>
		<div class="container">
			<?php echo $this->tpl_vars['notes']; ?>
			<?php echo $this->tpl_vars['include']; ?>
		</div>
	</body>
</html>
