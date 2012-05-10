var $document = $(document)
  , $content = $('div.oxy-content')
  , $menu = $('div.oxy-menu')
  , $header = $('div.oxy-header')
  , $footer = $('div.oxy-footer')
 ;

clientHeight = $document.height()-$header.height()-$footer.height();
$content.height(clientHeight);
$menu.height(clientHeight);

