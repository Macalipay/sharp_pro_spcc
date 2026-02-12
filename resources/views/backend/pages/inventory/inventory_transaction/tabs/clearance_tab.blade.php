
<div id="clearance_tab" class="form-tab">
    <h3>CLEARANCE</h3>
    <div class="row">
        <div class="col-md-3 mb-5">
            <label for="clearance_date">Clearance Date:</label>
            <input type="date" class="form-control form-control-sm" name="clearance_date" id="clearance_date"/>
        </div>
        <div class="col-md-12">
            <label for="clearance_date">Clearance List:</label>

            @foreach ($clearance as $item)
                <div class="clearanc-item">
                    {{$item['name']}}
                </div>
            @endforeach
        </div>
    </div>
</div>