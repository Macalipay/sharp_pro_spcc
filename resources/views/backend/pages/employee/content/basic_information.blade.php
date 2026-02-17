<div id="basicInformationScreen" class="content-employee active-screen">
    <h5>BASIC INFORMATION</h5>

    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="employee-picture">
                <label>Employee photo</label>
                <div onclick="$('#profile_img').click()" style="cursor:pointer;max-width:200px;">
                    <img src="/images/payroll/employee-information/default.png" alt="Employee Photo" width="200" id="viewer" class="image-previewer" data-cropzee="profile_img">
                </div>
                <input id="profile_img" type="file" name="profile_img" class="form-control mt-2" style="max-width:200px;" accept=".jpg,.jpeg,.png,image/jpeg,image/png" onchange="validateProfileImageType(event);previewReducedImage(event);scion.fileView(event)">
                <small class="text-muted d-block mt-1" style="max-width:200px;">Allowed file types: JPG, JPEG, PNG. Recommended: 2x2 ID photo.</small>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-8 form-group employee_no">
                    <label for="employee_no">EMPLOYEE NO.:</label>
                    <input type="text" class="form-control form-control-sm" id="employee_no" name="employee_no" placeholder="NEW" readonly/>
                </div>

                <div class="col-md-4 form-group status">
                    <label for="status">STATUS: <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control form-control-sm" onchange="lookupReturn()">
                        <option value="1">ACTIVE</option>
                        <option value="0">IN-ACTIVE</option>
                        <option value="2">TERMINATED</option>
                        <option value="3">RESIGNED</option>
                        <option value="4">SUSPENDED</option>
                        <option value="5">DECEASED</option>
                        <option value="6">PROBATION</option>
                        <option value="7">ON-CALL</option>
                        <option value="8">INTERNSHIP/OJT</option>
                        <option value="9">END OF CONTRACT</option>
                    </select>
                </div>

                <div class="col-md-3 form-group firstname">
                    <label for="firstname">FIRST NAME: <span class="required">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="firstname" name="firstname"/>
                </div>

                <div class="col-md-3 form-group middlename">
                    <label for="middlename">MIDDLE NAME:</label>
                    <input type="text" class="form-control form-control-sm" id="middlename" name="middlename"/>
                </div>

                <div class="col-md-3 form-group lastname">
                    <label for="lastname">LAST NAME: <span class="required">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="lastname" name="lastname"/>
                </div>

                <div class="col-md-3 form-group suffix">
                    <label for="suffix">SUFFIX:</label>
                    <input type="text" class="form-control form-control-sm" id="suffix" name="suffix"/>
                </div>

                <div class="col-md-6 form-group birthdate">
                    <label for="birthdate">BIRTH DATE: <span class="required">*</span></label>
                    <input type="date" class="form-control form-control-sm" id="birthdate" name="birthdate"/>
                </div>

                <div class="col-md-6 form-group birthplace">
                    <label for="birthplace">BIRTH PLACE: <span class="required">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="birthplace" name="birthplace"/>
                </div>
                
                <div class="col-md-4 form-group gender">
                    <label for="gender">GENDER: <span class="required">*</span></label>
                    <select name="gender" id="gender" class="form-control form-control-sm">
                        <option value="male">MALE</option>
                        <option value="female">FEMALE</option>
                    </select>
                </div>

                <div class="col-md-4 form-group citizenship">
                    <label for="citizenship">CITIZENSHIP: <span class="required">*</span></label>
                    <input type="text" value="FILIPINO" class="form-control form-control-sm" name="citizenship" id="citizenship" disabled/>
                </div>

                <div class="col-md-4 form-group civil_status">
                    <label>CIVIL STATUS <span class="required">*</span></label>
                    <select name="civil_status" id="civil_status" class="form-control form-control-sm">
                        <option value="SINGLE">SINGLE</option>
                        <option value="MARRIED">MARRIED</option>
                        <option value="SOLO PARENT">SOLO PARENT</option>
                        <option value="WIDOWED">WIDOWED</option>
                        <option value="DIVORCED">DIVORCED</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <br>

    <h5>CONTACT INFORMATION</h5>
    <div class="row">
        <div class="col-4 form-group phone1">
            <label for="phone1">CONTACT NUMBER 1: <span class="required">*</span></label>
            <input type="text" class="form-control form-control-sm" id="phone1" name="phone1" value="+63" maxlength="13" autocomplete="off"/>
        </div>
        <div class="col-4 form-group phone2">
            <label for="phone2">CONTACT NUMBER 2:</label>
            <input type="text" class="form-control form-control-sm" id="phone2" name="phone2" maxlength="13" autocomplete="off"/>
        </div>
        <div class="col-4 form-group email">
            <label for="email">EMAIL ADDRESS: <span class="required">*</span></label>
            <input type="email" class="form-control form-control-sm" id="email" name="email"/>
        </div>
    </div>

    <br>

    <h5>ADDRESS:</h5>
    <div class="row">
        <div class="form-group col-4 country_1">
            <label>REGION <span class="required">*</span></label>
            <select name="country_1" id="country_1" class="form-control form-control-sm" onchange="selectRegion()">
                <option value=""></option>
                @foreach ($region as $item)
                    <option value="{{$item->region_id}}">{{$item->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-4 province_1">
            <label>PROVINCE <span class="required">*</span></label>
            <select name="province_1" id="province_1" class="form-control form-control-sm" onchange="selectProvince()">
                <option value=""></option>
            </select>
        </div>
        <div class="form-group col-4 city_1">
            <label>CITY <span class="required">*</span></label>
            <select name="city_1" id="city_1" class="form-control form-control-sm" onchange="selectCity()">
                <option value=""></option>
            </select>
        </div>
        <div class="form-group col-4 barangay_1">
            <label>BARANGAY <span class="required">*</span></label>
            <select name="barangay_1" id="barangay_1" class="form-control form-control-sm">
                <option value=""></option>
            </select>
        </div>
        <div class="col-4">
            <div class="form-group street_1">
                <label>STREET NO. <span class="required">*</span></label>
                <input type="text" class="form-control form-control-sm" name="street_1" id="street_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group zip_1">
                <label>ZIP CODE <span class="required">*</span></label>
                <input type="text" class="form-control form-control-sm" name="zip_1" id="zip_1"/>
            </div>
        </div>
    </div>

    <br>

    <h5>EMERGENCY CONTACT:</h5>
    <div class="row">
        <div class="col-4 form-group emergency_name">
            <label for="emergency_name">NAME:</label>
            <input type="text" class="form-control form-control-sm" name="emergency_name" id="emergency_name"/>
        </div>
        <div class="col-4 form-group emergency_no">
            <label for="emergency_no">CONTACT NO.:</label>
            <input type="text" class="form-control form-control-sm" name="emergency_no" id="emergency_no" maxlength="13" inputmode="numeric" autocomplete="off"/>
        </div>
        <div class="col-4 form-group emergency_relationship">
            <label for="emergency_relationship">RELATIONSHIP:</label>
            <input type="text" class="form-control form-control-sm" name="emergency_relationship" id="emergency_relationship"/>
        </div>
    </div>

    <br>

    <h5>GOVERNMENT MANDATED BENEFITS:</h5>
    <div class="row">
        <div class="col-4 form-group sss_number">
            <label for="sss_number">SSS NUMBER:</label>
            <input type="text" class="form-control form-control-sm" id="sss_number" name="sss_number" placeholder="00-0000000-0" maxlength="12" autocomplete="off"/>
        </div>
        <div class="col-4 form-group pagibig_number">
            <label for="pagibig_number">PAG-IBIG NUMBER:</label>
            <input type="text" class="form-control form-control-sm" id="pagibig_number" name="pagibig_number" placeholder="0000-0000-0000" maxlength="14" autocomplete="off"/>
        </div>
        <div class="col-4 form-group tin_number">
            <label for="tin_number">TIN NUMBER:</label>
            <input type="text" class="form-control form-control-sm" id="tin_number" name="tin_number" placeholder="000-000-000-000" maxlength="15" autocomplete="off"/>
        </div>
        <div class="col-4 form-group philhealth_number">
            <label for="philhealth_number">PHILHEALTH NUMBER:</label>
            <input type="text" class="form-control form-control-sm" id="philhealth_number" name="philhealth_number" placeholder="00-000000000-0" maxlength="14" autocomplete="off"/>
        </div>
    </div>

    <br>

    <h5>BANK INFORMATION:</h5>
    <div class="row">
        <div class="col-4 form-group bank_name">
            <label for="bank_name">BANK NAME:</label>
            <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name"/>
        </div>
        <div class="col-4 form-group bank_account">
            <label for="bank_account">BANK ACCOUNT NAME:</label>
            <input type="text" class="form-control form-control-sm" id="bank_account" name="bank_account"/>
        </div>
        <div class="col-4 form-group bank_account_no">
            <label for="bank_account_no">BANK ACCOUNT #:</label>
            <input type="text" class="form-control form-control-sm" id="bank_account_no" name="bank_account_no" inputmode="numeric" autocomplete="off"/>
        </div>
    </div>

    <br>

    <h5>EMPLOYMENT DETAILS:</h5>
    <div class="row">
        <div class="col-3 form-group employment_status">
            <label>EMPLOYMENT STATUS: <span class="required">*</span></label>
            <select name="employment_status" id="employment_status" class="form-control"
            @role('HR|SUPER ADMIN|Super Admin|ADMIN & PO MANAGER')
            @else
                disabled
            @endrole
            >
                <option value="REGULAR">REGULAR</option>
                <option value="PROJECT-BASED">PROJECT-BASED</option>
                <option value="PROBATIONARY">PROBATIONARY</option>
                <option value="TEMPORARY">TEMPORARY</option>
                <option value="TERMINATED">TERMINATED</option>
                <option value="RESIGNED">RESIGNED</option>
            </select>
        </div>

        <div class="col-3 form-group classes_id">
            <label>WORK CLASS: <span class="required">*</span></label>
            <select name="classes_id" id="classes_id" class="form-control">
                <option value="">Please select classes</option>
                @foreach ($classes as $item)
                <option value="{{$item->id}}">{{$item->description}}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-3 form-group position_id">
            <label for="position_id">POSITION: <span class="required">*</span></label>
            <select onchange="getPosition()" name="position_id" id="position_id" class="form-control">
                <option value="">Please select position</option>
                @foreach ($position as $item)
                <option value="{{$item->id}}">{{$item->description}}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-3 form-group department_id">
            <label for="department_id">DEPARTMENT: <span class="required">*</span></label>
            <select name="department_id" id="department_id" class="form-control">
                <option value="">Please select department</option>
                @foreach ($department as $item)
                <option value="{{$item->id}}">{{$item->description}}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-4 form-group employment_date">
            <label for="employment_date">HIRE DATE:<span class="required">*</span></label>
            <input type="date" class="form-control" id="employment_date" name="employment_date" max="9999-12-31">
        </div>
        
        <div class="col-4 form-group payroll_calendar_id">
            <label for="payroll_calendar_id">PAYOUT SCHEDULE:<span class="required">*</span></label>
            <select name="payroll_calendar_id" id="payroll_calendar_id" class="form-control">
                <option value="" style="display:none;">PLEASE SELECT PAYROLL GROUP</option>
                <option value="0"></option>
                @foreach ($payroll_calendar as $item)
                <option value="{{$item->id}}">{{$item->title}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 form-group employment_type">
            <label for="employment_type">PAYROLL GROUP:<span class="required">*</span></label>
            <select name="employment_type" id="employment_type" class="form-control">
                <option value="fixed_rate">FIXED RATE</option>
                <option value="daily_rate">DAILY RATE</option>
                <option value="monthly_rate">MONTHLY RATE</option>
            </select>
        </div>
    </div>
</div>
