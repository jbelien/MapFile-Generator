$(document).ready(function() {
  $('#modal-class').on('show.bs.modal', function(event) {
    var id = $(event.relatedTarget).closest('.layer').attr('id');
    var data = $('#'+id).data();

    $(this).data('_id', id);
    if (data.minscaledenom) $('#inputLayerMinScaleDenom').val(data.minscaledenom);
    if (data.maxscaledenom) $('#inputLayerMaxScaleDenom').val(data.maxscaledenom);
    if (data.opacity) $('#inputLayerOpacity').val(data.opacity);
    if (data.classitem) $('#inputLayerClassItem').val(data.classitem);
    if (data.labelitem) $('#inputLayerLabelItem').val(data.labelitem);

    $('#modal-class .class:gt(0)').remove();
    if (typeof(data.class) != 'undefined') {
      for (var c = 0; c < data.class.length; c++) {
        if (typeof(data.class[c]) == 'undefined') continue;

        if (c == 0) {
          $('#inputClass0Name').val(data.class[c].name);
          $('#inputClass0Expression').val(data.class[c].expression);
        }
        else {
          var div = $('#class0').clone().appendTo('#modal-class form');
          $(div).attr('id', 'class'+c);
          $(div).find('legend').text('Class #'+(c+1));
          $(div).find('label[for=inputClass0Name]').attr('for', 'inputClass'+c+'Name');
          $(div).find('label[for=inputClass0Expression]').attr('for', 'inputClass'+c+'Expression');
          $(div).find('#inputClass0Name').attr({ id: 'inputClass'+c+'Name', name: 'class['+c+'][name]' }).val(data.class[c].name);
          $(div).find('#inputClass0Expression').attr({ id: 'inputClass'+c+'Expression', name: 'class['+c+'][expression]' }).val(data.class[c].expression);
        }
      }
    }
  });
  $('#modal-class').on('hidden.bs.modal', function(event) {
    $(this).data('_id', null); $('#modal-class form').trigger('reset');
  });
  $('#modal-class .modal-footer > .btn-default:eq(1)').on('click', function() {
    var c = $('#modal-class .class').length;
    var div = $('#class0').clone().appendTo('#modal-class form');
    $(div).attr('id', 'class'+c);
    $(div).find('legend').text('Class #'+(c+1));
    $(div).find('label[for=inputClass0Name]').attr('for', 'inputClass'+c+'Name');
    $(div).find('label[for=inputClass0Expression]').attr('for', 'inputClass'+c+'Expression');
    $(div).find('#inputClass0Name').attr({ id: 'inputClass'+c+'Name', name: 'class['+c+'][name]' }).val('');
    $(div).find('#inputClass0Expression').attr({ id: 'inputClass'+c+'Expression', name: 'class['+c+'][expression]' }).val('');
    $('#modal-class').modal('handleUpdate');
  });
  $('#modal-class .modal-footer > .btn-primary').on('click', function() {
    var id = $('#modal-class').data('_id');
    var data = $('#modal-class form').serializeObject();

    //var l = $('#'+id).index('.layer');
    var _data = $('#'+id).data();
    _data.minscaledenom = data.minscaledenom;
    _data.maxscaledenom = data.maxscaledenom;
    _data.opacity = data.opacity;
    _data.labelitem = data.labelitem;
    _data.classitem = data.classitem;
    for (var i = 0; i < $('#modal-class .class').length; i++) {
      _data.class[i].name = data.class[i].name;
      _data.class[i].expression = data.class[i].expression;
    }
    $('#'+id).data(_data);

    update(function() { $('#modal-class').modal('hide'); });
  });
});