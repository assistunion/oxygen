<?
    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class Oxygen_Class_  {

        public static $__oxygen_path = '/oxygen/class';
        public static $__lastMetaModified = 1339377357;

        private static $static = null;
        public static $__className = 'Oxygen_Class';
        public static function __getClass() {
            if (self::$static === null) {
                self::$static = Oxygen_Class::make('Oxygen_Class');
            }
            return self::$static;
        }

        public static $__metaClass = 'Oxygen_Class';

        public static function __getMetaClass() {
            return self::$__metaClass;
        }

        public static function __getParentClass() {
            return null;
        }

        # SCOPE:
                public static $__oxygenScope = false;

        # BEGIN ASSETS:

        # END ASSETS.

        # BEGIN VIEWS:

            public static $__css_target = 'css';

            /** GET: Renders css asset chunk for given view
            */
            public function get_css() {
                ob_start(); try { $this->put_css(); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders css asset chunk for given view
            */
            public function put_css() {
                try {
                    Oxygen::push($this,'css');
                    $result = include OXYGEN_ROOT . '/oxygen/class/css.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }

            public static $__js_target = 'javascript';

            /** GET: Renders javascript asset chunk for given view
            */
            public function get_js() {
                ob_start(); try { $this->put_js(); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders javascript asset chunk for given view
            */
            public function put_js() {
                try {
                    Oxygen::push($this,'js');
                    $result = include OXYGEN_ROOT . '/oxygen/class/js.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }

            public static $__less_target = 'less-css';

            /** GET: Renders less-css asset chunk for given view
            */
            public function get_less() {
                ob_start(); try { $this->put_less(); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders less-css asset chunk for given view
            */
            public function put_less() {
                try {
                    Oxygen::push($this,'less');
                    $result = include OXYGEN_ROOT . '/oxygen/class/less.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }

            public static $__oxy_target = 'php';

            /** GET: Renders oxy-class body
            */
            public function get_oxy() {
                ob_start(); try { $this->put_oxy(); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders oxy-class body
            */
            public function put_oxy() {
                try {
                    Oxygen::push($this,'oxy');
                    $result = include OXYGEN_ROOT . '/oxygen/class/oxy.php';
                    Oxygen::closeAll();
                    $class = $this->__getClass();
                } catch (Exception $e) {
                    Oxygen::pop();
                    throw $e;
                }
                Oxygen::pop();
                return $result;
            }

        # END VIEWS.

    }


?>