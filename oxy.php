<?='<?'?>

    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class <?=$class->oxyName?> <?if($class->extends):?>extends <?=$class->extends?><?endif?> {

        public static $__oxygen_path = '<?=$class->path?>';
        public static $__lastMetaModified = <?=time()?>;

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
            <?=$asset->access?> function <?=$asset->method?>($class) {<?
                if($asset->file === false):?>}<?else:?>

                if(!isset($this->__assets)) {
                    $this->__assets = &$this->scope->assets;
                }
                $name = $class . '-<?=$asset->name?>';
                if (!isset($this->__assets['<?=$asset->type?>'][$name])) {
                    $this->__assets['<?=$asset->type?>'][$name] = array(
                        'type'  => '<?=$asset->type?>',
                        'ext'   => '<?=$asset->ext?>',
                        'name'  => '<?=$asset->name?>',
                        'path'  => self::$__oxygen_path,
                        'class' => $class
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
                    $result = include OXYGEN_ROOT . '<?=$class->path?>/<?=$name?>.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
<?foreach($method->assets as $asset):?>
                    $this-><?=$asset->method?>($class);
<?endforeach?>
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }
<?endforeach?>

        # END VIEWS.

    }

    <?if($class->both):?>class <?=$class->name?> extends <?$class->oxyName?> {

    }<?endif?>

<?='?>'?>