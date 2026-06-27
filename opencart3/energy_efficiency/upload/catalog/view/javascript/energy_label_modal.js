$(document).on('click', '.open-file-modal', function () {
    let file = $(this).data('file');
    let title = $(this).data('title');

    $('#fileModalTitle').text(title);

    // reset
    $('#fileModalImage').hide();
    $('#fileModalFrame').hide();

    // detect image
    if (file.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
        $('#fileModalImage').attr('src', file).show();
    } else {
        $('#fileModalFrame').attr('src', file).show();
    }

    $('#fileModal').modal('show');
});

$('#fileModal').on('hidden.bs.modal', function () {
    $('#fileModalFrame').attr('src', '');
    $('#fileModalImage').attr('src', '');
});