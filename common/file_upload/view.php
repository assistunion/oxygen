<?o(array('rpc'=>$this->go()))?>
<div class="fileScope">Upload for <?=$this->fileScope?></div>
<form class="upload std-form" enctype="multipart/form-data" method="post" action="<?=$this->go()?>">
	<label><span>Format:</span><select name="format">
		<?foreach($this->getFileFormats() as $ff):?>
		<option value="<?=$ff->id?>"><?=$ff?></option>
		<?endforeach?>
	</select></label>
	<label><span>Select file:</span><input type="file" name="file"/></label>
    <a class="test">Test</a>
	<input type="submit" name="upload" value="Upload"/>
</form>
<?if($this->child):?>
	<?$this->put_selected_file($this->child)?>
	<?$this->child->put_view()?>
<?else:?>
	<?$this->put_history()?>
<?endif?>	

