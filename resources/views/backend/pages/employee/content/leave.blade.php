<div id="leaveScreen" class="content-employee">
    <h5>LEAVE</h5>

    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>LEAVE TYPE</label>
                <select name="leave_type" id="leave_type" class="form-control">
                    <option value="">PLEASE SELECT LEAVE TYPE</option>
                    @foreach ($leave_type as $item)
                        <option value="{{$item->id}}">{{$item->leave_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>LEAVE ENTITLEMENT (DAYS)</label>
                <input type="number" min="0" step="0.5" id="total_hours" name="total_hours" class="form-control" />
            </div>
        </div>
    </div>

    <div class="mt-2">
        <h6 class="font-weight-bold mb-2">LEAVE ENTITLEMENT</h6>
        <div class="table-responsive">
            <table id="leave_entitlement_table" class="table table-striped table-sm" style="width:100%"></table>
        </div>
    </div>

    <div class="mt-3">
        <h6 class="font-weight-bold mb-2">LEAVE HISTORY</h6>
        <div class="table-responsive">
            <table id="leave_history_table" class="table table-striped table-sm" style="width:100%"></table>
        </div>
    </div>
</div>
