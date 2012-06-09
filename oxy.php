<?='<?'?>

    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class <?=$class->oxyName?> <?if($class->extends):?>extends <?=$class->extends?><?endif?> {

        private static $static = null;
        public static $name = '<?=$class->name?>';
        public static function __getClass() {
            if (self::$static === null) {
                self::$static = new Oxygen_Class('<?=$class->name?>');
            }
            return self::$static;
        }

        public static function __getParentClass() {
<?if($class->extends):?>
            return <?=$class->extends?>::__getClass();
<?else:?>
            return null;
<?endif?>        
        }

        # SCOPE:
        <?if(is_array($class->scope)):?>

        public static $__oxygenScope = array(
<?foreach($class->scope as $key => $value):?>
            <?=var_export($key)?> => <?=var_export($value)?>,
<?endforeach?>
        );
        <?else:?>
        public static $__oxygenScope = <?var_export($class->scope)?>;
        <?endif?>

        # BEGIN ASSETS:
<?foreach($class->assets as $asset):?>
            public static $__defines_<?=$asset->type?>_<?=$asset->name?> = '<?=$class->name?>';
<?endforeach?>

<?foreach($class->assets as $asset):?>
            <?=$asset->access?> function asset_<?=$asset->name?>_<?=$asset->type?>($path, $css, $class, $last) {<?
                if(!$asset->override):?>}<?else:?>

                if(!isset($this->__assets)) {
                    $this->__assets = &$this->scope->assets;
                }
                $name = $css . '-<?=$asset->name?>';
                if (!isset($this->__assets['<?=$asset->type?>'][$name])) {
                    $this->__assets['<?=$asset->type?>'][$name] = array(
                        'source' => OXYGEN_ROOT . '<?=addslashes($asset->relPath)?>',
                        'destination' => OXYGEN_ASSET_ROOT . '/<?=$asset->type?>' . $path . '/<?=$asset->baseName?>',
                        'class' => $class,
                        'name' => '<?=$asset->name?>',
                        'last' => $last
                    );
                }
            }
            <?endif?>

<?endforeach?>
        # END ASSETS.

        # BEGIN VIEWS:
<?foreach($class->views as $name=>$method):?>
            <?$args = preg_replace('/(^|,)/','\\1\$',implode(',',array_keys($method->args)))?>
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
                try {
                    Oxygen::push($this,'<?=$name?>');
                    $result = include OXYGEN_ROOT . '<?=$method->relPath?>';
                    Oxygen::closeAll();
<?if(count($method->assets)):?>
                    $class = $this->__getClass();
                    $last = $this->__lastMetaModified();
<?foreach($method->assets as $asset):?>
                    $this->asset_<?=$asset->name?>_<?=$asset->type?>(
                        '<?=$method->path?>', 
                        'css-<?=$class->name?>',
                        $class,
                        $last
                    );
<?endforeach?>                
<?endif?>                
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }
<?endforeach?>

        # END VIEWS.

        public function __lastMetaModified() {
            return <?=time()?>;
        }
    }

    <?if($class->both):?>class <?=$class->name?> extends <?$class->oxyName?> {

    }<?endif?>

<?='?>'?>