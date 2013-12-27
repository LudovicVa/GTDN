<div class="wity-app wity-app-user wity-action-listing">
	<div class="row">
		<div class="col-md-9">
		<?php if (!empty($this->tpl_vars['users_waiting'])): ?>
			<h2><?php echo WLang::get("waiting"); ?></h2>
			<form id="admin-check-form" action="/admin/user/listing" method="post">
				<table id="admin-check" class="table table-hover table-striped">
					<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['users_waiting'] as $this->tpl_vars['count'] => $this->tpl_vars['user']):
	$hidden_counter1++; ?>
					<tr class="waiting" data-title="<?php echo $this->tpl_vars['user']['nickname']; ?>" data-content="&lt;ul class='unstyled'&gt;&lt;li&gt;<?php echo $this->tpl_vars['user']['firstname']; ?> <?php echo $this->tpl_vars['user']['lastname']; ?>&lt;/li&gt;&lt;li&gt;<?php echo $this->tpl_vars['user']['email']; ?>&lt;/li&gt;&lt;/ul&gt;">
						<td><?php echo $this->tpl_vars['user']['id']; ?></td>
						<td><strong><?php echo $this->tpl_vars['user']['nickname']; ?></strong></td>
						<td><?php echo $this->tpl_vars['user']['created_date']; ?></td>
						<td>
							<input class="hidden" type="radio" name="admin_check[<?php echo $this->tpl_vars['user']['id']; ?>]" value="validate" />
							<input class="hidden" type="radio" name="admin_check[<?php echo $this->tpl_vars['user']['id']; ?>]" value="refuse" />
							<a class="validate" href="javascript:void(0)" title="<?php echo WLang::get("validate"); ?>"><i class="text-success glyphicon glyphicon-ok"></i></a>
							<a class="refuse" href="javascript:void(0)" title="<?php echo WLang::get("refuse"); ?>"><i class="text-danger glyphicon glyphicon-remove"></i></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>
				<div id="admin-check-buttons" class="pull-right form-inline">
					<div class="checkbox">
						<label><input type="checkbox" name="notify" checked="checked" /> <?php echo WLang::get("notify_these_users"); ?></label>
					</div>
					<input id="admin-check-button" class="btn btn-primary" type="submit" value="<?php echo WLang::get("submit"); ?>" />
					<input id="cancel-button" class="btn btn-default" type="button" value="<?php echo WLang::get("cancel"); ?>" />
				</div>
				<div class="clearfix"></div>
			</form>
			<hr />
		<?php endif; ?>
			
			<h2 class="sr-only"><?php echo WLang::get("action_listing"); ?></h2>
			<span class="label label-info"><?php echo WLang::get("users_total", array($this->tpl_vars['stats']['total'], )); ?></span>
		<?php if ($this->tpl_vars['stats']['request'] < $this->tpl_vars['stats']['total']): ?>
			<span class="label label-warning"><?php echo WLang::get("users_found", array($this->tpl_vars['stats']['request'], )); ?></span>
		<?php endif; ?>
			
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th><a href="/admin/user/listing/id-<?php echo $this->tpl_vars['id_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['id_class']; ?>"></i> #</a></th>
						<th><a href="/admin/user/listing/nickname-<?php echo $this->tpl_vars['nickname_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("nickname"); ?></a></th>
						<th><a href="/admin/user/listing/groupe-<?php echo $this->tpl_vars['groupe_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['groupe_class']; ?>"></i> <?php echo WLang::get("group"); ?></a></th>
						<th><a href="/admin/user/listing/created_date-<?php echo $this->tpl_vars['created_date_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['created_date_class']; ?>"></i> <?php echo WLang::get("register_time"); ?></a></th>
						<th><a href="/admin/user/listing/last_activity-<?php echo $this->tpl_vars['last_activity_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['last_activity_class']; ?>"></i> <?php echo WLang::get("last_connection"); ?></a></th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['users'] as $this->tpl_vars['count'] => $this->tpl_vars['user']):
	$hidden_counter1++; ?>
					<tr>
						<td><?php echo $this->tpl_vars['user']['id']; ?></td>
						<td><strong><?php echo $this->tpl_vars['user']['nickname']; ?></strong></td>
						<td><?php echo $this->tpl_vars['user']['groupe_name']; ?></td>
						<td><?php echo $this->tpl_vars['user']['created_date']; ?></td>
						<td><?php echo $this->tpl_vars['user']['last_activity']; ?></td>
						<td>
							<a href="/admin/user/edit/<?php echo $this->tpl_vars['user']['id']; ?>" class="black" title="<?php echo WLang::get("edit"); ?>"><i class="glyphicon glyphicon-edit"></i></a>
							<?php if ($this->tpl_vars['user']['id'] != $_SESSION['userid']): ?><span title="<?php echo WLang::get("article_delete"); ?>" class="link" data-link-modal="/v/admin/user/delete/<?php echo $this->tpl_vars['user']['id']; ?>" data-modal-container="modal_delete"><i class="glyphicon glyphicon-trash"></i></span><?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if (isset($hidden_counter1) && intval($hidden_counter1) == 0): ?>
					<tr>
						<td colspan="6" class="text-center"><?php echo WLang::get("no_user_found"); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php echo $this->tpl_vars['pagination']; ?>

		</div>

		<div class="col-md-3">
			<div id="search" class=" well well-sm">
				<h4 class="text-center"><?php echo WLang::get("search"); ?></h4>
				<form action="/admin/user/listing" method="get" class="form-horizontal">
					<div class="form-group">
						<label for="nickname" class="control-label col-lg-5"><strong><?php echo WLang::get("nickname"); ?>:</strong></label>
						<div class="col-lg-7">
							<input id="nickname" class="form-control" type="text" name="nickname" value="<?php echo $this->tpl_vars['nickname']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label for="email" class="control-label col-lg-5"><?php echo WLang::get("email"); ?>:</label>
						<div class="col-lg-7">
							<input id="email" class="form-control" type="text" name="email" value="<?php echo $this->tpl_vars['email']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label for="firstname" class="control-label col-lg-5"><?php echo WLang::get("firstname"); ?>:</label>
						<div class="col-lg-7">
							<input id="firstname" class="form-control" type="text" name="firstname" value="<?php echo $this->tpl_vars['firstname']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label for="lastname" class="control-label col-lg-5"><?php echo WLang::get("lastname"); ?>:</label>
						<div class="col-lg-7">
							<input id="lastname" class="form-control" type="text" name="lastname" value="<?php echo $this->tpl_vars['lastname']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label for="groupe" class="control-label col-lg-5"><?php echo WLang::get("group"); ?>:</label>
						<div class="col-lg-7">
							<select id="groupe" class="form-control" name="groupe">
								<option value=""></option>
								<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['groups'] as $this->tpl_vars['group']):
	$hidden_counter1++; ?>
								<option value="<?php echo $this->tpl_vars['group']['id']; ?>"<?php if ($this->tpl_vars['groupe'] == $this->tpl_vars['group']['id']): ?> selected="selected"<?php endif; ?>><?php echo $this->tpl_vars['group']['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<input type="submit" class="btn btn-default btn-block" value="Filtrer" />
				</form>
			</div>
		</div>
	</div>

	<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_delete" aria-hidden="true"></div>
</div>
