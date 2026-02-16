<div id="workCalendarScreen" class="content-employee">
    <h5>WORK CALENDAR</h5>
    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col-md-4 form-group mb-1">
                            <label for="wc_preset_list">PRE-SAVED WORK CALENDAR</label>
                            <select id="wc_preset_list" class="form-control form-control-sm">
                                <option value="">Select Preset</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-1">
                            <label for="wc_time_in">TIME IN</label>
                            <input type="time" id="wc_time_in" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 form-group mb-1">
                            <label for="wc_time_off">TIME OFF</label>
                            <input type="time" id="wc_time_off" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 form-group mb-1 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-primary mr-1" onclick="applyCalendarPreset()">APPLY PRESET</button>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-4 form-group mb-0">
                            <label for="wc_preset_name">WORK CALENDAR NAME</label>
                            <input type="text" id="wc_preset_name" class="form-control form-control-sm" placeholder="WEEKDAY 8AM-5PM">
                        </div>
                        <div class="col-md-4 form-group mb-0 d-flex align-items-end">
                            <label class="mb-0">
                                <input type="checkbox" id="wc_is_flexi_time">
                                FLEXI TIME (No tardiness deduction, undertime applies if below 9 hours)
                            </label>
                        </div>
                        <div class="col-md-4 form-group mb-0 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-success" onclick="saveWorkCalendarPreset()">SAVE AS PRE-SAVED WORK CALENDAR</button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="mb-1 d-block">SELECT DAYS</label>
                            <div class="d-flex flex-wrap" id="wc_days_selector">
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="monday"> MON</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="tuesday"> TUE</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="wednesday"> WED</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="thursday"> THU</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="friday"> FRI</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="saturday"> SAT</label>
                                <label class="mr-3"><input type="checkbox" class="wc-day-checkbox" value="sunday"> SUN</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="sc-table">
                <thead>
                    <th>Day</th>
                    <th>Time In</th>
                    <th>Time Off</th>
                </thead>
                <tbody>
                    <tr>
                        <td>Sunday</td>
                        <td><input type="time" id="sunday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="sunday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Monday</td>
                        <td><input type="time" id="monday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="monday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Tuesday</td>
                        <td><input type="time" id="tuesday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="tuesday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Wednesday</td>
                        <td><input type="time" id="wednesday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="wednesday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Thursday</td>
                        <td><input type="time" id="thursday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="thursday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Friday</td>
                        <td><input type="time" id="friday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="friday_end_time" class="form-control" disabled></td>
                    </tr>
                    <tr>
                        <td>Saturday</td>
                        <td><input type="time" id="saturday_start_time" class="form-control" disabled></td>
                        <td><input type="time" id="saturday_end_time" class="form-control" disabled></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
