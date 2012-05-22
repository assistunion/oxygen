<?='<?'?>

    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class <?=$class->oxyName?> <?if($class->extends):?>extends <?=$class->extends?><?endif?> {

        # BEGIN ASSETS:
<?foreach($class->assets as $asset):?>
            <?=$class->templates[$asset->name]->modifier?> function asset_<?=$asset->name?>_<?=$asset->type?>() {<?
                if(!$asset->override):?>}<?else:?>

                if(!isset($this->__assets)) {
                    $this->__assets = $this->scope->assets;
                }
                $this->__assets['<?=$asset->type?>']->add('<?=$asset->absPath?>');
            }
            <?endif?>

<?endforeach?>
        # END ASSETS.

        # BEGIN TEMPLATES:
<?foreach($class->templates as $name=>$method):?>

            // <?=$name?> //

            <?=$method->modifier?> function get_<?=$name?>(<?=$method->args?>) {
                ob_start(); try { $this->put_<?=$name?>(<?=$method->args?>); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            <?=$method->modifier?> function put_<?=$name?>(<?=$method->args?>) {
                $result = include '<?=$method->absPath?>';
                $this->asset_<?=$name?>_css();
                $this->asset_<?=$name?>_js();
                $this->asset_<?=$name?>_less();
                return $result;
            }
<?endforeach?>

        # END TEMPLATES.
    }

    <?if($class->both):?>class <?=$class->name?> extends <?$class->oxyName?> {

    }<?endif?>

<?='?>'?>