<?
    class Oxygen {

        private static $stack = array();
        private static $sp = 0;

        public static function push($call){
            self::$stack[self::$sp++] = $call;
        }

        public static function pop() {
            return self::$stack[--self::$sp];
        }

        public static function open($tag = 'div'){
            $call = self::$stack[self::$sp-1];
            $call->stack[$call->sp++] = $tag;
            echo '<' . $tag . ' class="' . self::getCssClass() . '">';
        }

        public static function cssClassFor($class, $name) {
            return 'css-' . $class . '-' . $name;
        }

        public static function close() {
            $call = self::$stack[self::$sp-1];
            $tag = $call->stack[--$call->sp];
            echo '</' . $tag . '>';
        }

        public static function closeAll() {
            $call = self::$stack[self::$sp-1];
            while ($call->sp > 0) {
                $tag = $call->stack[--$call->sp];
                echo '</' . $tag . '>';
            }
        }

        public static function getCssClass() {
            if(self::$sp === 0) throw new Exception(
                'getCssClass() call is valid only within template code'
            );
            $call = self::$stack[self::$sp-1];
            if ($call->component === false) {
                return $call->component = self::cssClassFor(
                    $call->class,
                    $call->name
                );
            } else {
                return $call->component;
            }
        }

        public static function Generate() {
            $scope = Oxygen_Scope::root();
            $generator = $scope->Oxygen_Generator();
            $generator->generate();
        }
    }


?>