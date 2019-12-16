<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>krakend Editor</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulmaswatch/0.7.2/sandstone/bulmaswatch.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/css/all.min.css" />
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>
  <style type="text/css">
    .hapus, .tambah {
      cursor: pointer;
    }
    .hapus {
      margin-right: .5em;
    }
  </style>
</head>
<body>
  <section class="section">
    <?php if ($PAGE): ?>
      <?php echo $this->render($PAGE,NULL,get_defined_vars(),0); ?>
      <?php else: ?>
        <a href="/create-new" class="button is-warning is-medium is-pulled-right" title="Create New Endpoints">
          <span class="icon is-small">
            <i class="fas fa-long-arrow-alt-right"></i>
          </span>
        </a>
        <div class="container is-fluid">
          <form method="post" action="" id="form_upload" class="field">
            <div class="file has-name is-fullwidth is-info is-large">
              <label class="file-label">
                <input class="file-input" type="file" name="file" accept=".json">
                <span class="file-cta">
                  <span class="file-icon">
                    <i class="fas fa-upload"></i>
                  </span>
                  <span class="file-label">
                    Upload
                  </span>
                </span>
                <span class="file-name"></span>
              </label>
            </div>
          </form>
        </div>
      
    <?php endif; ?>
  </section>
  <section class="section" id="output"></section>
  <script type="text/javascript">
    $(function(){
      $(document)
      .on("change", "#form_upload", function(e){
        e.preventDefault();
        $.ajax({
          url: '/',
          type: 'POST',
          processData: false,
          contentType: false,
          data: new FormData(this),
          success: function (res) {
            $(".file-name").text($(".file-input").val());
            $.get('/output', function(data){ $("#output").empty().html(data) });
          }
        });
      })
      .on("click", ".hapus", function(){
        if ($(this).parents("tr").next("tr").length > 0) {
          $(this).parents("tr").remove();
        }
      })
      .on("click", ".tambah", function() {
        $(this).parents("tr").clone().appendTo("tbody");
      })
    });
  </script>
</body>
</html>