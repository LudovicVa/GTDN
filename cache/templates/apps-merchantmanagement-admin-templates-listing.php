<div class="wity-app wity-app-user wity-action-listing">
	<div class="row">
		<div class="col-md-12">		
			<div class="alert fade in" style="display:none"></div>	
			<div class="modal fade" id="confirmation_window" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel"><?php echo WLang::get("confirmation_title"); ?></h4>
				  </div>
				  <div class="modal-body">
					<?php echo WLang::get("confirmation_body"); ?>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo WLang::get("cancel"); ?></button>
					<button type="button" id="confirm" class="btn btn-primary" data-dismiss="modal"><?php echo WLang::get("confirm"); ?></button>
				  </div>
				</div><!-- /.modal-content -->
			</div>			
			<table class="table table-hover">
				<thead>
					<tr>
						<th width="1%">#</th>
						<th width="15%"><a href="/m/admin/merchantmanagement/listing/nickname-<?php echo $this->tpl_vars['nickname_sort']; ?>/"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("nickname"); ?></a></th>
						<th><a href="/m/admin/merchantmanagement/listing/name-<?php echo $this->tpl_vars['name_sort']; ?>/"><i class="<?php echo $this->tpl_vars['name_class']; ?>"></i> <?php echo WLang::get("name"); ?></a></th>
						<th width="20%"><a href="/m/admin/merchantmanagement/listing/email-<?php echo $this->tpl_vars['email_sort']; ?>/"><i class="<?php echo $this->tpl_vars['email_class']; ?>"></i> <?php echo WLang::get("email"); ?></a></th>
						<th width="15%">Password</th>
						<th colspan="2" width="17%"><a href="/m/admin/merchantmanagement/listing/last_activity-<?php echo $this->tpl_vars['last_activity_sort']; ?>/<?php echo $this->tpl_vars['subURL']; ?>"><i class="<?php echo $this->tpl_vars['last_activity_class']; ?>"></i> <?php echo WLang::get("last_connection"); ?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['users'] as $this->tpl_vars['count'] => $this->tpl_vars['user']):
	$hidden_counter1++; ?>
					<tr>
						<td><span data-toggle="collapse" data-target="#row<?php echo $this->tpl_vars['user']['id']; ?>" class="black accordion-toggle" title="<?php echo WLang::get("edit"); ?>"><i class="glyphicon glyphicon-plus"></i></span></td>
						<td><strong>
							<a href="#" class="editable-data" data-name="nickname" data-type="text" data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>" data-url="/m/admin/merchantmanagement/edit/"  data-verif="required">	
							<?php echo $this->tpl_vars['user']['nickname']; ?>
							</a>
						</strong></td>
						<td><strong>
							<a href="#" class="editable-data" data-name="name" data-type="text" data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>" data-url="/m/admin/merchantmanagement/edit/"  data-verif="required">	
							<?php echo $this->tpl_vars['user']['name']; ?>
							</a>
						</strong></td>
						<td>
							<a href="#" class="editable-data" data-name="email" data-type="text" data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>" data-url="/m/admin/merchantmanagement/edit/"  data-verif="email">	
							<?php echo $this->tpl_vars['user']['email']; ?>
							</a>
						</td>
						<td>
							<a href="#" class="editable-data" data-type="password" data-name="password"  data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>" data-url="/m/admin/merchantmanagement/edit/" data-verif="password">********</a>
						</td>
						<td><?php echo $this->tpl_vars['user']['last_activity']; ?></td>
						<td>
							<?php if ($this->tpl_vars['user']['id'] != $_SESSION['userid']): ?><span class="delete_merchant" data-url="/m/admin/merchantmanagement/delete/" data-name="merchant" data-pk="<?php echo $this->tpl_vars['user']['id_merchant']; ?>"><i class="glyphicon glyphicon-trash"></i></span><?php endif; ?>
							
						</td>				
					</tr>
					<tr>
						<td colspan="6" class="hiddenRow">
							<div class="row accordian-body collapse" id="row<?php echo $this->tpl_vars['user']['id']; ?>" style="background-color:#fff"> 
								<div class="col-md-7">
									<h4 style="text-align:center">Addresses</h4>
									<div class="alert fade in" style="display:none"></div>
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
												<a href="#" class="editable-data" data-name="address_name" data-type="text" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/m/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['address_name']; ?></a>
												</b></td>
												<td><a href="#" class="editable-data" data-name="address" data-type="textarea" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/m/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['address']; ?></a>
												</td>
												<td><a href="#" class="editable-data" data-name="opening_hours" data-type="textarea" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/m/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['opening_hours']; ?></a></td>
												<td><a href="#" class="editable-data" data-name="tel" data-type="text" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>" data-url="/m/admin/merchantmanagement/edit/"><?php echo $this->tpl_vars['address']['tel']; ?></a></td>
												<td><span class="delete_row" data-url="/m/admin/merchantmanagement/delete/" data-name="address" data-pk="<?php echo $this->tpl_vars['address']['id_address']; ?>"><i class="glyphicon glyphicon-trash"></i></span></td>
											</tr>
											<?php endforeach; ?>
											<tr class="new-row" data-name="address" data-id="<?php echo $this->tpl_vars['user']['id']; ?>">
												<td>
													<b>
													<a href="#" class="add" data-name="address_name" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
													</b>
												</td>
												<td>
													<a href="#" class="add" data-name="address" data-type="textarea" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td>
													<a href="#" class="add" data-name="opening_hours" data-type="textarea" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td>
													<a href="#" class="add" data-name="tel" data-type="textarea" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td><span class="delete_row hide" data-url="/m/admin/merchantmanagement/delete/" data-name="address"><i class="glyphicon glyphicon-trash"></i></span>
													<span class="submit" data-url="/m/admin/merchantmanagement/add/address/<?php echo $this->tpl_vars['user']['id_merchant']; ?>"><i class="glyphicon glyphicon-ok"></i></span>
												</td>
											</tr>	
											</tbody>
									</table>
								</div>
								<div class="col-md-5">
									<h4 style="text-align:center">Emails</h4>
									<div class="alert fade in" style="display:none"></div>
									<table class="table table-striped table-hover" id="table_email">
										<thead>
											<tr>
												<th width="30%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("name"); ?></th>
												<th width="70%" colspan="2"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("email"); ?></th>
											</tr>
										</thead>						
										<tbody>
											<?php $hidden_counter2 = 0;
foreach((array) $this->tpl_vars['user']['contact_email'] as $this->tpl_vars['count'] => $this->tpl_vars['mail']):
	$hidden_counter2++; ?>	
											<tr>
												<td>
													<a href="#" class="editable-data" data-name="contacts_name" data-type="text" data-pk="<?php echo $this->tpl_vars['mail']['id_email']; ?>" data-url="/m/admin/merchantmanagement/edit/">
														<?php echo $this->tpl_vars['mail']['email_name']; ?>
													</a>
												</td>
												<td>
													<a href="#" class="editable-data" data-name="contacts_email" data-type="text" data-pk="<?php echo $this->tpl_vars['mail']['id_email']; ?>" data-url="/m/admin/merchantmanagement/edit/">	
														<?php echo $this->tpl_vars['mail']['email']; ?>
													</a>
												</td>
												<td>
													<span class="delete_row" data-url="/m/admin/merchantmanagement/delete/" data-name="email" data-pk="<?php echo $this->tpl_vars['mail']['id_email']; ?>"><i class="glyphicon glyphicon-trash"></i></span>
												</td>
											</tr>
											<?php endforeach; ?>	
											<tr class="new-row" data-name="email" data-id="<?php echo $this->tpl_vars['user']['id']; ?>">
												<td>
													<a href="#" class="add" data-name="contacts_name" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required">
													</a>
												</td>
												<td>
													<a href="#" class="add" data-name="contacts_email" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="email">	
													</a>
												</td>
												<td>
													<span class="delete_row hide" data-url="/m/admin/merchantmanagement/delete/" data-name="email"><i class="glyphicon glyphicon-trash"></i></span>
													<span class="submit" data-url="/m/admin/merchantmanagement/add/contacts/<?php echo $this->tpl_vars['user']['id_merchant']; ?>"><i class="glyphicon glyphicon-ok"></i></span>
												</td>
											</tr>	
										</tbody>
									</table>
								</div>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
					<tr class="new-row" data-name="merchant" data-id="<?php echo $this->tpl_vars['user']['id']; ?>" id="row_new_email_<?php echo $this->tpl_vars['user']['id']; ?>">
						<td><span data-toggle="collapse" class="black accordion-toggle" title="<?php echo WLang::get("edit"); ?>"><i class="glyphicon glyphicon-plus"></i></span></td>
						<td><strong>
							<a href="#" class="add" data-name="nickname" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required">	
							</a>
						</strong></td>
						<td><strong>
							<a href="#" class="add" data-name="name" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required">	
							</a>
						</strong></td>
						<td>
							<a href="#" class="add" data-name="email" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="email">	
							</a>
						</td>
						<td>
							<a href="#" class="add" data-name="password" data-type="password" data-url="/m/admin/merchantmanagement/edit/" data-verif="password"></a>
						</td>
						<td></td>
						<td>
							<span class="delete_merchant hide" data-url="/m/admin/merchantmanagement/delete/" data-name="merchant"><i class="glyphicon glyphicon-trash"></i></span>
							<span class="submit" data-url="/m/admin/merchantmanagement/add/merchant/"><i class="glyphicon glyphicon-ok"></i></span>							
						</td>
					</tr>
					<tr class="new-row-collapse">
						<td colspan="6" class="hiddenRow">
							<div class="row accordian-body collapse" style="background-color:#fff"> 
								<div class="col-md-7">
									<h4 style="text-align:center">Addresses</h4>
									<div class="alert fade in" style="display:none"></div>
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th width="20%"><i class="<?php echo $this->tpl_vars['id_class']; ?>"></i> <?php echo WLang::get("name"); ?></th>
												<th width="30%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("address"); ?></th>
												<th width="25%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("opening_hours"); ?></th>
												<th colspan="2" width="25%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("tel"); ?></th>
											</tr>
										</thead>						
										<tbody>
											<tr class="new-row" data-name="address" data-id="<?php echo $this->tpl_vars['user']['id']; ?>">
												<td>
													<b>
													<a href="#" class="add" data-name="address_name" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
													</b>
												</td>
												<td>
													<a href="#" class="add" data-name="address" data-type="textarea" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td>
													<a href="#" class="add" data-name="opening_hours" data-type="textarea" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td>
													<a href="#" class="add" data-name="tel" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required"></a>
												</td>
												<td><span class="delete_row hide" data-url="/m/admin/merchantmanagement/delete/" data-name="address"><i class="glyphicon glyphicon-trash"></i></span>
													<span class="submit" data-url="/m/admin/merchantmanagement/add/address/"><i class="glyphicon glyphicon-ok"></i></span>
												</td>
											</tr>	
											</tbody>
									</table>
								</div>
								<div class="col-md-5">
									<h4 style="text-align:center">Emails</h4>
									<div class="alert fade in" style="display:none"></div>
									<table class="table table-striped table-hover" id="table_email">
										<thead>
											<tr>
												<th width="30%"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("name"); ?></th>
												<th width="70%" colspan="2"><i class="<?php echo $this->tpl_vars['nickname_class']; ?>"></i> <?php echo WLang::get("email"); ?></th>
											</tr>
										</thead>						
										<tbody>	
											<tr class="new-row" data-name="email" data-id="<?php echo $this->tpl_vars['user']['id']; ?>">
												<td>
													<a href="#" class="add" data-name="contacts_name" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="required">
													</a>
												</td>
												<td>
													<a href="#" class="add" data-name="contacts_email" data-type="text" data-url="/m/admin/merchantmanagement/edit/" data-verif="email">	
													</a>
												</td>
												<td>
													<span class="delete_row hide" data-url="/m/admin/merchantmanagement/delete/" data-name="email"><i class="glyphicon glyphicon-trash"></i></span>
													<span class="submit" data-url="/m/admin/merchantmanagement/add/contacts/"><i class="glyphicon glyphicon-ok"></i></span>
												</td>
											</tr>	
										</tbody>
									</table>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php echo $this->tpl_vars['pagination']; ?>
		</div>
	</div>

	<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_delete" aria-hidden="true"></div>
	
</div>
</div>