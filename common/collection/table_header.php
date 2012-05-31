<?$headers = $args[0]?>
<tr>
    <?foreach($headers as $source => $header):?>
    <th class="<?=$header['mode']?>"><?=$header['name']?></th>
    <?endforeach?>
</tr>