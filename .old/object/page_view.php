<!DOCTYPE html>
<html>
<?
    $assets->js->addRemote('http://code.jquery.com/jquery-1.7.1.min.js');
    $assets->js->addRemote('http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.3/underscore-min.js');
    try {
        $body = $this->get_view();
        $less = $assets->less->compile();
        $js   = $assets->js->compile();
    } catch (Exception $ex) {
        $body = $this->scope->__wrapException($ex)->get_view();
    }
?>
<head>
<?=$this->put_html5shim()?>
<?=$this->put_stylesheets()?>
<?=$this->put_javascripts()?>
<?if($this instanceof Oxygen_Controller):?>
<?$current=$this->getCurrent()?>
<link rel="shortcut icon" href="<?=$current->getIconSource()?>"/>
<title><?$current->put_title()?></title>
<?endif?>
</head>
<body>
<?=$body?>
</body>
</html>

