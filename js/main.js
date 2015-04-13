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
      $('#inputExtentMinX').val(data.minx);
      $('#inputExtentMinY').val(data.miny);
      $('#inputExtentMaxX').val(data.maxx);
      $('#inputExtentMaxY').val(data.maxy);
    }
  });

  $('#editor tbody > tr:last input').on('change', function(event) {
    var l = $('#editor .layer').length;
    var _tr = $('#editor .layer:last');

    var tr = $(_tr).clone().appendTo('#editor tbody');
    $(tr).attr('id', 'layer'+l);
    $(tr).find('input').attr({ id: 'inputLayer'+l+'Name', name: 'layers['+l+'][name]' }).val('');
    $(tr).find('select').attr({ id: 'selectLayer'+l+'Type', name: 'layers['+l+'][type]' }).find('option:not(:disabled):first').prop('selected', true);

    $(_tr).find('.dropdown-toggle.disabled').removeClass('disabled');
  });

  $('#editor form input, #editor form select').on('change', function() {
    $('#editor form').trigger('submit');
  });
  $('#editor form').on('submit', function(event) {
    event.preventDefault();
    update();
  });

  $('a[href="#delete"]').on('click', function(event) {
    event.preventDefault();
    var id = $(this).closest('.layer').remove();
    update();
  });

  $('a[href="#duplicate"]').on('click', function(event) {
    event.preventDefault();

    var l = $('#editor .layer').length;
    var data = $(this).closest('.layer').data();

    var tr = $(this).closest('.layer').clone().data(data).insertBefore('#editor tbody > tr:last');
    $(tr).attr('id', 'layer'+l);
    $(tr).find('input').attr({ id: 'inputLayer'+l+'Name', name: 'layers['+l+'][name]' });
    $(tr).find('select').attr({ id: 'selectLayer'+l+'Type', name: 'layers['+l+'][type]' });

    update();
  });
});

/* ***********************************************
 *
 */
function update(callback) {
  modified = true;

  var data = $('#editor form').serializeObject();

  $('.layer').each(function(i) {
    var n = data.layers[i].name, t = data.layers[i].type;
    $.extend(data.layers[i], $(this).data());
    data.layers[i].name = n; data.layers[i].type = t;
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
