$(function(){
  multi();

  $(document)
  .on("ready ajaxComplete", function() {
    multi();
  })
  .on("click", "#button-generate", function(e) {
    e.preventDefault();
    $.post("/generate-table", $("#endpoint-generate").val(), function(res) { $("#table-generate").html(res); });
  })
  .on("click", ".tambah", function() {
    $("#blank_table").clone().removeAttr("id").prependTo("tbody");
  })
  .on("click", ".hapus", function() {
    $(this).parents("tr").remove();
  })
  .on("change", "#form_convert", function() {
    $(".file-name").text($(".file-input").val());
    $("#button-convert").removeAttr("disabled");
  })
  .on("paste", "#textarea", function(e) {
    var teks = e.originalEvent.clipboardData.getData('text');
    $.post('/paste', {data: teks}, function(res) {
      $("#upload_result").html(res);
    });
  })
  .on("change", "#form_upload", function(e) {
    e.preventDefault();
    $(".file-name").text("");
    $.ajax({
      url: $(this).attr("action"),
      type: 'POST',
      processData: false,
      contentType: false,
      data: new FormData(this),
      success: function (res) {
        $(".file-name").text($(".file-input").val());
        if (res.status) {
          $("#upload_result").empty().text(res.message);
        } else {
          $("#upload_result").empty().html(res);
        }
      }
    });
  })
  .on("click", "#menu-tabs li", function(e) {
    e.preventDefault();
    const href = $(this).children("a").attr("href");
    history.pushState({}, '', href);
    $("#container").load(href);
    $(this).addClass("is-active").siblings("li").removeClass("is-active");
  });


  window.onpopstate = function () {
    $("#container").load(location.href);
  };

  function multi() {
    $("select.multi-choice").selectize({
      plugins: ['remove_button'],
      persist: true,
      create: function(input) {
        return {
          value: input,
          text: input
        }
      }
    });
  }

});