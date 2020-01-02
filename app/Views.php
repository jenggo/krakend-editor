<?php class Views extends Controllers {
  private $basicMethods = ['GET', 'POST', 'PUT', 'DELETE'];
  private $extendMethods = ['OPTIONS', 'CONNECT', 'TRACE', 'PATCH'];
  private $encodings = ['JSON', 'STRING', 'NO-OP'];

  public function create($f3) {
    $this->template($f3, 'create');
  }

  public function edit($f3) {
    $this->template($f3, 'edit');
  }

  public function convert($f3) {
    $this->template($f3, 'convert');
  }

  public function pasteTextarea($f3) {
    $postData = json_decode($f3->get('POST.data'), true);
    // Kalau ada isinya baru tampilkan (json)
    if ($postData) {
      $f3->mset([
        'DATA' => $postData['endpoints'],
        'USE_METHODS' => $this->basicMethods,
        'USE_ENCODINGS' => $this->encodings
      ]);
      echo $this->show('form-data');
    } else {
      $f3->set('MESSAGE', 'Not JSON or invalid one');
      echo $this->show('error');
    }
  }

  public function editForm($f3) {
    $filenya = \Web::instance()->receive(function($file) {
      if ($file['type'] != 'application/json') {
        return false;
      }
      return true;
    }, true, true);

    $namafile = array_keys($filenya)[0];
    if (empty($namafile)) {
      exit;
    }

    $parsing = file_get_contents($namafile);
    unlink($namafile);
    $parsing = json_decode($parsing, true);
    if ($parsing) {
      $f3->mset([
        'DATA' => $parsing['endpoints'],
        'USE_METHODS' => $this->basicMethods,
        'USE_ENCODINGS' => $this->encodings
      ]);
      echo $this->show('form-data');
    } else {
      $f3->set('MESSAGE', 'Not JSON or invalid one');
      echo $this->show('error');
    }
  }

  public function generate($f3) {
    $postData = $f3->get('POST');
    $for = basename($f3->get('PARAMS.0'));
    if ($for == 'generate-table') {
      $f3->mset([
        'jumlah_endpoint' => array_keys($postData)[0],
        'USE_METHODS' => $this->basicMethods,
        'USE_ENCODINGS' => $this->encodings
      ]);
      echo $this->show('generate-table');
    }
  }

  public function upload($f3) {
    $filenya = \Web::instance()->receive(function($file) {
      if ($file['type'] != 'application/json') {
        return false;
      }
      return true;
    }, true, true);

    $namafile = array_keys($filenya)[0];
    if (empty($namafile)) {
      exit;
    }

    $parsing = file_get_contents($namafile);
    unlink($namafile);
    $parsing = json_decode($parsing, true);

    if (!is_array($parsing)) {
      // Jelas bukan file yang dimaksud
      $f3->set('MESSAGE', 'Unknown file or invalid JSON!');
      echo $this->show('error');
    } else {
      // tidak ada key utama, berarti bukan filenya
      if (!array_key_exists('version', $parsing)) {
        $f3->set('MESSAGE', 'Invalid File!');
        echo $this->show('error');
      } else {
        $untuk = basename($f3->get('PARAMS.0'));
        if ($untuk == 'convert') {
          // Cacah menjadi per-host
          $file_endpoint = $this->perHost($parsing['endpoints'], $f3->UPLOADS);
          $file_krakend = $this->parsingKrakend($f3, $parsing, $file_endpoint);

          $this->zipping($file_krakend, $file_endpoint, $f3->UPLOADS); // Download!
        } else {
          $f3->set('MESSAGE', 'Error!');
          echo $this->show('error');
        }
      }
    }
  }

  private function template($f3, string $file_html) {
    $f3->mset([
      'USE_METHODS' => $this->basicMethods,
      'USE_ENCODINGS' => $this->encodings,
      'USE_ALLOW_METHODS' => array_merge($this->basicMethods, $this->extendMethods)
    ]);

    if ($f3->AJAX) {
      usleep(100000);
      $load = $file_html . '.html';
    } else {
      $f3->set('TEMPLATE', $file_html . '.html');
      $load = 'index.html';
    }
    echo \Template::instance()->render($load);
  }
}