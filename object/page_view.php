<!DOCTYPE html>
<html>
<?
    $assets->js->addRemote('http://code.jquery.com/jquery-1.7.1.min.js');
    try {
        $body = $this->get_view();
        $less = $assets->less->compile();
        $js   = $assets->js->compile();
    } catch (Exception $ex) {
        $body = $this->scope->wrapException($ex)->get_view();
    }
?>
<head>
<?=$this->put_html5shim()?>
<?=$this->put_stylesheets()?>
<?=$this->put_javascripts()?>
</head>
<body>
<?=$body?>
</body>
</html>

