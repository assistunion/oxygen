<?$headers = $args[0]?>
<?foreach($this->section('data') as $child):?>
<?$child->put_as_table_row($headers)?>
<?endforeach?>
