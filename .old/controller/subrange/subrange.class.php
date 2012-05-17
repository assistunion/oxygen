<?
	class Oxygen_Controller_Subrange 
		extends Oxygen_Controller
		implements ArrayAccess, IteratorAggregate, Countable 
	{

		public function __construct($model,$parent,$route){
			parent::__construct($model,$parent);
		}

		public function offsetGet($offset) {

		}

		public function offsetExists($offset) {

		}

		public function count() {

		}

		public function getIterator() {

		}
	}


?>