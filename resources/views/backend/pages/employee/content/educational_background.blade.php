<div id="educationalBackgroundScreen" class="content-employee">
    <h5>EDUCATIONAL BACKGROUND</h5>

    <div class="row">
        <div class="col-6 form-group educational_attainment">
            <label for="educational_attainment">EDUCATIONAL ATTAINMENT <span class="required">*</span></label>
            <input type="text" class="form-control form-control-sm" id="educational_attainment" name="educational_attainment"/>
        </div>
        <div class="col-6 form-group course">
            <label for="course">COURSE</label>
            <input type="text" class="form-control form-control-sm" id="course" name="course"/>
        </div>
        <div class="col-6 form-group school_year">
            <label for="school_year">SCHOOL YEAR</label>
            <input type="text" class="form-control form-control-sm" id="school_year" name="school_year"/>
        </div>
        <div class="col-6 form-group school">
            <label for="school">SCHOOL</label>
            <input type="text" class="form-control form-control-sm" id="school" name="school"/>
        </div>
        <div class="col-12 form-group attachment">
            <label for="educational_attachment">ATTACHMENT (PDF, JPEG, PNG, DOC, DOCX | MAX 25MB)</label>
            <input type="file" class="form-control form-control-sm" id="educational_attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"/>
            <input type="hidden" id="educational_attachment_data" name="attachment_data"/>
            <input type="hidden" id="educational_attachment_name" name="attachment_name"/>
            <input type="hidden" id="educational_attachment_mime" name="attachment_mime"/>
        </div>

        <div class="col-12 form-group">
            <div class="background-container">
                {{-- EDUCATIONAL BACKGROUND CONTENT LIST --}}
            </div>
        </div>
    </div>
</div>
