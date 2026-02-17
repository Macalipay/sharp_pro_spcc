<div id="workHistoryScreen" class="content-employee">
    <h5>WORK HISTORY</h5>
    
    <div class="row">
        <div class="col-6 form-group">
            <label for="educational_attainment">COMPANY</label>
            <input type="text" class="form-control form-control-sm" id="company" name="company"/>
        </div>
        <div class="col-6 form-group">
            <label for="position">POSITION</label>
            <input type="text" class="form-control form-control-sm" id="position" name="position"/>
        </div>
        <div class="col-6 form-group">
            <label for="date_hired">DATE HIRED</label>
            <input type="date" class="form-control form-control-sm" id="date_hired" name="date_hired"/>
        </div>
        <div class="col-6 form-group">
            <label for="date_of_resignation">DATE OF RESIGNATION</label>
            <input type="date" class="form-control form-control-sm" id="date_of_resignation" name="date_of_resignation"/>
        </div>
        <div class="col-12 form-group">
            <label for="date_of_resignation">REMARKS</label>
            <textarea class="form-control form-control-sm" id="remarks" name="remarks"></textarea>
        </div>
        <div class="col-12 form-group attachment">
            <label for="work_history_attachment">ATTACHMENT (PDF, JPEG, PNG, DOC, DOCX | MAX 25MB)</label>
            <input type="file" class="form-control form-control-sm" id="work_history_attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"/>
            <input type="hidden" id="work_history_attachment_data" name="attachment_data"/>
            <input type="hidden" id="work_history_attachment_name" name="attachment_name"/>
            <input type="hidden" id="work_history_attachment_mime" name="attachment_mime"/>
        </div>

        <div class="col-12 form-group">
            <div class="history-container">
                {{-- WORK HISTORY CONTENT LIST --}}
            </div>
        </div>
    </div>
</div>
