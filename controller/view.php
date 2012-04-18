<ul>
<?foreach($this as $route=>$child):?>
<li><a href="<?=$child->go()?>"><?=$route?></a></li>
<?endforeach?>
</ul>