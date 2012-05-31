<?

    class Oxygen_Common_Collection extends Oxygen_Common_Controller {
        public function plural($n) {
            if($n === 1) return 'item';
            else return 'items';
        }

        public function rpc_getMore($offset) {
            $this->model = $this->model->slice($offset, 25);
            return array(
                'count'=>count($this->section('data')),
                'embed'=>$this->embed_table_rows($this->getHeaders())
            );
        }

        public function humanize($name) {
            $x = str_replace('_', ' ', $name);
            $x = preg_replace('/\s(en|ru|lv)$/','(\\1)',$x);
            $x = preg_replace('/id$/','ID',$x);
            $x = explode('.',$x);
            array_shift($x);
            return ucfirst(implode(' ', $x));
        }

        public function getHeaders() {
            $result = array();
            foreach ($this->model->meta['keys'] as $key) {
                foreach ($key as $column) {
                    $result[$column] = array(
                        'name' => $this->humanize($column),
                        'mode' => 'link'
                    );
                }
            }
            foreach($this->model->meta['select']['select'] as $column) {
                if(!isset($result[$column])) {
                    $result[$column] = array(
                        'name' => $this->humanize($column),
                        'mode' => 'show'
                    );
                }
            }
            foreach($this->model->meta['select']['update'] as $column) {
                if(isset($result[$column]) && $result[$column]['mode'] === 'show') {
                    $result[$column]['mode'] = 'edit';
                }
            }
            return $result;
        }

        public function rpc_UpdateCell($args) {
            $remote = $args->remote;
            $this->log($this->model->meta['keys']);
            return $args->remote;
        }


    }


?>