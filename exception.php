<?o()?>
<h1>Exception: <?=$get_class($ex)?></h1>
<div class="details"><b>Details:</b> <?=$this->getMessage()?></div>
<?$this->put_exception_trace($ex->getTrace())?>
</div>
<?//TODO: Add Previous ?>