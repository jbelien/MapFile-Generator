<?php require_once('library/map.php'); use MapFile\Layer; ?>
<div id="modal-data" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title"><i class="fa fa-database"></i> Layer - Data</h3>
      </div>
      <div class="modal-body">
        <form autocomplete="off" class="form-horizontal">
          <div class="form-group">
            <label for="selectLayerProj" class="col-sm-3 control-label">Projection</label>
            <div class="col-sm-9">
              <select class="form-control" id="selectLayerProj" name="projection">
                <option value="epsg:3857">EPSG:3857 - Spherical Mercator</option>
                <option value="epsg:4326">EPSG:4326 - WGS 84</option>
                <option value="epsg:31370">EPSG:31370 - Belge 1972 / Belgian Lambert 72</option>
                <option value="epsg:900913">EPSG:900913 - Spherical Mercator</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="selectLayerConnectionType" class="col-sm-3 control-label">Connection type</label>
            <div class="col-sm-9">
              <select class="form-control" id="selectLayerConnectionType" name="connectiontype">
                <?php if ($mapscript) { ?>
                <option value="<?= MS_INLINE ?>">Inline</option>
                <option value="<?= MS_GRATICULE ?>" disabled="disabled">Graticule (not yet supported)</option>
                <option value="<?= MS_OGR ?>">OGR</option>
                <option value="<?= MS_ORACLESPATIAL ?>" disabled="disabled">OracleSpatial (not yet supported)</option>
                <option value="<?= MS_PLUGIN ?>" disabled="disabled">Plugin (not yet supported)</option>
                <option value="<?= MS_POSTGIS ?>" disabled="disabled">PostGIS (not yet supported)</option>
                <option value="<?= MS_RASTER ?>" disabled="disabled">Raster (not yet supported)</option>
                <option value="<?= MS_SDE ?>" disabled="disabled">SDE (not yet supported)</option>
                <option value="<?= MS_SHAPEFILE ?>">Shapefile</option>
                <?php /*<option value="<?= MS_TILED_OGR ?>">Tiled OGR</option> */ ?>
                <option value="<?= MS_TILED_SHAPEFILE ?>">Tiled shapefile</option>
                <option value="<?= MS_UNION ?>" disabled="disabled">Union (not yet supported)</option>
                <option value="<?= MS_WFS ?>">WFS</option>
                <option value="<?= MS_WMS ?>">WMS</option>
                <?php } else { ?>
                <option value="<?= Layer::CONNECTIONTYPE_CONTOUR ?>" disabled="disabled">Contour (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_LOCAL ?>">Local</option>
                <option value="<?= Layer::CONNECTIONTYPE_OGR ?>">OGR</option>
                <option value="<?= Layer::CONNECTIONTYPE_ORACLESPATIAL ?>" disabled="disabled">OracleSpatial (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_PLUGIN ?>" disabled="disabled">Plugin (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_POSTGIS ?>" disabled="disabled">PostGIS (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_SDE ?>" disabled="disabled">SDE (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_UNION ?>" disabled="disabled">Union (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_UVRASTER ?>" disabled="disabled">UV Raster (not yet supported)</option>
                <option value="<?= Layer::CONNECTIONTYPE_WFS ?>">WFS</option>
                <option value="<?= Layer::CONNECTIONTYPE_WMS ?>">WMS</option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerConnection" class="col-sm-3 control-label">Connection</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerConnection" name="connection">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerData" class="col-sm-3 control-label">Data</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerData" name="data">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerFilterItem" class="col-sm-3 control-label">Filter item</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerFilterItem" name="filteritem">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerFilter" class="col-sm-3 control-label">Filter</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerFilter" name="filter">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLayerGroup" class="col-sm-3 control-label">Group</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="inputLayerGroup" name="group">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
