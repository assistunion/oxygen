<div class="oxygen-exception" style="font-family:Verdana;font-size:0.8em">
<h1>Exception: <?=$this->getName()?></h1>
<?$this->put->details()?>
<?$this->put->stack_trace()?>
</div>
<?if($this->previous):?>
<?$this->previous->put->view()?>
<?endif?>
