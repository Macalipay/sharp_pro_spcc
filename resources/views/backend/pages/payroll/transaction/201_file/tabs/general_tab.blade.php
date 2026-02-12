
<div id="general_tab" class="form-tab">
    <h3>GENERAL TAB</h3>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <div class="employee-picture">
                    <label>PERSONNEL PICTURE</label>
                    <div id="" onclick="$('#profile_img').click()">
                        <img src="/images/payroll/201-file/default.png" alt="" width="200px" id="viewer" class="image-previewer" data-cropzee="profile_img">
                    </div>
                    <input id="profile_img" type="file" name="profile_img" class="form-control" onchange="scion.fileView(event)" style="display:none;">
                    <button class="btn btn-primary" type="button" onclick="$('#profile_img').click()" style="width:100%;">Select Photo</button>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                @include('backend.partial.component.lookup', [
                    'label' => "EMPLOYEE NUMBER", 
                    'placeholder' => '<NEW>',
                    'id' => "employee_no", 
                    'title' => "EMPLOYEE NUMBER", 
                    'url' => "/payroll/201-file/get", 
                    'data' => array(
                        array('data' => "DT_RowIndex", 'title' => "#"), 
                        array('data' => "employee_no", 'title' => "Employee Number"),
                        array('data' => "full_name", 'title' => "Name"),
                        array('data' => "email", 'title' => "Email"),
                    ),
                    'disable' => true,
                    'lookup_module' => '201-file',
                    'modal_type'=> 'all',
                    'lookup_type' => 'main'
                ])
            </div>
        </div>
        <div class="col-6">
            <div class="form-group status">
                <label>STATUS <span class="required">*</span></label>
                <select name="status" id="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">In-active</option>
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group firstname">
                <label>FIRST NAME <span class="required">*</span></label>
                <input type="text" class="form-control" name="firstname" id="firstname"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group middlename">
                <label>MIDDLE NAME</label>
                <input type="text" class="form-control" name="middlename" id="middlename"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group lastname">
                <label>LAST NAME <span class="required">*</span></label>
                <input type="text" class="form-control" name="lastname" id="lastname"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group suffix">
                <label>SUFFIX</label>
                <input type="text" class="form-control" name="suffix" id="suffix"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group birthdate">
                <label>BIRTHDATE <span class="required">*</span></label>
                <input type="date" class="form-control" name="birthdate" id="birthdate"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group gender">
                <label>GENDER <span class="required">*</span></label>
                <select name="gender" id="gender" class="form-control">
                    <option value="male">MALE</option>
                    <option value="female">FEMALE</option>
                </select>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group citizenship">
                <label>CITIZENSHIP <span class="required">*</span></label>
                <input type="text" class="form-control" name="citizenship" id="citizenship"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group phone1">
                <label>CONTACT NUMBER 1 <span class="required">*</span></label>
                <input type="text" class="form-control" name="phone1" id="phone1"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group phone2">
                <label>CONTACT NUMBER 2</label>
                <input type="text" class="form-control" name="phone2" id="phone2"/>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group email">
                <label>EMAIL ADDRESS</label>
                <input type="email" class="form-control" name="email" id="email"/>
            </div>
        </div>

        <h3 class="col-12 form-title">ADDRESS 1</h3>
        <div class="col-4">
            <div class="form-group street_1">
                <label>STREET NO. <span class="required">*</span></label>
                <input type="text" class="form-control" name="street_1" id="street_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group barangay_1">
                <label>BARANGAY <span class="required">*</span></label>
                <input type="text" class="form-control" name="barangay_1" id="barangay_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group city_1">
                <label>CITY <span class="required">*</span></label>
                <input type="text" class="form-control" name="city_1" id="city_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group province_1">
                <label>PROVINCE <span class="required">*</span></label>
                <input type="text" class="form-control" name="province_1" id="province_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group country_1">
                <label>COUNTRY <span class="required">*</span></label>
                <input type="text" class="form-control" name="country_1" id="country_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group zip_1">
                <label>ZIP CODE <span class="required">*</span></label>
                <input type="text" class="form-control" name="zip_1" id="zip_1"/>
            </div>
        </div>
        
        <h3 class="col-12 form-title">ADDRESS 2</h3>
        <div class="col-4">
            <div class="form-group street_2">
                <label>STREET NO.</label>
                <input type="text" class="form-control" name="street_2" id="street_2"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group barangay_2">
                <label>BARANGAY</label>
                <input type="text" class="form-control" name="barangay_2" id="barangay_2"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group city_2">
                <label>CITY</label>
                <input type="text" class="form-control" name="city_2" id="city_2"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group province_2">
                <label>PROVINCE</label>
                <input type="text" class="form-control" name="province_2" id="province_2"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group country_2">
                <label>COUNTRY</label>
                <input type="text" class="form-control" name="country_2" id="country_2"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group zip_2">
                <label>ZIP CODE</label>
                <input type="text" class="form-control" name="zip_2" id="zip_2"/>
            </div>
        </div>

        <h3 class="col-12 form-title">MARITAL INFORMATION</h3>
        <div class="col-6">
             <div class="form-group marital_status">
                <label>MARITAL STATUS <span class="required">*</span></label>
                <select name="marital_status" id="marital_status" class="form-control">
                    <option value="SINGLE">SINGLE</option>
                    <option value="MARRIED">MARRIED</option>
                    <option value="DIVORCED">DIVORCED</option>
                    <option value="SEPARATED">SEPARATED</option>
                    <option value="WIDOWED">WIDOWED</option>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group spouse_name">
                <label>SPOUSE'S NAME</label>
                <input type="text" class="form-control" name="spouse_name" id="spouse_name"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group spouse_employer">
                <label>SPOUSE'S EMPLOYER</label>
                <input type="text" class="form-control" name="spouse_employer" id="spouse_employer"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group spouse_workphone">
                <label>SPOUSE'S WORK PHONE</label>
                <input type="text" class="form-control" name="spouse_workphone" id="spouse_workphone"/>
            </div>
        </div>

        <h3 class="col-12 form-title">JOB INFORMATION</h3>
        <div class="col-6">
            <div class="form-group job_title">
                <label>JOB TITLE</label>
                <input type="text" class="form-control" name="spouse_workphone" id="spouse_workphone"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group job_supervisor">
                <label>SUPERVISOR</label>
                <input type="text" class="form-control" name="job_supervisor" id="job_supervisor"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group work_location">
                <label>WORK LOCATION</label>
                <input type="text" class="form-control" name="work_location" id="work_location"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group work_email">
                <label>WORK EMAIL</label>
                <input type="text" class="form-control" name="work_email" id="work_email"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group work_telephone">
                <label>WORK TELEPHONE</label>
                <input type="text" class="form-control" name="work_telephone" id="work_telephone"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group work_mobile">
                <label>WORK MOBILE</label>
                <input type="text" class="form-control" name="work_mobile" id="work_mobile"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group work_startdate">
                <label>START DATE <span class="required">*</span></label>
                <input type="date" class="form-control" name="work_startdate" id="work_startdate"/>
            </div>
        </div>
        
        <h3 class="col-12 form-title">EMERGENCY CONTACT</h3>
        <div class="col-6">
            <div class="form-group emergency_name">
                <label>NAME</label>
                <input type="text" class="form-control" name="emergency_name" id="emergency_name"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group emergency_no">
                <label>CONTACT NUMBER</label>
                <input type="text" class="form-control" name="emergency_no" id="emergency_no"/>
            </div>
        </div>

        <h3 class="col-12 form-title">BENEFITS AND OTHERS</h3>
        <div class="col-12">
            <div class="form-group bank_account">
                <label>BANK ACCOUNT</label>
                <input type="text" class="form-control" name="bank_account" id="bank_account"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group tin_number">
                <label>TIN NUMBER</label>
                <input type="text" class="form-control" name="tin_number" id="tin_number"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group sss_number">
                <label>SSS NUMBER</label>
                <input type="text" class="form-control" name="sss_number" id="sss_number"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group pagibig_number">
                <label>PAGIBIG NUMBER</label>
                <input type="text" class="form-control" name="pagibig_number" id="pagibig_number"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group philhealth">
                <label>PHILHEALTH</label>
                <input type="text" class="form-control" name="philhealth" id="philhealth"/>
            </div>
        </div>

        
    </div>
</div>