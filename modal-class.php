<div id="modal-class" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title"><i class="fa fa-paint-brush"></i> Layer - Class</h3>
      </div>
      <div class="modal-body">
        <form autocomplete="off" class="form-horizontal">
          <div class="form-group">
            <label for="inputLayerScaleDenom" class="col-sm-3 control-label">Display range</label>
            <div class="col-sm-4">
              <input type="text" class="form-control" id="inputLayerMinScaleDenom" name="minscaledenom" placeholder="Min. Scale Denom.">
            </div>
            <div class="col-sm-1 text-center" style="line-height:34px;">
              <span class="glyphicon glyphicon-arrow-right"></span>
            </div>
            <div class="col-sm-4">
              <input type="text" class="form-control" id="inputLayerMaxScaleDenom" name="maxscaledenom" placeholder="Max. Scale Denom.">
            </div>
          </div>
          <div class="form-group">
            <label for="selectLayerOpacity" class="col-sm-3 control-label">Opacity</label>
            <div class="col-sm-9">
              <input type="range" class="form-control" id="inputLayerOpacity" min="0" max="100" step="10" name="opacity" value="100">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerClassItem" class="col-sm-3 control-label">Class item</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerClassItem" name="classitem">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerLabelItem" class="col-sm-3 control-label">Label item</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerLabelItem" name="labelitem">
            </div>
          </div>
          <hr>
          <fieldset class="class" id="class0">
            <legend>Class #1</legend>
            <div class="form-group">
              <label for="inputClass0Name" class="col-sm-3 control-label">Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="inputClass0Name" name="class[0][name]">
              </div>
            </div>
            <div class="form-group">
              <label for="inputClass0Expression" class="col-sm-3 control-label">Expression</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="inputClass0Expression" name="class[0][expression]">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-2 col-sm-offset-3"><a class="btn btn-default btn-block" href="#modal-label" data-toggle="modal"><i class="fa fa-tag"></i> Label</a></div>
              <div class="col-sm-2"><a class="btn btn-default btn-block" href="#modal-style" data-toggle="modal"><i class="fa fa-picture-o"></i> Style</a></div>
            </div>
          </fieldset>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default">Add class</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
