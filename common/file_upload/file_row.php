<?$file = $args[0]?>
<?$f = $file->model?>
<tr>
	<td><?=date(DATE_RFC822,$f->dt_created)?></td>
	<td><a href="<?=$file->go()?>"><?=$f->file_name?></a></td>
	<td><?=$f->uploaded_by?></td>
	<td class="<?=$f->upload_result?>"><?=$f->upload_result?></td>
	<td class="<?=$f->current_result?>"><?=$f->current_result?></td>
	<td><?=date(DATE_RFC822,$f->dt_updated)?></td>
	<td><?=$f->updated_by?></td>
</tr>
