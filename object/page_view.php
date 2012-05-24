<!DOCTYPE html>
<html>
<?
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
<script src="<?=$this->scope->lib->url('js/oxygen.js')?>"></script>
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

