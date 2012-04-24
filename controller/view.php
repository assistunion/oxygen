<?print_r($this->args)?>
<ul>
<li><a href="<?=$this['.']->go()?>">-- CURRENT --</a></li>
<?if(isset($this['..'])):?>
<li><a href="<?=$this['..']->go()?>">-- PARENT --</a></li>
<?endif?>
<?foreach($this as $route=>$child):?>
<li><a href="<?=$child->go()?>"><?=$route?></a></li>
<?endforeach?>
</ul>