<?
    class Oxygen_Exception_Wrapper extends Oxygen_Exception {
        private $ex = null;
        public function __construct($ex) {
            parent::__construct($ex->getMessage());
            $this->ex = $ex;
        }
        public function __toString() {
            return (string)$this->ex;
        }
        
        public function getWrapTrace() {
            return $this->ex->getTrace();
        }
        
        public function getName() {
            return get_class($this->ex);
        }        
        
    }

?>