<?o('tr')?>
<?$headers = $args[0]?>
<?foreach($headers as $source=>$def):?>
<?$mode = $def['mode']?>
<?$text = $this->model[$source]?>
<?if($mode === 'link'):?>
<td class="<?=$def['mode']?>"><a href="<?=$this->go()?>"><?=$text?></a></td>
<?elseif($mode === 'edit'):?>
<td class="<?=$def['mode']?>" data-source="<?=htmlspecialchars($source)?>"><?=$text?></td>
<?else:?>
<td class="<?=$def['mode']?>"><?=$text?></td>
<?endif?>
<?endforeach?>