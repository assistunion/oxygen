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

        public static function open($tag = 'div', $data = array()){
            if(is_array($tag)) {
                $data = $tag;
                $tag = 'div';
            }
            preg_match_all('/(([A-Za-z_]+)="([^"]+)")/', $tag, $attrs);
            preg_match_all('/\.([A-Za-z_0-9]+)/', $tag, $classes);
            $classes = $classes[1];
            preg_match('/^[A-Za-z:_0-9]+/', $tag, $tagm);
            $tag  = $tagm[0];
            $attrs = $attrs[1];
            $call = self::$stack[self::$sp-1];
            $remote = $call->instance->go();
            if($remote != '/')$remote = $remote.'/';
            $data['remote'] = $remote;
            $data['component'] = $call->name;
            $call->stack[$call->sp++] = $tag;
            echo '<' . $tag . ' class="' . self::getCssClass();
            foreach($classes as $class) {
                echo ' '. $class;
            }
            echo '"';
            foreach ($attrs as $a) {
                echo ' '.$a;
            }
            if(is_array($data)) {
                foreach ($data as $key => $value) {
                    if(!is_string($value)){
                        $value = json_encode($value);
                    }
                    echo ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                }
            }
            echo '>';
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