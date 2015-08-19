/* ***********************************************
 *
 */
var modified = false;
window.onbeforeunload = function (e) {
  if (modified === true) {
    var e = e || window.event;
    // For IE and Firefox
    if (e) { e.returnValue = 'You have unsaved changes.'; }
    // For Safari
    return 'You have unsaved changes.';
  }
};

/* ***********************************************
 *
 */
$(document).ready(function() {
  $('#mapfile-save').on('click', function() { modified = false; });

  $('input[name=wms]').on('click', function() { if ($(this).prop('checked') == true) $('.wms-control').show(); else { $('.wms-control').hide(); $('.wms-control input').val(''); } });
  $('#selectProj').on('change', function() {
    var data = $(this).find('option:selected').data();
    if (typeof(data.minx) != 'undefined' && typeof(data.miny) != 'undefined' && typeof(data.maxx) != 'undefined' && typeof(data.maxy) != 'undefined') {
      $('#inputExtentMinX').val(data.minx); $('#inputExtentMinY').val(data.miny);
      $('#inputExtentMaxX').val(data.maxx); $('#inputExtentMaxY').val(data.maxy);
    }
  });

  $('#editor > form input, #editor > form select').on('change', function() {
    $('#editor form').trigger('submit');
  });
  $('#editor > form').on('submit', function(event) {
    event.preventDefault();
    update();
  });

  //if ($('#inputExtentMinX').val() == -1 && $('#inputExtentMinY').val() == -1 && $('#inputExtentMaxX').val() == -1 && $('#inputExtentMaxY').val() == -1) $('#selectProj').trigger('change');
});

/**
 *
 */
$(document).on('change', '#editor tbody > tr:last input', function(event) {
  var l = $('#editor .layer').length;
  var _tr = $('#editor .layer:last');

  var tr = $(_tr).clone().appendTo('#editor tbody');
  $(tr).find('input').val('');
  $(tr).find('select').find('option:not(:disabled):first').prop('selected', true);

  $(_tr).find('.dropdown-toggle.disabled').removeClass('disabled');
});

/**
 *
 */
$(document).on('click', 'a[href="#move-up"]', function(event) {
  event.preventDefault();
  var layers = $('#editor .layer');
  var l = $(this).closest('.layer'), i = $(layers).index(l);

  if (i > 0) {
    $.post((mapscript ? 'mapscript.php' : 'library.php'), { up: i }, function(map) {
      $('#mapfile > pre').text(map);
      $('#mapfile > .text-info:last').text('Last update : '+new Date().toLocaleString())

      $('#map img:first').attr('src', $('#map').data('url')+'&mode=map&layers=all&'+Date.now());
      $('#map-scalebar').attr('src', $('#map').data('url')+'&mode=scalebar&layers=all&'+Date.now());
      $('#map-legend').attr('src', $('#map').data('url')+'&mode=legend&layers=all&'+Date.now());

      l.insertBefore($(layers).get(i-1));
    });
  }
});

/**
 *
 */
$(document).on('click', 'a[href="#move-down"]', function(event) {
  event.preventDefault();
  var layers = $('#editor .layer');
  var l = $(this).closest('.layer'), i = $(layers).index(l);

  if (i < (layers.length-2)) {
    $.post((mapscript ? 'mapscript.php' : 'library.php'), { down: i }, function(map) {
      $('#mapfile > pre').text(map);
      $('#mapfile > .text-info:last').text('Last update : '+new Date().toLocaleString())

      $('#map img:first').attr('src', $('#map').data('url')+'&mode=map&layers=all&'+Date.now());
      $('#map-scalebar').attr('src', $('#map').data('url')+'&mode=scalebar&layers=all&'+Date.now());
      $('#map-legend').attr('src', $('#map').data('url')+'&mode=legend&layers=all&'+Date.now());

      l.insertAfter($(layers).get(i+1));
    });
  }
});

/**
 *
 */
$(document).on('click', 'a[href="#delete"]', function(event) {
  event.preventDefault();
  var layers = $('#editor .layer');
  var l = $(this).closest('.layer'), i = $(layers).index(l);

  $.post((mapscript ? 'mapscript.php' : 'library.php'), { delete: i }, function(map) {
    $('#mapfile > pre').text(map);
    $('#mapfile > .text-info:last').text('Last update : '+new Date().toLocaleString())

    $('#map img:first').attr('src', $('#map').data('url')+'&mode=map&layers=all&'+Date.now());
    $('#map-scalebar').attr('src', $('#map').data('url')+'&mode=scalebar&layers=all&'+Date.now());
    $('#map-legend').attr('src', $('#map').data('url')+'&mode=legend&layers=all&'+Date.now());

    $(l).remove();
  });
});

/**
 *
 */
/*
$(document).on('click', 'a[href="#duplicate"]', function(event) {
  event.preventDefault();

  var l = $('#editor .layer').length;
  var data = $(this).closest('.layer').data();

  var tr = $(this).closest('.layer').clone().data(data).insertBefore('#editor tbody > tr:last');
  $(tr).attr('id', 'layer'+l);
  $(tr).find('input').attr({ id: 'inputLayer'+l+'Name', name: 'layers['+l+'][name]' });
  $(tr).find('select').attr({ id: 'selectLayer'+l+'Type', name: 'layers['+l+'][type]' });

  update();
});
*/

/**
 *
 */
function update(callback) {
  modified = true;

  var data = $('#editor > form').serializeObject();
  data.layers = new Array();
  $('.layer').each(function(i) {
    data.layers[i] = $(this).data();
    data.layers[i].name = $(this).find('input[name=layer_name]').val();
    data.layers[i].type = $(this).find('select[name=layer_type]').val();
  });

  $.post((mapscript ? 'mapscript.php' : 'library.php'), data, function(map) {
    $('#mapfile > pre').text(map);
    $('#mapfile > .text-info:last').text('Last update : '+new Date().toLocaleString())

    $('#map img:first').attr('src', $('#map').data('url')+'&mode=map&layers=all&'+Date.now());
    $('#map-scalebar').attr('src', $('#map').data('url')+'&mode=scalebar&layers=all&'+Date.now());
    $('#map-legend').attr('src', $('#map').data('url')+'&mode=legend&layers=all&'+Date.now());
  });

  if (typeof(callback) == 'function') callback();
}
