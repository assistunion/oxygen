<div class="<?=$this->getComponentClass()?>">
<h1>Exception: <?=$this->getName()?></h1>
<?$this->put_details()?>
<?$this->put_stack_trace()?>
</div>
<?if($this->previous):?>
<?$this->previous->put_view()?>
<?endif?>
