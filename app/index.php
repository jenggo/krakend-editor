<?php
  require('vendor/autoload.php');
  $f3 = \Base::instance();

  $f3->mset(array(
    'DEBUG' => 2,
    'UPLOADS' => 'tmp/'
  ));

  $f3->route('GET /', function(){
    view();
  });

  $f3->route('GET /create-new', function($f3){
    $f3->set('PAGE', 'create.html');
    view();
  });

  $f3->route('GET /output', function(){
    view('output.html');
  });

  $f3->route('POST /', function($f3){
    $filenya = \Web::instance()->receive(function($file) {
      if ($file['type'] != 'application/json') {
        return false;
      }
      return true;
    }, true, true);

    $namafile = array_keys($filenya)[0];
    $filenya = file_get_contents($namafile);
    unlink($namafile);
    $filenya = json_decode($filenya, true);

    foreach ($filenya['endpoints'] as $f) {
      $backend = $f['backend'][0];

      $output[] = array(
        'endpoint_fe' => $f['endpoint'],
        'method' => $f['method'],
        'host' => $backend['host'][0],
        'endpoint_be' => $backend['url_pattern'],
        'encoding' => $f['output_encoding']
      );
    }
    $f3->set('SESSION.OUTPUT', $output);
  });

  $f3->route('POST /output', function($f3) {
    $data = $f3->get('POST');
    $jdata = count($data['method']);
    for($i = 0; $i < $jdata; $i++) {
      $endpoint = $data['endpoint_fe'][$i];
      $url = $data['endpoint_be'][$i];
      $host = $data['host'][$i];
      $method = $data['method'][$i];
      $encoding = $data['encoding'][$i];

      $query_string = array('*', 'search', 'limit', 'sortby', 'order', 'offset', 'columns', 'query', 'fields');
      $headers_to_pass = array('Authorization', 'Content-Type');

      $output[] = array(
        'endpoint' => $endpoint,
        'method' => $method,
        'output_encoding' => $encoding,
        'concurrent_calls' => 1,
        'backend' => array(array(
          'url_pattern' => $url,
          'encoding' => $encoding,
          'sd' => 'static',
          'host' => array($host),
          'headers_to_pass' => $headers_to_pass,
          'disable_host_sanitize' => false
        )),
        'headers_to_pass' => $headers_to_pass
      );
      if ($method == 'GET') {
        $output[$i]['querystring_params'] = $query_string;
      }
    }
    $f3->set('ENDPOINTS', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $output = Template::instance()->render('skeleton.json','application/json');
    $namafile = $f3->UPLOADS . md5(rand()) . '.json';
    file_put_contents($namafile, $output);
    $sent = \Web::instance()->send($namafile, 'application/json', 2048);
    unlink($namafile);
  });

  function view(string $template = null) {
    $template = (is_null($template)) ? 'view.html' : $template;
    echo \Template::instance()->render($template);
  }

  $f3->run();