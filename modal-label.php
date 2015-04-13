<div id="modal-label" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title"><i class="fa fa-tag"></i> Layer - Label</h3>
      </div>
      <div class="modal-body">
        <form autocomplete="off" class="form-horizontal">
          <div class="form-group">
            <label for="selectLabelAlign" class="col-sm-3 control-label">Align</label>
            <div class="col-sm-9">
              <select class="form-control" id="selectLabelAlign" name="label[align]">
                <option value="<?= MS_ALIGN_LEFT ?>">Left</option>
                <option value="<?= MS_ALIGN_CENTER ?>">Center</option>
                <option value="<?= MS_ALIGN_RIGHT ?>">Right</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="selectLabelPosition" class="col-sm-3 control-label">Position</label>
            <div class="col-sm-9">
              <select class="form-control" id="selectLabelPosition" name="label[position]">
                <option value="<?= MS_AUTO ?>" selected="selected">AUTO</option>
                <option value="<?= MS_UL ?>">Upper Left</option>
                <option value="<?= MS_UC ?>">Upper Center</option>
                <option value="<?= MS_UR ?>">Upper Right</option>
                <option value="<?= MS_CL ?>">Center Left</option>
                <option value="<?= MS_CC ?>">Center Center</option>
                <option value="<?= MS_CR ?>">Center Right</option>
                <option value="<?= MS_LL ?>">Lower Left</option>
                <option value="<?= MS_LC ?>">Lower Center</option>
                <option value="<?= MS_LR ?>">Lower Right</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="inputLabelColor" class="col-sm-3 control-label">Color</label>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelColorR" name="label[color][r]"></div>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelColorG" name="label[color][g]"></div>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelColorB" name="label[color][b]"></div>
          </div>
          <div class="form-group">
            <label for="inputLabelOutlineColor" class="col-sm-3 control-label">Outline color</label>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelOutlineColorR" name="label[outlinecolor][r]"></div>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelOutlineColorG" name="label[outlinecolor][g]"></div>
            <div class="col-sm-3"><input type="text" class="form-control" id="inputLabelOutlineColorB" name="label[outlinecolor][b]"></div>
          </div>
          <div class="form-group">
            <label for="inputLabelScaleDenom" class="col-sm-3 control-label">Display range</label>
            <div class="col-sm-4">
              <input type="text" class="form-control" placeholder="Min. Scale Denom." id="inputLabelMinScaleDenom" name="label[minscaledenom]">
            </div>
            <div class="col-sm-1 text-center" style="line-height:34px;">
              <span class="glyphicon glyphicon-arrow-right"></span>
            </div>
            <div class="col-sm-4">
              <input type="text" class="form-control" placeholder="Max. Scale Denom." id="inputLabelMaxScaleDenom" name="label[maxcaledenom]">
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
