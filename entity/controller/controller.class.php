<?

    class Oxygen_Entity_Controller extends Oxygen_Controller {
        public function rpc_UpdateCell($args) {
            $this->model[$args->source]=$args->current;
            return $this->model->__submit();
        }

    }

?>