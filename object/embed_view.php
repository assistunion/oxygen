<?
    try {
        $body = $this->get_view();
        $less = $assets->less->compile();
        $js   = $assets->js->compile();
    } catch (Exception $ex) {
        $body = $this->scope->__wrapException($ex)->get_view();
    }
    $head = json_encode($this->get_stylesheets() . $this->get_javascripts());
?>
<script>
$('head').append(<?=$head?>);
</script>
<?=$body?>