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
<link rel="stylesheet" type="text/css" href="<?=$this->scope->lib->url('css/redmond/ui.css')?>"/>
<script src="<?=$this->scope->lib->url('js/oxygen.js')?>"></script>
<script src="<?=$this->scope->lib->url('js/jquery-ui-1.8.20.custom.min.js')?>"></script>
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
<div class="dialog-space" style="width:0px;height:0px;position:absolute"></div>
</body>
</html>

