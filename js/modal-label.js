$(document).ready(function() {
  //$('#modal-label').on('show.bs.modal', function() { $('#modal-class').modal('hide'); });
  //$('#modal-label').on('hidden.bs.modal', function() { $('#modal-class').modal('show'); });

  $('#modal-label').on('show.bs.modal', function(event) {
    var id = $('#modal-class').data('_id');
    var c_id = $(event.relatedTarget).closest('.class').attr('id');
    var c = $('#'+c_id).index('.class');

    var data = $('#'+id).data();

    $(this).data('_id', c_id);

    if (typeof(data.class) != 'undefined' && typeof(data.class[c]) != 'undefined' && typeof(data.class[c].label) != 'undefined') {
      if (data.class[c].label.align) $('#selectLabelAlign').val(data.class[c].label.align);
      if (data.class[c].label.position) $('#selectLabelPosition').val(data.class[c].label.position);
      if (data.class[c].label.color) {
        $('#inputLabelColorR').val(data.class[c].label.color.r);
        $('#inputLabelColorG').val(data.class[c].label.color.g);
        $('#inputLabelColorB').val(data.class[c].label.color.b);
      }
      if (data.class[c].label.outlinecolor) {
        $('#inputLabelOutlineColorR').val(data.class[c].label.outlinecolor.r);
        $('#inputLabelOutlineColorG').val(data.class[c].label.outlinecolor.g);
        $('#inputLabelOutlineColorB').val(data.class[c].label.outlinecolor.b);
      }
      $('#inputLabelMinScaleDenom').val(data.class[c].label.minscaledenom);
      $('#inputLabelMaxScaleDenom').val(data.class[c].label.maxscaledenom);
    }
  });
  $('#modal-label').on('hidden.bs.modal', function(event) {
    $(this).data('_id', null); $('#modal-label form').trigger('reset');
  });
  $('#modal-label .modal-footer > .btn-primary').on('click', function() {
    var id = $('#modal-class').data('_id');
    var c_id = $('#modal-label').data('_id');
    var c = $('#'+c_id).index('.class');

    var data = $('#modal-label form').serializeObject();

    var _data = $('#'+id).data();
    if (typeof(_data.class) == 'undefined') _data.class = [];
    if (typeof(_data.class[c]) == 'undefined') _data.class[c] = {};
    _data.class[c].label = data.label;
    $('#'+id).data(_data);

    update(function() { $('#modal-label').modal('hide'); });
  });


});