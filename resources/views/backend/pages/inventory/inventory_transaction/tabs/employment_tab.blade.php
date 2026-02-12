
<div id="employment_tab" class="form-tab">
    <h3>EMPLOYMENT TAB</h3>
    <div class="row">
        <div class="col-xl-4 col-md-12">
            <div class="form-group classes_id">
                <label>WORK CLASS:<span class="required">*</span></label>
                <select name="classes_id" id="classes_id" class="form-control">
                    <option value="">Please select classes</option>
                    @foreach ($classes as $item)
                    <option value="{{$item->id}}">{{$item->description}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-xl-4 col-md-12">
            <div class="form-group position_id">
                <label>POSITION:<span class="required">*</span></label>
                <select name="position_id" id="position_id" class="form-control">
                    <option value="">Please select position</option>
                    @foreach ($position as $item)
                    <option value="{{$item->id}}">{{$item->description}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-xl-4 col-md-12">
            <div class="form-group department_id">
                <label>DEPARTMENT:<span class="required">*</span></label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">Please select department</option>
                    @foreach ($department as $item)
                    <option value="{{$item->id}}">{{$item->description}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-xl-6 col-md-12">
            <div class="form-group employment_date">
                <label>DATE OF EMPLOYMENT:<span class="required">*</span></label>
                <input type="date" class="form-control" id="employment_date" name="employment_date" max="9999-12-31">
            </div>
        </div>
        <div class="col-xl-6 col-md-12">
            <div class="form-group tax_rate">
                <label>TAX RATE:</label>
                <input type="number" class="form-control" id="tax_rate" name="tax_rate">
            </div>
        </div>
        <div class="col-xl-12 col-md-12">
            <div class="form-group payroll_calendar_id">
                <label>PAYROLL CALENDAR:<span class="required">*</span></label>
                <select name="payroll_calendar_id" id="payroll_calendar_id" class="form-control">
                    <option value="" style="display:none;">PLEASE SELECT PAYROLL CALENDAR</option>
                    <option value="0"></option>
                    @foreach ($payroll_calendar as $item)
                    <option value="{{$item->id}}">{{$item->title}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label>EARNINGS</label>
                <div class="earning">
                    @foreach ($earning as $item)
                        <span class="earning-item" id="earning_{{$item->id}}" onclick="earningSetup({{$item->id}})">{{$item->name}}</span>
                    @endforeach
                </div>
            </div>
            {{-- <div class="form-group">
                <label>ALLOWANCE</label>
                <div class="allowance">
                    @foreach ($allowance as $item)
                        <span class="allowance-item" id="allowance_{{$item->id}}" onclick="allowanceSetup({{$item->id}})">{{$item->name}}</span>
                    @endforeach
                </div>
            </div> --}}
        </div>
    </div>
</div>