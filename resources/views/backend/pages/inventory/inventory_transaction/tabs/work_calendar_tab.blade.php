
<div id="work_calendar_tab" class="form-tab">
    <h5>WORK CALENDAR TAB</h5>
    <div class="row">
        <div class="col-12">
            <div class="text-right form-group">
                <input type="checkbox" id="edit_schedule" onchange="customSchedule()"/> <label for="edit_schedule">CUSTOM SCHEDULE</label>
            </div>
            <table class="sc-table">
                <thead>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('sunday')"><i class="fas fa-clock"></i></button> Sunday
                        </td>
                        <td>
                            <input type="time" id="sunday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="sunday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('monday')"><i class="fas fa-clock"></i></button> Monday
                        </td>
                        <td>
                            <input type="time" id="monday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="monday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('tuesday')"><i class="fas fa-clock"></i></button> Tuesday
                        </td>
                        <td>
                            <input type="time" id="tuesday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="tuesday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('wednesday')"><i class="fas fa-clock"></i></button> Wednesday
                        </td>
                        <td>
                            <input type="time" id="wednesday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="wednesday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('thursday')"><i class="fas fa-clock"></i></button> Thursday
                        </td>
                        <td>
                            <input type="time" id="thursday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="thursday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('friday')"><i class="fas fa-clock"></i></button> Friday
                        </td>
                        <td>
                            <input type="time" id="friday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="friday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" onclick="workTypeModal('saturday')"><i class="fas fa-clock"></i></button> Saturday
                        </td>
                        <td>
                            <input type="time" id="saturday_start_time" class="form-control" disabled>
                        </td>
                        <td>
                            <input type="time" id="saturday_end_time" class="form-control" disabled>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
