<div class="oxy-menu">
	<ul class="first-level">
		<?foreach($this as $child):?>
		<?if($child->isActive):?>
			<li class="expanded">
				<a class="title" href="<?=$child->go()?>"><?$child->put_icon()?><?=$child?></a>
				<?if(count($child)>0):?>
				<ul class="second-level">
					<?foreach($child as $subchild):?>
					<?if($subchild->isActive):?>
						<li class="expanded">
							<a class="title" href="<?=$subchild->go()?>"><?$subchild->put_icon()?><?=$subchild?></a>
						</li>
					<?else:?>
						<li class="collapsed">
							<a class="title" href="<?=$subchild->go()?>"><?$subchild->put_icon()?><?=$subchild?></a>
						</li>
					<?endif?>
					<?endforeach?>
				</ul>
				<?endif?>
			</li>
		<?else:?>
			<li class="collapsed">
				<a class="title" href="<?=$child->go()?>"><?$child->put_icon()?><?=$child?></a>
			</li>
		<?endif?>
		<?endforeach?>
	</ul>
</div>