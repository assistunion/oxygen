<?

    abstract class Oxygen_Validation_Rule extends Oxygen_Object {
        public abstract function validate($instance);
        protected function Error($owner,$message,$data = null) {
            $this->scope->validationContext->Error($owner, $message, $data);
        }
    }


?>