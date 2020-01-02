<?php class Controllers {

  public function create($f3) {
    $postData = $f3->get('POST');
    $withKrakend = $postData['with_krakend'];

    $host = $postData['host'];
    $namaFile = $f3->UPLOADS . $this->hostAsFilename($host);
    $querystring_params = (!empty($postData['querystring_params'])) ? array_map('trim',explode(',', $postData['querystring_params'])) : '';
    $headers_to_pass = (!empty($postData['headers_to_pass'])) ?  array_map('trim',explode(',', $postData['headers_to_pass'])) : '';
    $output = [];
    foreach ($postData['endpoint'] as $key => $val) {
      $params = strstr($val, '{');

      $encoding = $postData['encoding'][$key];
      $methods = $postData['methods'][$key];

      // Apakah ada > 1 method? ada kita buat jadi endpoint terpisah
      $jml_method = count($methods);
      for ($i = 0; $i < $jml_method; $i++) {
        $method = $methods[$i];
        $querystring_params = ($method === 'GET' && !$params && $encoding === 'json') ? '' : $querystring_params;
        $output[] = $this->createByTemplate(
          $val,
          $querystring_params,
          $headers_to_pass,
          $method,
          $postData['url_pattern'][$key],
          $host,
          $encoding
        );
      }
      file_put_contents($namaFile, $this->encjson(['endpoints' => $output ]));
    }
    // Cek apakah untuk endpoint doang ato bersama krakend.json ?
    if (!$withKrakend) { // Kalau tidak langsung download aja endpointnya
      $this->download($namaFile);
    } else {
      $headerForKrakend = [
        'extra_config' => [
          'github_com/devopsfaith/krakend-cors' => [
            'allow_methods' => $postData['allow_methods'],
            'allow_headers' => $this->explodeArray($postData['allow_headers'])
          ]
        ]
      ];
      $postData = array_merge($postData, $headerForKrakend);
      $krakend = $this->parsingKrakend($f3, $postData, [ basename(str_replace('.json', '', $namaFile)) ]);

      $this->zipping($krakend, [ str_replace($f3->UPLOADS, '', $namaFile) ], $f3->UPLOADS);
    }
  }

  public function saveEdit($f3) {
    $postData = $f3->get('POST');
    $host = $postData['host'];

    $jml_endpoint = count($postData['endpoint']);
    for ($i = 0; $i < $jml_endpoint; $i++) {
      $output[] = $this->createByTemplate(
        $postData['endpoint'][$i],
        $this->explodeArray($postData['querystring_params'][$i]),
        $this->explodeArray($postData['headers_to_pass'][$i]),
        $postData['method'][$i],
        $postData['url_pattern'][$i],
        $host,
        $postData['encoding'][$i]
      );
    }
    $namaFile = $this->hostAsFilename($host);
    file_put_contents($f3->UPLOADS . $namaFile, $this->encjson(['endpoints' => $output ]));
    $this->download($f3->UPLOADS . $namaFile);
  }

  public function perHost(array $endpoints, string $folderTemp) {
    // Ambil host
    $jml = count($endpoints);
    for ($i = 0; $i <= $jml; $i++) {
      $host = $endpoints[$i]['backend'][0]['host'][0];
      if (!is_null($host)) {
        $hosts[] = $host;
      }
    }
    $list_hosts = array_values(array_unique($hosts));

    // Bagi endpoint per host
    foreach ($list_hosts as $host) {
      $block = [];
      for ($i = 0; $i <= $jml; $i++) {
        if ($endpoints[$i]['backend'][0]['host'][0] == $host) {
          $block[] = $endpoints[$i];
        }
      }
      $namafile = $this->hostAsFilename($host);
      file_put_contents($folderTemp . $namafile, $this->encjson(['endpoints' => $block]));
      $return[] = $namafile;
    }

    return $return;
  }

  public function parsingKrakend($f3, array $input, array $endpoint) {
      // Ada setingan cors? Ambil
    $cors = $input['extra_config']['github_com/devopsfaith/krakend-cors'];
    if (is_array($cors)) {
      $cors = [ 'github_com/devopsfaith/krakend-cors' => [
        'allow_headers' => $cors['allow_headers'],
        'allow_methods' => $cors['allow_methods']
      ]];
    } else {
      $cors = '';
    }
    $output = [
      'version' => 2,
      'name' => 'NST krakend config generator',
      'port' => (int)$input['port'],
      'timeout' => $input['timeout'],
      'cache_ttl' => $input['cache_ttl'],
      'output_encoding' => 'json',
      'extra_config' => $this->encjson($cors),
      'endpoints' => $this->block($endpoint)
    ];
    $f3->mset($output); // Kirim ke f3, digunakan pada baris selanjutnya
    $krakend = $this->show('skeleton-krakend', 'application/json');
    return $krakend;
  }

  public function zipping(string $krakend, array $nama_endpoint, string $folderTemp) {
    // Buat file krakend.json
    $file_krakend = $folderTemp . 'krakend.json';
    file_put_contents($file_krakend, $krakend);

    // Buat Zip
    $nama_zip = $folderTemp . $this->fileNameRand() . '.zip';
    $zip = new ZipArchive;
    if ($zip->open($nama_zip, ZipArchive::CREATE) === TRUE) {
      foreach ($nama_endpoint as $file) {
        $zip->addFile($folderTemp . $file, '/settings/' . $file);
      }
      $zip->addFile($file_krakend, basename($file_krakend));
      $zip->close();
    }
    foreach ($nama_endpoint as $file) {
      unlink($folderTemp . $file);
    }
    unlink($file_krakend);
    $this->download($nama_zip);
  }

  public function convertToJSONEndpoint($f3, array $endpoints, array $headers_to_pass, array $querystring_params) {
    foreach ($endpoints as $host => $val) {
      $namafile = strstr(basename($host), ':', true) . '.json';
      $output = [];

      // Host punya berapa endpoint?
      $jml_endpoint = count($val);

      // Loop..
      for ($i = 0; $i < $jml_endpoint; $i++) {
        $methods = $val[$i]['method'];
        $block = $val[$i]['endpoint'];
        $params = strstr($block['endpoint'], '{');

        // Ada berapa method?
        $jml_method = count($methods);
        for ($j = 0; $j < $jml_method; $j++) {
          $method = $methods[$j];
          if (!is_null($method)) {
            $qp = ($method == 'GET' && !$params) ? $querystring_params : '';
            $output[] = $this->createByTemplate(
              $block['endpoint'],
              $qp,
              $headers_to_pass,
              $method,
              $block['url_pattern'],
              $host,
              $block['output_encoding']
            );
          }
        }
      }
    }
  }

  private function createByTemplate(string $endpoint, $querystring_params, $headers_to_pass, string $method, string $url_pattern, string $host, string $encoding) {
    return [
      'endpoint' => $endpoint,
      'querystring_params' => $querystring_params,
      'headers_to_pass' => $headers_to_pass,
      'method' => $method,
      'backend' => [
        'url_pattern' => $url_pattern,
        'host' => [ $host ],
        'encoding' => $encoding
      ]
    ];
  }

  private function block(array $nama_block) {
    foreach ($nama_block as $nama) {
      $nama = str_replace(['.json', '-'], '', $nama);
      $output[] =
      '{{ range $i, $v := .'. $nama .'.endpoints }}
        {{ marshal $v }},
       {{ end }}';
    }
    return $output;
  }

  private function download(string $nama_file, string $mime = null) {
    if (is_null($mime)) {
      $mime = 'application/json';
    }
    $sent = \Web::instance()->send($nama_file);
    unlink($nama_file);
  }

  private function explodeArray(string $input) {
    return array_map('trim',explode(',', $input));
  }

  private function fileNameRand() {
    return bin2hex(random_bytes(5));
  }

  private function hostAsFilename(string $input) {
    $namaFilter = strstr(basename($input), ':', true);
    if (!$namaFilter) {
      $namaFilter = basename($input);
    }
    $namaFilter = str_replace('-', '', $namaFilter);
    return $namaFilter . '.json';
  }

  private function encjson($input) {
    return json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  }

  public function show(string $nama_file, string $mime = null) {
    if (is_null($mime)) {
      $mime = 'text/html';
    }
    return Template::instance()->render($nama_file . '.' . basename($mime), $mime);
  }
}