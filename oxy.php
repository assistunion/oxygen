<?='<?'?>

    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class <?=$class->oxyName?> <?if($class->extends):?>extends <?=$class->extends?><?endif?> {

        # BEGIN ASSETS:
<?foreach($class->assets as $asset):?>
            <?=$class->views[$asset->name]->access?> function asset_<?=$asset->name?>_<?=$asset->type?>() {<?
                if(!$asset->override):?>}<?else:?>

                if(!isset($this->__assets)) {
                    $this->__assets = $this->scope->assets;
                }
                $this->__assets['<?=$asset->type?>']->add('<?=$asset->absPath?>');
            }
            <?endif?>

<?endforeach?>
        # END ASSETS.

        # BEGIN VIEWS:
<?foreach($class->views as $name=>$method):?>
            <?$args = preg_replace('/(^|,)/','\$\\1',implode(',',array_keys($method->args)))?>
            <?if($args=='$')$args=''?>

            /** GET: <?=$method->info?>
            <?foreach ($method->args as $arg => $type):?>

                @param <?=$type?> <?=$arg?>
            <?endforeach?>

            */
            <?=$method->access?> function get_<?=$name?>(<?=$args?>) {
                ob_start(); try { $this->put_<?=$name?>(<?=$args?>); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: <?=$method->info?>
            <?foreach ($method->args as $arg => $type):?>

                @param <?=$type?> <?=$arg?>
            <?endforeach?>

            */
            <?=$method->access?> function put_<?=$name?>(<?=$args?>) {
                $result = include '<?=$method->absPath?>';
                $this->asset_<?=$name?>_css();
                $this->asset_<?=$name?>_js();
                $this->asset_<?=$name?>_less();
                return $result;
            }
<?endforeach?>

        # END VIEWS.
    }

    <?if($class->both):?>class <?=$class->name?> extends <?$class->oxyName?> {

    }<?endif?>

<?='?>'?>