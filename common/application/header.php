<?o('td[colspan="2"]')?>
<?$this->put_logo()?>
<?$current = $this->getCurrent()?>
<h1><?$current->put_title()?></h1>
<?$this->put_login()?>
<ul class="bread-crumbs">
<?$breadCrumbs = $this->getPathToCurrent()?>
<?array_pop($breadCrumbs)?>
<?foreach($breadCrumbs as $child):?>
<li><a href="<?=$child->go()?>"><?=$child?></a></li>
<?endforeach?>
</ul>

