<script type="text/javascript" src="/libraries/requirejs/require.js"></script>
<div class="wity-app wity-app-user wity-action-listing">
	<div class="row">
		<div class="col-md-12">						
			<table class="table table-hover">
				<thead>
					<tr>
						<th><a href="/admin/merchantmanagement/listing/id-<?php echo $this->tpl_vars['id_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['id_class']; ?>"></i> #</a></th>
						<th><a href="/admin/merchantmanagement/listing/nickname-<?php echo $this->tpl_vars['nickname_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("nickname"); ?></a></th>
						<th><a href="/admin/merchantmanagement/listing/email-<?php echo $this->tpl_vars['nickname_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("email"); ?></a></th>
						<th><a href="/admin/merchantmanagement/listing/created_date-<?php echo $this->tpl_vars['created_date_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['created_date_class']; ?>"></i> <?php echo WLang::get("register_time"); ?></a></th>
						<th><a href="/admin/merchantmanagement/listing/last_activity-<?php echo $this->tpl_vars['last_activity_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['last_activity_class']; ?>"></i> <?php echo WLang::get("last_connection"); ?></a></th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['users'] as $this->tpl_vars['count'] => $this->tpl_vars['user']):
	$hidden_counter1++; ?>
					<tr class="<?php if (empty($this->tpl_vars['user']['addresses'])): ?> danger<?php endif; ?>">
						<td><span data-toggle="collapse" data-target="#row<?php echo $this->tpl_vars['user']['id']; ?>" class="black accordion-toggle" title="<?php echo WLang::get("edit"); ?>"><i class="glyphicon glyphicon-plus"></i></span></td>
						<td><strong>
							<a href="#" class="editable-data" data-name="name" data-type="text" data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>" data-url="/admin/merchantmanagement/edit/">	
							<?php echo $this->tpl_vars['user']['name']; ?>
							</a>
						</strong></td>
						<td>
							<a href="#" class="editable-data" data-name="email" data-type="text" data-pk="<?php echo $this->tpl_vars['user']['id']; ?>" data-url="/admin/merchantmanagement/edit/">	
							<?php echo $this->tpl_vars['user']['email']; ?>
							</a>
						</td>
						<td><?php echo $this->tpl_vars['user']['created_date']; ?></td>
						<td><?php echo $this->tpl_vars['user']['last_activity']; ?></td>
						<td>
							<a href="/admin/user/edit/<?php echo $this->tpl_vars['user']['id']; ?>" class="black" title="<?php echo WLang::get("edit"); ?>"><i class="glyphicon glyphicon-edit"></i></a>
							<?php if ($this->tpl_vars['user']['id'] != $_SESSION['userid']): ?><span title="<?php echo WLang::get("article_delete"); ?>" class="link" data-link-modal="/v/admin/user/delete/<?php echo $this->tpl_vars['user']['id']; ?>" data-modal-container="modal_delete"><i class="glyphicon glyphicon-trash"></i></span><?php endif; ?>
							
						</td>				
					</tr>
					<tr>
						<td colspan="6" class="hiddenRow">
							<div class="row accordian-body collapse" id="row<?php echo $this->tpl_vars['user']['id']; ?>" style="background-color:#fff"> 
								<div class="col-md-7">
									<h4 style="text-align:center">Addresses</h4>
									<div id="msg_address_<?php echo $this->tpl_vars['user']['id']; ?>" class="alert hide fade in"></div>
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th width="20%"><i class="<?php echo $this->tpl_vars['id_class']; ?>"></i> <?php echo WLang::get("name"); ?></th>
												<th width="30%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("address"); ?></th>
												<th width="30%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("opening_hours"); ?></th>
												<th colspan="2" width="20%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("tel"); ?></th>
											</tr>
										</thead>						
										<tbody>
										<?php $hidden_counter2 = 0;
foreach((array) $this->tpl_vars['user']['addresses'] as $this->tpl_vars['count'] => $this->tpl_vars['address']):
	$hidden_counter2++; ?>	
										<tr>
											<td><b>
											<a href="#" class="editable-data-popup" data-name="address_name" data-type="text" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['address_name']; ?></a>
											</b></td>
											<td><a href="#" class="editable-data-popup" data-name="address" data-type="textarea" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['address']; ?></a>
											</td>
											<td><a href="#" class="editable-data-popup" data-name="opening_hours" data-type="textarea" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['opening_hours']; ?></a></td>
											<td colspan="2"><a href="#" class="editable-data-popup" data-name="tel" data-type="text" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['tel']; ?></a></td>
										</tr>
										<?php endforeach; ?>
										</tbody>
										<tr id="row_new_address_<?php echo $this->tpl_vars['user']['id']; ?>" >
											<td>
												<a href="#" class="new_address_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="address_name" data-type="text" data-url="/admin/merchantmanagement/edit/" data-verif="required"></a>
											</td>
											<td>
												<a href="#" class="new_address_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="address" data-type="textarea" data-url="/admin/merchantmanagement/edit/" data-verif="required"></a>
											</td>
											<td>
												<a href="#" class="new_address_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="opening_hours" data-type="textarea" data-url="/admin/merchantmanagement/edit/" data-verif="required"></a>
											</td>
											<td>
												<a href="#" class="new_address_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="tel" data-type="textarea" data-url="/admin/merchantmanagement/edit/" data-verif="required"></a>
											</td>
											<td>
												<button class="btn btn-primary" id="new_address_submit_<?php echo $this->tpl_vars['user']['id']; ?>" data-url="/admin/merchantmanagement/add/address/<?php echo $this->tpl_vars['user']['id_merchant']; ?>">Add</button>
											</td>
										</tr>	
										<script>
											require(['jquery', 'bootstrap3-editable', '../apps/merchantmanagement/admin/js/script'], function($) {
												$.fn.editable.defaults.mode = 'inline';
												$(document).ready(function() {
													declare(<?php echo $this->tpl_vars['user']['id']; ?>, 'address');
												});
											});
										</script>
									</table>
								</div>
								<div class="col-md-5">
									<h4 style="text-align:center">Emails</h4>
									<div id="msg_email_<?php echo $this->tpl_vars['user']['id']; ?>" class="alert hide fade in"></div>
									<table class="table table-striped table-hover" id="table_email">
										<thead>
											<tr>
												<th width="40%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("name"); ?></th>
												<th width="60%" colspan="2"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("email"); ?></th>
											</tr>
										</thead>						
										<tbody>
											<?php $hidden_counter2 = 0;
foreach((array) $this->tpl_vars['user']['contact_email'] as $this->tpl_vars['count'] => $this->tpl_vars['mail']):
	$hidden_counter2++; ?>	
											<tr>
												<td>
													<a href="#" class="editable-data-popup" data-name="contacts_name" data-type="text" data-pk="<?php echo $this->tpl_vars['mail']['id_email']; ?>" data-url="/admin/merchantmanagement/edit/">
														<?php echo $this->tpl_vars['mail']['email_name']; ?>
													</a>
												</td>
												<td colspan="2">
													<a href="#" class="editable-data-popup" data-name="contacts_email" data-type="text" data-pk="<?php echo $this->tpl_vars['mail']['id_email']; ?>" data-url="/admin/merchantmanagement/edit/">	
														<?php echo $this->tpl_vars['mail']['email']; ?>
													</a>
												</td>
											</tr>
											<?php endforeach; ?>	
											<tr id="row_new_email_<?php echo $this->tpl_vars['user']['id']; ?>" >
												<td>
													<a href="#" class="new_email_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="contacts_name" data-type="text" data-url="/admin/merchantmanagement/edit/" data-verif="required">
													</a>
												</td>
												<td width="40%">
													<a href="#" class="new_email_<?php echo $this->tpl_vars['user']['id']; ?>" data-name="contacts_email" data-type="text" data-url="/admin/merchantmanagement/edit/" data-verif="email">	
													</a>
												</td>
												<td>
													<button class="btn btn-primary" id="new_email_submit_<?php echo $this->tpl_vars['user']['id']; ?>" data-url="/admin/merchantmanagement/add/contacts/<?php echo $this->tpl_vars['user']['id_merchant']; ?>">Add</button>
												</td>
											</tr>	
											<script>
												require(['jquery', 'bootstrap3-editable', '../apps/merchantmanagement/admin/js/script'], function($) {
													$.fn.editable.defaults.mode = 'inline';
													$(document).ready(function() {
														declare(<?php echo $this->tpl_vars['user']['id']; ?>, 'email');
													});
												});
											</script>
										</tbody>
									</table>
								</div>
							</div>
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
	</div>

	<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_delete" aria-hidden="true"></div>
</div>
</div>


