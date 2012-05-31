<?

    class Oxygen_Common_AdminTools extends Oxygen_Controller {

        public function configure($x){
            $x['dbs'] = $this->scope->connection;
            $x['auth'] = $this->scope->__authenticated();
        }

        public function __toString() {
            return 'Admin tools';
        }

        public function getIcon() {
            return 'wrench';
        }
    }
?>