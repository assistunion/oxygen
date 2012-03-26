<?

    class Oxygen_Validation_Context extends Oxygen_Object implements {
        private $items = array();

        public function Error($owner,$message,$data = null) {
            $this->Add($this->scope->Oxygen_Validation_Error($owner, $message, $data));
        }
        public function Warning($owner, $message, $data = null) {
            $this->Add($this->scope->Oxygen_Validation_Warning($owner, $message, $data));
        }
        public function Notice($owner, $message, $data = null) {
            $this->Add($this->scope->Oxygen_Validation_Notice($owner, $message, $data));
        }
        public function Add($item) {
            $severity = $item->getSeverity();
            if(!isset($this->items[$severity])) {
                $this->items[$severity] = array($item);
            } else {
                $this->items[$severity][] = $item;
            }
        }
    }


?>