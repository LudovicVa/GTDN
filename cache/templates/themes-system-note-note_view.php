<?php $hidden_counter1 = 0;
foreach((array) $this->tpl_vars['notes_data'] as $this->tpl_vars['note']):
	$hidden_counter1++; ?>
<div class="alert alert-<?php echo $this->tpl_vars['note']['level']; ?>" data-note-code="<?php echo $this->tpl_vars['note']['code']; ?>">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php echo $this->tpl_vars['note']['message']; ?>
</div>
<?php endforeach; ?>