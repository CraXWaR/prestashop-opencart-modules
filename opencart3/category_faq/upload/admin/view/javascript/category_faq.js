$(document).ready(function () {
  $('button[form="form-category-faq"]').on("click", function () {
    $("#form-category-faq").submit();
  });

  $("#form-category-faq").on("submit", function (e) {
    e.preventDefault();
    $.ajax({
      url: $("form#form-category-faq").attr("action"),
      type: "POST",
      data: $("form#form-category-faq").serialize(),
      dataType: "json",
      success: function (response) {
        $(".alert").remove();
        if (response.success) {
          $("#content").prepend(
            '<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' +
              response.success +
              ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>',
          );
        }
        if (response.error) {
          $("#content").prepend(
            '<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' +
              response.error +
              ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>',
          );
        }
      },
    });
  });
});
