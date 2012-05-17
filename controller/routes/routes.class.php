<?
    class Oxygen_Controller_Routes extends Oxygen_Object implements ArrayAccess {

        public $controller = null;

        public function __construct($controller) {
            $this->controller = $controller;
        }

        public function offsetExists($offset) {
            $this->throw_Exception('Not implemented yet');
        }

        public function offsetSet($offset, $value) {
            $this->controller->addExplicit($offset,$value);
        }

        public function offsetGet($offset) {
            return $this->scope->Configurator($this->controller, $offset);
        }

        public function offsetUnset($offset) {
            $this->throw_Exception('Not implemented yet');
        }
    }


?>