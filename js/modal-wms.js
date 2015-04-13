$(document).ready(function() {
  $('#modal-wms').on('show.bs.modal', function(event) {
    var id = $(event.relatedTarget).closest('.layer').attr('id');
    var data = $(event.relatedTarget).closest('.layer').data();

    $(this).data('_id', id);

    if (data.wms_title) $('#inputLayerWMSTitle').val(data.wms_title);
    if (data.wms_abstract) $('#inputLayerWMSAbstract').val(data.wms_abstract);
    if (data.wms_include_items) $('#inputLayerWMSIncludeItems').val(data.wms_include_items);
    if (data.wms_exclude_items) $('#inputLayerWMSExcludeItems').val(data.wms_exclude_items);
    if (data.wms_attribution_title) $('#inputLayerWMSAttributionTitle').val(data.wms_attribution_title);
    if (data.wms_attribution_onlineresource) $('#inputLayerWMSAttributionOnlineResource').val(data.wms_attribution_onlineresource);
    if (data.wms_enable_request) $('#inputLayerWMSRequest').val(data.wms_enable_request);
  });
  $('#modal-wms').on('hidden.bs.modal', function(event) {
    $(this).data('_id', null); $('#modal-wms form').trigger('reset');
  });
  $('#modal-wms .btn-primary').on('click', function() {
    var id = $('#modal-wms').data('_id');
    var data = $('#modal-wms form').serializeObject();

    $('#'+id).data(data);

    update(function() { $('#modal-wms').modal('hide'); });
  });
});
