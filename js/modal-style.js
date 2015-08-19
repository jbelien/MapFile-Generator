$(document).ready(function() {
  //$('#modal-style').on('show.bs.modal', function() { $('#modal-class').modal('hide'); });
  //$('#modal-style').on('hidden.bs.modal', function() { $('#modal-class').modal('show'); });

  $('#modal-style').on('show.bs.modal', function(event) {
    var id = $('#modal-class').data('_id');
    var c_id = $(event.relatedTarget).closest('.class').attr('id');
    var c = $('#'+c_id).index('.class');

    var data = $('.layer:eq('+id+')').data();

    $(this).data('_id', c_id);

    $('#modal-style .style:gt(0)').remove();

    if (typeof(data.class) != 'undefined' && typeof(data.class[c]) != 'undefined' && typeof(data.class[c].style) != 'undefined') {
      for (var s = 0; s < data.class[c].style.length; s++) {
        if (typeof(data.class[c].style[s]) == 'undefined') continue;

        if (s == 0) {
          $('#inputStyle0ColorR').val(data.class[c].style[s].color.r);
          $('#inputStyle0ColorG').val(data.class[c].style[s].color.g);
          $('#inputStyle0ColorB').val(data.class[c].style[s].color.b);
          $('#inputStyle0OutlineColorR').val(data.class[c].style[s].outlinecolor.r);
          $('#inputStyle0OutlineColorG').val(data.class[c].style[s].outlinecolor.g);
          $('#inputStyle0OutlineColorB').val(data.class[c].style[s].outlinecolor.b);
          $('#inputStyle0Width').val(data.class[c].style[s].width);
          $('#inputStyle0Symbol').val(data.class[c].style[s].symbolname);
          $('#inputStyle0Size').val(data.class[c].style[s].size);
        }
        else {
          var div = $('#style0').clone().appendTo('#modal-style form');
          $(div).attr('id', 'style'+s);
          $(div).find('legend').text('Style #'+(s+1));
          $(div).find('label[for=inputStyle0ColorR]').attr('for', 'inputStyle'+s+'ColorR');
          $(div).find('label[for=inputStyle0ColorG]').attr('for', 'inputStyle'+s+'ColorG');
          $(div).find('label[for=inputStyle0ColorB]').attr('for', 'inputStyle'+s+'ColorB');
          $(div).find('label[for=inputStyle0OutlineColorR]').attr('for', 'inputStyle'+s+'OutlineColorR');
          $(div).find('label[for=inputStyle0OutlineColorG]').attr('for', 'inputStyle'+s+'OutlineColorG');
          $(div).find('label[for=inputStyle0OutlineColorB]').attr('for', 'inputStyle'+s+'OutlineColorB');
          $(div).find('label[for=inputStyle0Width]').attr('for', 'inputStyle'+s+'Width');
          $(div).find('label[for=inputStyle0Symbol]').attr('for', 'inputStyle'+s+'Symbol');
          $(div).find('label[for=inputStyle0Size]').attr('for', 'inputStyle'+s+'Size');
          $(div).find('#inputStyle0ColorR').attr({ id: 'inputStyle'+s+'ColorR', name: 'style['+s+'+][color][r]' }).val(data.class[c].style[s].color.r);
          $(div).find('#inputStyle0ColorG').attr({ id: 'inputStyle'+s+'ColorG', name: 'style['+s+'+][color][g]' }).val(data.class[c].style[s].color.g);
          $(div).find('#inputStyle0ColorB').attr({ id: 'inputStyle'+s+'ColorB', name: 'style['+s+'+][color][b]' }).val(data.class[c].style[s].color.b);
          $(div).find('#inputStyle0OutlineColorR').attr({ id: 'inputStyle'+s+'OutlineColorR', name: 'style['+s+'+][outlinecolor][r]' }).val(data.class[c].style[s].outlinecolor.r);
          $(div).find('#inputStyle0OutlineColorG').attr({ id: 'inputStyle'+s+'OutlineColorG', name: 'style['+s+'+][outlinecolor][g]' }).val(data.class[c].style[s].outlinecolor.g);
          $(div).find('#inputStyle0OutlineColorB').attr({ id: 'inputStyle'+s+'OutlineColorB', name: 'style['+s+'+][outlinecolor][b]' }).val(data.class[c].style[s].outlinecolor.b);
          $(div).find('#inputStyle0Width').attr({ id: 'inputStyle'+s+'Width', name: 'style['+s+'+][width]' }).val(data.class[c].style[s].width);
          $(div).find('#inputStyle0Symbol').attr({ id: 'inputStyle'+s+'Symbol', name: 'style['+s+'+][symbolname]' }).val(data.class[c].style[s].symbolname);
          $(div).find('#inputStyle0Size').attr({ id: 'inputStyle'+s+'Size', name: 'style['+s+'+][size]' }).val(data.class[c].style[s].size);
        }
      }
    }
  });
  $('#modal-style').on('hidden.bs.modal', function(event) {
    $(this).data('_id', null); $('#modal-style form').trigger('reset');
  });
  $('#modal-style .modal-footer > .btn-default:eq(1)').on('click', function() {
    var s = $('#modal-style .style').length;
    var div = $('#style0').clone().appendTo('#modal-style form');
    $(div).attr('id', 'style'+s);
    $(div).find('legend').text('Style #'+(s+1));
    $(div).find('label[for=inputStyle0ColorR]').attr('for', 'inputStyle'+s+'ColorR');
    $(div).find('label[for=inputStyle0ColorG]').attr('for', 'inputStyle'+s+'ColorG');
    $(div).find('label[for=inputStyle0ColorB]').attr('for', 'inputStyle'+s+'ColorB');
    $(div).find('label[for=inputStyle0OutlineColorR]').attr('for', 'inputStyle'+s+'OutlineColorR');
    $(div).find('label[for=inputStyle0OutlineColorG]').attr('for', 'inputStyle'+s+'OutlineColorG');
    $(div).find('label[for=inputStyle0OutlineColorB]').attr('for', 'inputStyle'+s+'OutlineColorB');
    $(div).find('label[for=inputStyle0Width]').attr('for', 'inputStyle'+s+'Width');
    $(div).find('label[for=inputStyle0Symbol]').attr('for', 'inputStyle'+s+'Symbol');
    $(div).find('label[for=inputStyle0Size]').attr('for', 'inputStyle'+s+'Size');
    $(div).find('#inputStyle0ColorR').attr({ id: 'inputStyle'+s+'ColorR', name: 'style['+s+'][color][r]' }).val('');
    $(div).find('#inputStyle0ColorG').attr({ id: 'inputStyle'+s+'ColorG', name: 'style['+s+'][color][g]' }).val('');
    $(div).find('#inputStyle0ColorB').attr({ id: 'inputStyle'+s+'ColorB', name: 'style['+s+'][color][b]' }).val('');
    $(div).find('#inputStyle0OutlineColorR').attr({ id: 'inputStyle'+s+'OutlineColorR', name: 'style['+s+'][outlinecolor][r]' }).val('');
    $(div).find('#inputStyle0OutlineColorG').attr({ id: 'inputStyle'+s+'OutlineColorG', name: 'style['+s+'][outlinecolor][g]' }).val('');
    $(div).find('#inputStyle0OutlineColorB').attr({ id: 'inputStyle'+s+'OutlineColorB', name: 'style['+s+'][outlinecolor][b]' }).val('');
    $(div).find('#inputStyle0Width').attr({ id: 'inputStyle'+s+'Width', name: 'style['+s+'][width]' }).val('');
    $(div).find('#inputStyle0Symbol').attr({ id: 'inputStyle'+s+'Symbol', name: 'style['+s+'][symbolname]' }).val('');
    $(div).find('#inputStyle0Size').attr({ id: 'inputStyle'+s+'Size', name: 'style['+s+'][size]' }).val('');
    $('#modal-style').modal('handleUpdate');
  });
  $('#modal-style .modal-footer > .btn-primary').on('click', function() {
    var id = $('#modal-class').data('_id');
    var c_id = $('#modal-style').data('_id');
    var c = $('#'+c_id).index('.class');

    var data = $('#modal-style form').serializeObject();

    var _data = $('.layer:eq('+id+')').data();
    if (typeof(_data.class) == 'undefined') _data.class = [];
    if (typeof(_data.class[c]) == 'undefined') _data.class[c] = {};
    _data.class[c].style = data.style;
    $('.layer:eq('+id+')').data(_data);

    update(function() { $('#modal-style').modal('hide'); });
  });
});
