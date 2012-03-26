<?
    class Oxygen_Getter extends Oxygen_Putter {
        public function __call($resource,$args) {
            ob_start();
            parent::__call($resource,$args);
            return ob_get_clean();
        }
    }
?>
