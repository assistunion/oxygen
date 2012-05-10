<div class="oxy-header">
	<?$this->put_logo()?>
	<h1><?$this->getCurrent()->put_title()?></h1>
	<ul class="bread-crumbs">
	<?$breadCrumbs = $this->getPathToCurrent()?>
	<?array_pop($breadCrumbs)?>
	<?foreach($breadCrumbs as $child):?>
	<li><?=$child?></li>
	<?endforeach?>
	</ul>
	<?$this->put_login()?>
</div>