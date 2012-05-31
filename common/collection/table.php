<?o('table', array('offset'=>0,'limit'=>0,'filter'=>'','order'=>array('test')))?>
<?$h = $this->getHeaders()?>
<?$i=0;$more=false?>
<thead>
<?$this->put_table_header($h)?>
</thead>
<tfoot>
<tr><td colspan="<?=count($h)?>"><img class="loader" src="<?=$this->urlFor('ajax-loader.gif')?>"></div></td></tr>
</tfoot>
<tbody>
</tbody>

