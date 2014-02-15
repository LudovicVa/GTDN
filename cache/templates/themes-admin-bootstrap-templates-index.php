<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $this->tpl_vars['page_title']; ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!-- Bootstrap -->
		<link href="/libraries/bootstrap-3.0.0/css/bootstrap.min.css" rel="stylesheet" media="screen" />
		<link href="/libraries/bootstrap-modal-2.2.0/css/bootstrap-modal-bs3patch.css" rel="stylesheet" />
		<link href="/libraries/bootstrap-modal-2.2.0/css/bootstrap-modal.css" rel="stylesheet" />
		<link href="/themes/admin-bootstrap/css/style.css" rel="stylesheet" />
		<?php if (isset($this->tpl_vars['css'])): ?><?php echo $this->tpl_vars['css']; ?><?php endif; ?>

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="/libraries/IE-compatibility/html5shiv.js"></script>
			<script src="/libraries/IE-compatibility/respond.min.js"></script>
		<![endif]-->

		<script type="text/javascript">
			var require = {
				deps: ['bootstrap', 'themes!admin-bootstrap/core-admin']
			};
		</script>

		<?php if (isset($this->tpl_vars['js'])): ?><?php echo $this->tpl_vars['js']; ?><?php endif; ?>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-menu-admin-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/" style="padding:0px"><img src="/themes/gtdn/img/logo_small.png" style="height:50px; padding:0px"/></a>
			</div>

			<div class="navbar-collapse collapse navbar-menu-admin-collapse">
				<?php if (!empty($this->tpl_vars['appsList'])): ?>
				<ul class="nav navbar-nav">
					<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['appsList'] as $this->tpl_vars['app'] => $this->tpl_vars['details']):
	$hidden_counter1++; ?>
					<li <?php if ($this->tpl_vars['app'] == $this->tpl_vars['appSelected']): ?> class="active"<?php endif; ?>>
						<a href="/admin/<?php echo $this->tpl_vars['app']; ?>/"><?php echo WLang::get("".$this->tpl_vars['details']['name'].""); ?></a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
				<?php if (!empty($this->tpl_vars['userNickname'])): ?>
				<p class="navbar-text pull-right">
					Bienvenue <?php echo $this->tpl_vars['userNickname']; ?>
					&nbsp;|&nbsp;
					<a href="/user/logout/" class="navbar-link">Déconnexion</a>
				</p>
				<?php endif; ?>
			</div><!--/.nav-collapse -->
		</div>
		<?php if (!empty($this->tpl_vars['adminMenu'])): ?>
		<div class="col-md-2">
			<ul class="nav nav-pills nav-stacked">
				<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['actionsList'] as $this->tpl_vars['action'] => $this->tpl_vars['desc']):
	$hidden_counter1++; ?>
				<?php if ($this->tpl_vars['desc']['menu']): ?>
				<li class="<?php if ($this->tpl_vars['action'] == $this->tpl_vars['actionAsked']): ?>active<?php endif; ?>">
					<a href="/admin/<?php echo $this->tpl_vars['appSelected']; ?>/<?php echo $this->tpl_vars['action']; ?>/"><?php echo WLang::get("".$this->tpl_vars['desc']['description'].""); ?></a>
				</li>
				<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div><!--/span-->
		<div class="col-md-10">
		<?php else: ?>
		<div class="col-md-12">
		<?php endif; ?>
			<?php echo $this->tpl_vars['notes']; ?>
			<div class="clearfix"></div>
			<?php echo $this->tpl_vars['include']; ?>
		</div><!--/span-->
		<div class="clearfix"></div>
		<div class="col-md-12">
			<hr />
			<footer>
				<p>&copy; WityCMS Administration 2013</p>
			</footer>
		</div>
	</body>
</html>
