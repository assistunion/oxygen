<?
    class Oxygen_Controller_Routes extends Oxygen_Object implements ArrayAccess {

        const ASSIGNMENT_CONFIGURE = 'Assigment instead of configuring. For configuring please use $route["path"]->Class($model)';

        public function offsetExists($offset) {
            $this->throw_Exception('Not implemented yet');
        }

        public function offsetSet($offset, $value) {
            $this->throw_Exception(self::ASSIGNMENT_CONFIGURE);
        }

        public function offsetGet($offset) {
            return $this->new_Oxygen_Controller_Configurator($offset);
        }

        public function offsetUnset($offset) {
            $this->throw_Exception('Not implemented yet');
        }
    }


?>