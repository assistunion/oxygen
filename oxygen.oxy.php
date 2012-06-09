<?
    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class Oxygen_  {

        private static $static = null;
        public static $name = 'Oxygen';
        public static function __getClass() {
            if (self::$static === null) {
                self::$static = new Oxygen_Class('Oxygen');
            }
            return self::$static;
        }

        public static function __getParentClass() {
            return null;
        
        }

        # SCOPE:
        
        public static $__oxygenScope = array(
            'Object' => 'Oxygen_Object',
            'Exception' => 'Exception',
            'Scope' => 'Oxygen_Scope',
            'Controller' => 'Oxygen_Controller',
        );
        
        # BEGIN ASSETS:
            public static $__defines_css_inspected = 'Oxygen';
            public static $__defines_less_inspected = 'Oxygen';
            public static $__defines_js_inspected = 'Oxygen';

            public function asset_inspected_css($path, $css, $class, $last) {}
            public function asset_inspected_less($path, $css, $class, $last) {}
            public function asset_inspected_js($path, $css, $class, $last) {}
        # END ASSETS.

        # BEGIN VIEWS:
                        
            /** GET: Renders given exception            
                @param Exception ex            
            */
            private function get_exception($ex) {
                ob_start(); try { $this->put_exception($ex); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders given exception            
                @param Exception ex            
            */
            private function put_exception($ex) {
                try {
                    Oxygen::push($this,'exception');
                    $result = include OXYGEN_ROOT . '/oxygen/exception.php';
                    Oxygen::closeAll();
                
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }
                        
            /** GET: Renders given exception trace            
                @param array trace            
            */
            private function get_exception_trace($trace) {
                ob_start(); try { $this->put_exception_trace($trace); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders given exception trace            
                @param array trace            
            */
            private function put_exception_trace($trace) {
                try {
                    Oxygen::push($this,'exception_trace');
                    $result = include OXYGEN_ROOT . '/oxygen/exception_trace.php';
                    Oxygen::closeAll();
                
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }
                        
            /** GET: Put contents of given object in debug/inspectable form            
                @param Object value            
            */
            public function get_inspected($value) {
                ob_start(); try { $this->put_inspected($value); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Put contents of given object in debug/inspectable form            
                @param Object value            
            */
            public function put_inspected($value) {
                try {
                    Oxygen::push($this,'inspected');
                    $result = include OXYGEN_ROOT . '/oxygen/inspected.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
                    $last = $this->__lastMetaModified();
                    $this->asset_inspected_css(
                        '/oxygen', 
                        'css-Oxygen',
                        $class,
                        $last
                    );
                    $this->asset_inspected_less(
                        '/oxygen', 
                        'css-Oxygen',
                        $class,
                        $last
                    );
                    $this->asset_inspected_js(
                        '/oxygen', 
                        'css-Oxygen',
                        $class,
                        $last
                    );
                
                
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }
                        
            /** GET: Renders template            
                @param Object class            
            */
            private function get_oxy($class) {
                ob_start(); try { $this->put_oxy($class); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders template            
                @param Object class            
            */
            private function put_oxy($class) {
                try {
                    Oxygen::push($this,'oxy');
                    $result = include OXYGEN_ROOT . '/oxygen/oxy.php';
                    Oxygen::closeAll();
                
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }

        # END VIEWS.

        public function __lastMetaModified() {
            return 1339256399;
        }
    }

    
?>