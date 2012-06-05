<?

    class Oxygen_Object {

        const OBJECT_CLASS    = 'Oxygen_Object';
        const EXCEPTION_CLASS = 'Oxygen_Exception';
        const SCOPE_CLASS     = 'Oxygen_Scope';

        const DEFAULT_TO_STRING = '[{0} Object]';
        const ASSERTION_FAILED = 'Assertion failed';

        const CALL_REGEXP = '/^(parent_)?(get_|put_|throw_|new_|embed_)(.*)$/';
        const UNKNOWN_METHOD = 'Unknown method {0}->{1}';

        public $scope = null;

        public function urlFor($resource) {
            return $this->scope->loader->urlFor(get_class($this),$resource);
        }

        public function pathFor($resource) {
            return $this->scope->loader->pathFor(get_class($this),$resource);
        }

        public static function newGuid() {
            $s = '0123456789abcdef';
            $r = '';
            for ($i = 0; $i < 40; $i++) {
                $r .= $s{mt_rand()%16};
            }
            return $r;
        }

        private function queueFlash($message, $type = 'info') {
            $trace = debug_backtrace();
            $trace = $trace[1];
            $messages = $this->scope->SESSION->get('oxygen-flash-messages',array());
            $messages[] = array('message'=>$message, 'type'=>$type, 'at' =>
                    str_replace(DIRECTORY_SEPARATOR,'/',
                    str_replace($this->scope->OXYGEN_ROOT,'',$trace['file'])). ':' . $trace['line']
            );
            $this->scope->SESSION['oxygen-flash-messages'] = $messages;
        }

        public function flash($message, $type = 'info') {
            $this->queueFlash($message, $type);
        }

        public function log($object){
            $this->queueFlash($object,'debug');
        }

        public function __call($method, $args) {
            $this->__assert(
                preg_match(self::CALL_REGEXP, $method, $match),
                self::UNKNOWN_METHOD,
                get_class($this),
                $method
            );
            $class = get_class($this);
            if ($match[1] !== '') $class = get_parent_class($this);
            return $this->{$match[2]}($match[3],$args);
        }

        public function getDefaultView() {
            return 'view';
        }

        public function put() {
            $this->put_($this->getDefaultView(),func_get_args());
        }

        public final function new_($class, $args = array()) {
            return $this->scope->resolve($class)->getInstance($args, $this->scope);
        }

        public final function throw_($class, $args) {
            throw $this->new_($class, $args);
        }

        public final function embed_($name, $args = array(), $class = false) {
            $assets = $this->scope->assets;
            $body = $this->get_($name, $args, $class);
            $less = $assets->less->compile();
            $js   = $assets->js->compile();
            return array(
                'body' => $body,
                'less' => $this->scope->OXYGEN_ROOT_URI . '/' . $less . '.less',
                'js' => $this->scope->OXYGEN_ROOT_URI . '/' . $js . '.js'
            );
        }

        public final function get_($name, $args = array(), $class = false) {
            ob_start();
            try {
                $this->put_($name, $args, $class);
                $ex = null;
            } catch(Exception $e) {
                $ex = $e;
            }
            if ($ex !== null) {
                ob_end_clean();
                throw $ex;
            } else {
                return ob_get_clean();
            }
        }

        public final function put_($name, $args = array(), $class = false) {
            $class = ($class === false) ? get_class($this) : $class;
            $call = (object)array(
                'instance'  => $this,
                'class'     => $class,
                'name'      => $name,
                'stack'     => array(),
                'sp'        => 0,
                'component' => false,
                'assets'    => array()
            );
            Oxygen::push($call);
            $scope = $this->scope;
            $assets = $scope->assets;
            $resource = (strpos($name,'.') === false)
                ? $name . Oxygen_Loader::TEMPLATE_EXTENSION
                : $name
            ;
            try {
                include $scope->loader->pathFor(
                    $class,
                    $resource
                );
                $ex = null;
            } catch(Exception $e){
                $ex = $e;
            }
            Oxygen::closeAll();
            $result = Oxygen::pop();
            if ($ex !== null) throw $ex;
            $assets->add($result);
        }

        public function getTemplateClass() {
            return Oxygen::getCssClass();
        }


        public function __toString() {
            return Oxygen_Utils_Text::format(self::DEFAULT_TO_STRING, get_class($this));
        }

        public final function __assert(
            $condition,
            $message = false,
            $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
        ) {
            if (!$condition) {
                $this->throw_Exception(
                    Oxygen_Utils_Text::format(
                        ($message === false ? self::ASSERTION_FAILED : $message),
                        $arg0, $arg1, $arg2, $arg3, $arg4
                    )
                );
            }
        }

        public function __complete() {
        }

        public static function __class_construct($scope) {
            /* Intentionally left blank. No code here */
        }

        public function __depend($scope) {
            $this->scope = $scope;
        }

        // Small inheritance hack:
        // Let system think that EXCEPTION_CLASS
        // is inherited from OBJECT_CLASS (not from Exception)
        public static function getOxygenParentClass($class) {
            return $class === self::EXCEPTION_CLASS
                ? self::OBJECT_CLASS
                : get_parent_class($class)
            ;
        }

        public static function isOxygenClass($class) {
            return (is_subclass_of($class, self::OBJECT_CLASS)
            || is_subclass_of($class, self::EXCEPTION_CLASS)
            || $class === self::OBJECT_CLASS
            || $class === self::EXCEPTION_CLASS
            );
        }

    }

?>