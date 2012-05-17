<table>
<?$this->put_table_header()?>
<tbody>
<?foreach($this as $child):?>
<?$child->put_as_table_row()?>
<?endforeach?>
</tbody>
</table>
