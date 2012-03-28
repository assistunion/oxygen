<table class="trace">
<?foreach($this->getWrapTrace() as $t):?><?$t=(object)$t?>
    <tr>
        <td class="oxygen-exception-func" 
            style="white-space: nowrap;
                   vertical-align: top;
                   ">
        <?if(isset($t->class)):?>
            <?=$t->class?><?=$t->type?><?=$t->function?>()
        <?else:?>
            <?=$t->function?>()
        <?endif?>
        </td>
        <td style="white-space:nowrap;
                   vertical-align:top;
                   ">
        <?if(isset($t->file)):?> in <?=$t->file?><?else:?>no-file<?endif?>
        <?if(isset($t->line)):?>
            <span style="color:#C00000"> at line: <?=$t->line?></span>
        <?endif?>
        </td>
        <td>
        <?if(isset($t->args)):?>
            <ul>
            <?foreach($t->args as $arg):?>
                <li>
                <?if(is_object($arg)||is_array($arg)):?>
                    <span style="cursor:pointer; 
                                 color:#69C;
                                 text-decoration:underline" 
                          onclick="(function(x,y,z){x[y]=x[y]==z?'':z})
                                   (this.nextSibling.style,'display','none')">
                    <?if(is_object($arg)):?>
                        <?=get_class($arg)?>
                    <?elseif(is_array($arg)):?>
                        Array
                    <?endif?>
                    </span><pre style="padding:10px;
                                       border:1px solid #DDC;
                                       font-size:11px;
                                       display:none;
                                       background-color:#FFFFF0"><?print_r($arg)?></pre>
                <?else:?>
                    <span style="color:#008000;
                                 font-weight:bold"><?=htmlspecialchars($arg)?></span>: 
                                 <?=gettype($arg)?>
                <?endif?>
                </li>
            <?endforeach?>
            </ul>
        <?else:?>
            <ul>
                <li>no arguments</li>
            </ul>
        <?endif?>
        </td>
    </tr>
<?endforeach?>
</table>