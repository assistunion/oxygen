<table class="trace">
<?foreach($this->getWrapTrace() as $t):?><?$t=(object)$t?>
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
<pre class="value"><?print_r($arg)?></pre>
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