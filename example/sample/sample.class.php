<?

    class Sample extends Sample_ {
        public function __toString() {
            return 'Sample';
        }
        public function configure($x) {
            $x['a']->Sample();
            $x['b']->Sample();
            $x['c']->Sample();
            $x['d']->Sample();
            $x['e']->Sample();
            $x['f']->Sample();
        }
    }

?>