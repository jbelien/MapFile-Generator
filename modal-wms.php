<div id="modal-wms" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title"><i class="fa fa-globe"></i> Layer - WMS</h3>
      </div>
      <div class="modal-body">
        <form autocomplete="off" class="form-horizontal">
          <div class="form-group">
            <label for="inputLayerWMSTitle" class="col-sm-3 control-label">Title</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerWMSTitle" name="wms_title">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerWMSAbstract" class="col-sm-3 control-label">Abstract</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerWMSAbstract" name="wms_abstract">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerWMSIncludeItems" class="col-sm-3 control-label">Include items</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerWMSIncludeItems" name="wms_include_items">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerWMSExcludeItems" class="col-sm-3 control-label">Exclude items</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerWMSExcludeItems" name="wms_exclude_items">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerWMSAttributionTitle" class="col-sm-3 control-label">Attribution title</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerWMSAttributionTitle" name="wms_attribution_title">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerWMSAttributionOnlineResource" class="col-sm-3 control-label">Attribution online resource</label>
            <div class="col-sm-9">
              <input type="url" class="form-control" id="inputLayerWMSAttributionOnlineResource" name="wms_attribution_onlineresource">
            </div>
          </div>
          <!--
          <div class="form-group">
            <label for="inputLayerWMSRequest" class="col-sm-3 control-label">WMS Request</label>
            <div class="col-sm-9">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default active">
                  <input type="checkbox" name="wms_enable_request[]" value="GetCapabilities"> GetCapabilities
                </label>
                <label class="btn btn-default active">
                  <input type="checkbox" name="wms_enable_request[]" value="GetMap"> GetMap
                </label>
                <label class="btn btn-default active">
                  <input type="checkbox" name="wms_enable_request[]" value="GetFeatureInfo"> GetFeatureInfo
                </label>
                <label class="btn btn-default active">
                  <input type="checkbox" name="wms_enable_request[]" value="GetLegendGraphic"> GetLegendGraphic
                </label>
              </div>
            </div>
          </div>
          -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
