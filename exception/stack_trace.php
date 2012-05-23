<table class="trace">
<?$trace=$this->getWrapTrace()?>
<?if(isset($trace[6]) && $trace[6]['function'] ==='__assert' && !$trace[6]['args'][0]):?>
<?$countdown=8?>
<?else:?>
<?$countdown=0?>
<?endif?>
<?foreach($trace as $n=>$t):?><?$t=(object)$t?>
<?if(--$countdown>0)continue?>
<?if(preg_match("/^(get|put|throw|new)_$/", $t->function) && $trace[$n+1]['function']=='__call'):?>
<?continue?>
<?endif?>
<?if($t->function=='__call')continue?>
<tr>
<td><?if(isset($t->class)):?><?=$t->class?><?=$t->type?><?=$t->function?>()<?else:?><?=$t->function?>()<?endif?></td>
<td><?if(isset($t->file)):?> in <?=$t->file?><?else:?>no-file<?endif?>
<?if(isset($t->line)):?><span class="line"> at line: <?=$t->line?></span><?endif?>
</td>
<td>
<ul class="arguments">
<?if(isset($t->args)):?>
<?foreach($t->args as $arg):?>
<?if(is_object($arg)||is_array($arg)):?>
<li class="complex-argument">
<span class="type"><?if(is_object($arg)):?><?=get_class($arg)?><?elseif(is_array($arg)):?>Array<?endif?></span>
<pre class="value"><?//print_r($arg)?></pre>
</li>
<?else:?>
<li class="simple-argument">
<span class="value"><?=htmlspecialchars($arg)?></span>:
<span class="type"><?=gettype($arg)?></span>
</li>
<?endif?>
<?endforeach?>
<?else:?>
<li>no arguments</li>
<?endif?>
</ul>
</td>
</tr>
<?endforeach?>
</table>