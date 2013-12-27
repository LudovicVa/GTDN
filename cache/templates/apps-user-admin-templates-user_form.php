<div class="wity-app wity-app-user wity-action-user-form">
	<form method="post">
		<div class="row">
			<div class="col-md-9 form-horizontal">
				<h2 class="sr-only"><?php if (empty($this->tpl_vars['id'])): ?><?php echo WLang::get("action_add"); ?><?php else: ?><?php echo WLang::get("action_edit"); ?><?php endif; ?></h2>
				<input type="hidden" name="id" value="<?php echo $this->tpl_vars['id']; ?>" />
				
				<div class="form-group">
					<div class="sr-only">
						<label for="nickname"><?php echo WLang::get("nickname"); ?>*</label>
					</div>
					<div class="col-md-12">
						<input id="nickname" class="form-control input-lg" type="text" name="nickname" value="<?php echo $this->tpl_vars['nickname']; ?>"  autocomplete="off" placeholder="<?php echo WLang::get("nickname"); ?>" />
					</div>
				</div>

				<div class="form-group">
					<label for="password" class="control-label col-md-3"><?php echo WLang::get("password"); ?><?php if (empty($this->tpl_vars['id'])): ?>*<?php endif; ?></label>
					<div class="col-md-9">
						<input id="password" class="form-control" type="password" name="password" autocomplete="off" />
						<?php if (!empty($this->tpl_vars['id'])): ?><span class="help-block"><?php echo WLang::get("leave_empty_to_leave_unchanged"); ?></span><?php endif; ?>
					</div>
				</div>

				<div class="form-group">
					<label for="password_conf" class="control-label col-md-3"><em><?php echo WLang::get("confirmation"); ?><?php if (empty($this->tpl_vars['id'])): ?>*<?php endif; ?></em></label>
					<div class="col-md-9">
						<input id="password_conf" class="form-control" type="password" name="password_conf" autocomplete="off"/>
					</div>
				</div>
				<div class="form-group">
					<label for="email" class="control-label col-md-3"><?php echo WLang::get("email"); ?>*</label>
					<div class="col-md-9">
						<input id="email" class="form-control" type="email" name="email" value="<?php echo $this->tpl_vars['email']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="firstname" class="control-label col-md-3"><?php echo WLang::get("firstname"); ?></label>
					<div class="col-md-9">
						<input id="firstname" class="form-control" type="text" name="firstname" value="<?php echo $this->tpl_vars['firstname']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="lastname" class="control-label col-md-3"><?php echo WLang::get("lastname"); ?></label>
					<div class="col-md-9">
						<input id="lastname" class="form-control" type="text" name="lastname" value="<?php echo $this->tpl_vars['lastname']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="groupe" class="control-label col-md-3"><?php echo WLang::get("group"); ?>*</label>
					<div class="col-md-9">
						<script type="text/javascript">
							var user_access = '<?php echo $this->tpl_vars['access']; ?>';
							var group_access = {
							<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['groups'] as $this->tpl_vars['group']):
	$hidden_counter1++; ?>
								<?php echo $this->tpl_vars['group']['id']; ?>: '<?php echo $this->tpl_vars['group']['access']; ?>',
							<?php endforeach; ?>
							};
						</script>
						<select id="groupe" class="form-control" name="groupe" onchange="accessGroup(this.options[this.selectedIndex].value);">
							<option value="0"><?php echo WLang::get("group_default"); ?></option>
						<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['groups'] as $this->tpl_vars['group']):
	$hidden_counter1++; ?>
							<option value="<?php echo $this->tpl_vars['group']['id']; ?>"<?php if ($this->tpl_vars['group']['id'] == $this->tpl_vars['groupe']): ?> selected="selected"<?php endif; ?>><?php echo $this->tpl_vars['group']['name']; ?></option>
						<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<strong><?php echo WLang::get("user_rights"); ?>*</strong>
					<div id="user-access">
						<div class="radio">
							<label><input class="access-type none" type="radio" name="type" value="none"<?php if (empty($this->tpl_vars['access'])): ?> checked="checked"<?php endif; ?> /> <?php echo WLang::get("access_none"); ?></label>
						</div>
						<div class="radio">
							<label><input class="access-type all" type="radio" name="type" value="all"<?php if ($this->tpl_vars['access'] == 'all'): ?> checked="checked"<?php endif; ?> /> <?php echo WLang::get("access_all"); ?></label>
						</div>
						<div class="radio">
							<label><input class="access-type custom" type="radio" name="type" value="custom"<?php if (!empty($this->tpl_vars['access']) && $this->tpl_vars['access'] != 'all'): ?> checked="checked"<?php endif; ?> /> <?php echo WLang::get("access_custom"); ?></label>
						</div>
						<br />
						
						<div class="text-right">
							<a href="javascript:void(0)" class="check-all"><?php echo WLang::get("check_all"); ?></a> - <a href="javascript:void(0)" class="uncheck-all"><?php echo WLang::get("uncheck_all"); ?></a>
						</div>
						<table class="user-rights" cellpadding="0" cellspacing="0">
							<colgroup><col width="25%" /></colgroup>
							<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['admin_apps'] as $this->tpl_vars['app'] => $this->tpl_vars['details']):
	$hidden_counter1++; ?>
							<tr>
								<td><p class="text-center"><strong><?php echo $this->tpl_vars['details']['name']; ?></strong></p></td>
								<td>
									<table class="permissions" cellpadding="0" cellspacing="0">
										<tr>
										<?php $hidden_counter2 = 0;
foreach((array) $this->tpl_vars['details']['permissions'] as $this->tpl_vars['perm']):
	$hidden_counter2++; ?>
											<td><p class="text-center"><label><?php echo ucfirst($this->tpl_vars['perm']); ?><br /><input type="checkbox" name="access[<?php echo $this->tpl_vars['app']; ?>][<?php echo $this->tpl_vars['perm']; ?>]" /></label></p></td>
										<?php endforeach; ?>
										<?php if (isset($hidden_counter2) && intval($hidden_counter2) == 0): ?>
											<td><p class="text-center"><em><?php echo WLang::get("no_permissions"); ?></em></p></td>
										<?php endif; ?>
										</tr>
									</table>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
						<div class="text-right">
							<a href="javascript:void(0)" class="check-all"><?php echo WLang::get("check_all"); ?></a> - <a href="javascript:void(0)" class="uncheck-all"><?php echo WLang::get("uncheck_all"); ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<?php if (!empty($this->tpl_vars['created_date'])): ?>
				<div class="well">
					<ul class="list-unstyled">
						<li><strong><?php echo WLang::get("register_time"); ?>:</strong> <?php echo $this->tpl_vars['created_date']; ?></li>
						<li><strong><?php echo WLang::get("last_connection"); ?>:</strong> <?php echo $this->tpl_vars['last_activity']; ?></li>
					</ul>
				</div>
				<?php endif; ?>
				<div class="well well-sm">
					<div class="checkbox">
						<label><input type="checkbox" name="email_confirmation"<?php if (empty($this->tpl_vars['id'])): ?> checked="checked"<?php endif; ?> /> <?php echo WLang::get("send_email_confirmation"); ?></label>
					</div>
					<button type="submit" class="btn btn-primary btn-lg btn-block"><?php if (empty($this->tpl_vars['id'])): ?><?php echo WLang::get("add_user"); ?><?php else: ?><?php echo WLang::get("edit_user"); ?><?php endif; ?></button>
					<a class="btn btn-default btn-lg btn-block" value="<?php echo WLang::get("cancel"); ?>" href="/admin/user/"><?php echo WLang::get("cancel"); ?></a>
				</div>
			</div>
		</div>
	</form>
</div>
