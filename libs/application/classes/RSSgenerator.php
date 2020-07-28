<?php
class RSSgenerator {
  // obsah jednotlivých kanálů
  var $chanel = array();
  // použité kodování
  var $rss_encoding = 'utf-8';
  // verze RSS
  var $rss_version = '2.0';
  
  // přidá nový kanál do RSS
  function add_chanel($name, $param) {
    $this->chanel[$name] = $param;
    $this->chanel[$name]['.item'] = array();
  }
  // přidá novou položku do kanálu
  function add_item($chanel_name, $param) {
    $this->chanel[$chanel_name]['.item'][] = $param;
  }
  // přidá novou položku do kanálu
  function create_rss() {
    // vytvoří a nastaví instanci třídy c_xml_generator
    $xg = new XMLgenerator();
    $xg->xml_encoding = $this->rss_encoding;
    // vytvoří nejvyšší xml element rss a nastaí verzi rss
    $x_rss = $xg->add_node(0, 'rss',
      array('version'=>$this->rss_version)
    );
    // postupně prochází pole $chanel
    foreach($this->chanel as $chanel) {
      // vloží element chanel
      $x_chanel = $xg->add_node($x_rss, 'channel');
      // prochází jednotlivé parametry kanálu
      foreach($chanel as $param_name => $param_value) {
        // pokud je parametr 'image' nebo 'textInput' projde a vloží jeho parametry
        if (($param_name == 'image') || ($param_name == 'textInput')) {
          $x_image = $xg->add_node($x_chanel, $param_name);
          foreach($param_value as $image_param_name => $image_param_value) {
            $xg->add_node_cdata($x_image, $image_param_name, $image_param_value);
          };
        // pokud je parametr 'item' - jednotliv0 položky kanálu, projde a vloží je
        } elseif ($param_name == '.item') {
          foreach($chanel['.item'] as $item) {
            // pro každou položku vyvtoří element item
            $x_item = $xg->add_node($x_chanel, 'item');
            // projde a vloží jednotlivé parametry
            foreach($item as $item_param_name => $item_param_value) {
              // pokud je parametr 'image' nebo 'textInput' projde a vloží jeho parametry
              if (($item_param_name == 'image') || ($item_param_name == 'textInput')) {
                $x_image = $xg->add_node($x_item, $item_param_name);
                foreach($item_param_value as $image_param_name => $image_param_value) {
                  $xg->add_node_cdata($x_image, $image_param_name, $image_param_value);
                };
              // pokud jde o jiný parametr vloží jeho hodnotu
              } else {
                $xg->add_node_cdata($x_item, $item_param_name ,$item_param_value);
              };
            };
          };
        // pokud jde o jiný parametr vloží jeho hodnotu
        } else {
          $xg->add_node_cdata($x_chanel, $param_name ,$param_value);
        };
      };
    };
    // nakonec vygeneruje a vrátí výsledné XML
    return($xg->create_xml());
  }
};
