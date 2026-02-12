<div id="certificationScreen" class="content-employee">
    <h5>CERTIFICATION</h5>
    
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label>CERTIFICATION NO</label>
                <div class="total_hours">
                    <input type="text" id="certification_no" name="certification_no" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>NAME</label>
                <div class="total_hours">
                    <input type="text" id="certification_name" name="certification_name" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>CERTIFICATION AUTHORITY</label>
                <div class="total_hours">
                    <input type="text" id="certification_authority" name="certification_authority" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>DESCRIPTION</label>
                <div class="total_hours">
                    <input type="text" id="certification_description" name="certification_description" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>CERTIFICATION DATE</label>
                <div class="total_hours">
                    <input type="date" id="certification_date" name="certification_date" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>EXPIRATION DATE</label>
                <div class="total_hours">
                    <input type="date" id="certification_expiration_date" name="certification_expiration_date" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>CERTIFICATION LEVEL</label>
                <div class="total_hours">
                    <input type="text" id="certification_level" name="certification_level" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group status">
                <label>CERTIFICATION STATUS <span class="required">*</span></label>
                <select name="certification_status" id="certification_status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Expired">Expired</option>
                    <option value="Revoked">Revoked</option>
                </select>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>CERTIFICATION ACHIEVEMENTS</label>
                <div class="total_hours">
                    <input type="text" id="certification_achievements" name="certification_achievements" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>CERTIFICATION RENEWAL DATE</label>
                <div class="total_hours">
                    <input type="date" id="certification_renewal_date" name="certification_renewal_date" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>RECERTIFICATION DATE</label>
                <div class="total_hours">
                    <input type="date" id="recertification_date" name="recertification_date" class="form-control"/>
                </div>
            </div>
        </div>

        <div class="col-12 form-group">
            <div class="certification-container">
                {{-- CERTIFICATION CONTENT LIST --}}
            </div>
        </div>
    </div>
</div>