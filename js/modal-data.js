$(document).ready(function() {
  $('#modal-data').on('show.bs.modal', function(event) {
    var id = $(event.relatedTarget).closest('.layer').index('.layer');
    var data = $(event.relatedTarget).closest('.layer').data();

    $(this).data('_id', id);

    if (data.projection) $('#selectLayerProj').val(data.projection);
    if (data.connectiontype) $('#selectLayerConnectionType').val(data.connectiontype);
    if (data.connection) $('#inputLayerConnection').val(data.connection);
    if (data.data) $('#inputLayerData').val(data.data);
    if (data.filteritem) $('#inputLayerFilterItem').val(data.filteritem);
    if (data.filter) $('#inputLayerFilter').val(data.filter);
    if (data.group) $('#inputLayerGroup').val(data.group);
  });
  $('#modal-data').on('hidden.bs.modal', function(event) {
    $(this).data('_id', null); $('#modal-data form').trigger('reset');
  });
  $('#modal-data .btn-primary').on('click', function() {
    var id = $('#modal-data').data('_id');
    var data = $('#modal-data form').serializeObject();

    $('.layer:eq('+id+')').data(data);

    update(function() { $('#modal-data').modal('hide'); });
  });
});
