<?='<?'?>
/*******************************************************

             !!! DO NOT CHANGE THIS FILE !!!            
             
     This file is generated on basis of schema file     
     and will be overwritten every time schema changes
     If you want add or override any functionality 
     please edit '<?=$this->getModelName()?>' instead
     
     
********************************************************/     

class <?=$this->getModelBaseName()?> extends <?=$this->getModelParent()?> {
    private static $__fields = array();
    public static function fileds() {
        return self::$__fields;
    }
    const CLAZZ = '<?=$this->getModelName()?>';
    <?foreach($this->getFields() as $field):?>
    public static $<?=$field->name?> = null;
    <?endforeach?>
    public static function __class_construct() {
        $scope = Oxygen_Scope::root();
        <?foreach($this->getFields() as $field):?>
        self::$<?=$field->name?> =
        self::$__fields['<?=$field->name?>'] = $scope-><?=get_class($field)?>(
            self::CLAZZ,
            '<?=$field->name?>',
            <?=str_replace(array("\n",",)","(  "),array('',')','('),var_export($field->yaml,true))?> //
        );
        <?endforeach?>//
    }
    <?foreach($this->getFields() as $field):?>
    public function <?=$field->name?>() {
        switch(func_num_args()) {
        case 0: return self::$<?=$field->name?>[$this];
        case 1: return self::$<?=$field->name?>[$this] = func_get_arg(0);
        case 2: $this->throwException(self::WRONG_ARGUMENT_COUNT);
        }
    }
    <?endforeach?>
}    
<?='?>'?>