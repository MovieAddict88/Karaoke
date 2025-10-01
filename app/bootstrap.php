<?php
  // Load Config
  require_once dirname(dirname(__FILE__)) . '/config/config.php';

  // Load Helpers
  require_once dirname(__FILE__) . '/helpers/url_helper.php';
  require_once dirname(__FILE__) . '/helpers/session_helper.php';

  // Autoload Core Libraries
  spl_autoload_register(function($className){
    require_once dirname(__FILE__) . '/libraries/' . $className . '.php';
  });