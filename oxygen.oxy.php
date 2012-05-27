<?
    # WARNING !!!

    # This file is generated automatically by Oxygen,
    # so any changes within it will be overwritten

    class Oxygen_  {

        # SCOPE:
        
        public static $__oxygenScope = array(
            'Object' => 'Oxygen_Object',
            'Exception' => 'Exception',
            'Scope' => 'Oxygen_Scope',
            'Controller' => 'Oxygen_Controller',
        );
        
        # BEGIN ASSETS:
            public function asset_exception_css() {}
            public function asset_exception_less() {}
            public function asset_exception_js() {}
            public function asset_exception_trace_css() {}
            public function asset_exception_trace_less() {}
            public function asset_exception_trace_js() {}
            public function asset_inspected_css() {}
            public function asset_inspected_less() {}
            public function asset_inspected_js() {}
            public function asset_oxy_css() {}
            public function asset_oxy_less() {}
            public function asset_oxy_js() {}
        # END ASSETS.

        # BEGIN VIEWS:
                        
            /** GET: Renders given exception            
                @param Exception ex            
            */
            public function get_exception($ex) {
                ob_start(); try { $this->put_exception($ex); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders given exception            
                @param Exception ex            
            */
            public function put_exception($ex) {
                $result = include 'C:\webdev\www\toic2.lv\oxygen\exception.php';
                $this->asset_exception_css();
                $this->asset_exception_js();
                $this->asset_exception_less();
                return $result;
            }
                        
            /** GET: Renders given exception trace            
                @param array trace            
            */
            public function get_exception_trace($trace) {
                ob_start(); try { $this->put_exception_trace($trace); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders given exception trace            
                @param array trace            
            */
            public function put_exception_trace($trace) {
                $result = include 'C:\webdev\www\toic2.lv\oxygen\exception_trace.php';
                $this->asset_exception_trace_css();
                $this->asset_exception_trace_js();
                $this->asset_exception_trace_less();
                return $result;
            }
                        
            /** GET: inspected view            
            */
            public function get_inspected() {
                ob_start(); try { $this->put_inspected(); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: inspected view            
            */
            public function put_inspected() {
                $result = include 'C:\webdev\www\toic2.lv\oxygen\inspected.php';
                $this->asset_inspected_css();
                $this->asset_inspected_js();
                $this->asset_inspected_less();
                return $result;
            }
                        
            /** GET: Renders template            
                @param Object class            
            */
            public function get_oxy($class) {
                ob_start(); try { $this->put_oxy($class); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            /** PUT: Renders template            
                @param Object class            
            */
            public function put_oxy($class) {
                $result = include 'C:\webdev\www\toic2.lv\oxygen\oxy.php';
                $this->asset_oxy_css();
                $this->asset_oxy_js();
                $this->asset_oxy_less();
                return $result;
            }

        # END VIEWS.
    }

    
?>