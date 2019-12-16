{
  "version": 2,
  "extra_config": {
    "github_com/devopsfaith/krakend-cors": {
      "allow_origins": [
        "*"
      ],
      "expose_headers": [
        "Content-Length"
      ],
      "max_age": "0s",
      "allow_methods": [
        "GET",
        "HEAD",
        "POST",
        "PUT",
        "DELETE",
        "OPTIONS"
      ],
      "allow_headers": [
        "Authorization",
        "Content-Type",
        "Access-Control-Allow-Headers",
        "Accept",
        "Origin",
        "Access-Control-Request-Method"
      ],
      "allow_credentials": true
    }
  },
  "endpoints": <?= ($this->raw($ENDPOINTS)) ?>,
  "timeout": "3000ms",
  "cache_ttl": "0s",
  "output_encoding": "json",
  "name": "krakenditor",
  "port": 8080,
  "disable_rest": false,
  "read_timeout": "0s",
  "write_timeout": "0s",
  "idle_timeout": "0s",
  "read_header_timeout": "0s"
}