
<div id="leaves_tab" class="form-tab">
    <h5>LEAVES TAB</h5>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label>SELECT LEAVE TYPE</label>
                <div class="leave_type">
                    <select name="leave_type" id="leave_type" class="form-control">
                        <option value="">PLEASE SELECT LEAVE TYPE</option>
                        @foreach ($leave_type as $item)
                            <option value="{{$item->id}}">{{$item->leave_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label>TOTAL DAYS</label>
                <div class="total_hours">
                    <input type="number" id="total_hours" name="total_hours" class="form-control"/>
                </div>
            </div>
        </div>
    </div>
    
    <div class="leave-table">
        <div class="row">
            <div class="col-12">
                <table id="leaves_table" class="table table-striped" style="width:100%">
                </table>
            </div>
        </div>
    </div>
</div>
