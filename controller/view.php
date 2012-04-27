<?print_r($this->args)?>
<ul>
<li><?$this['.']->put_as_current()?></li>
<?if(isset($this['..'])):?>
<li><?$this['..']->put_as_parent()?></li>
<?endif?>
<?foreach($this as $route=>$child):?>
<li><?=$child->put_as_child()?></li>
<?endforeach?>
</ul>