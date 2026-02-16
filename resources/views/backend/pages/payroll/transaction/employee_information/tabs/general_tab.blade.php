
<div id="general_tab" class="form-tab">
    <h5>GENERAL TAB</h5>
    <div class="row">
        <div class="col-3">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <div class="employee-picture">
                            <label>EMPLOYEE PICTURE</label>
                            <div id="" onclick="$('#profile_img').click()">
                                <img src="/images/payroll/employee-information/default.png" alt="" width="200px" id="viewer" class="image-previewer" data-cropzee="profile_img">
                            </div>
                            <input id="profile_img" type="file" name="profile_img" class="form-control" onchange="scion.fileView(event)" style="display:none;">
                            <button class="btn btn-primary" type="button" onclick="$('#profile_img').click()" style="width:100%;">Select Photo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-9">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        @include('backend.partial.component.lookup', [
                            'label' => "EMPLOYEE NUMBER",
                            'placeholder' => '<NEW>',
                            'id' => "employee_no",
                            'title' => "EMPLOYEE NUMBER",
                            'url' => "/payroll/employee-information/get",
                            'data' => array(
                                array('data' => "DT_RowIndex", 'title' => "#"),
                                array('data' => "employee_no", 'title' => "Employee Number"),
                                array('data' => "full_name", 'title' => "Name"),
                                array('data' => "email", 'title' => "Email"),
                            ),
                            'disable' => true,
                            'lookup_module' => 'employee-information',
                            'modal_type'=> 'all',
                            'lookup_type' => 'main'
                        ])
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <div class="employee-rfid">
                            <label>RFID CODE:</label>
                            <input type="text" id="rfid" class="form-control" disabled>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group status">
                        <label>STATUS <span class="required">*</span></label>
                        <select name="status" id="status" class="form-control" onchange="lookupReturn()">
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
                        <select id="suffix"  class="form-control" name="suffix">
                            <option value="">Select</option>
                            <option value="JR">JR</option>
                            <option value="SR">SR</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                            <option value="V">V</option>
                            <option value="VI">VI</option>
                            <option value="VII">VII</option>
                            <option value="VIII">VIII</option>
                            <option value="IX">IX</option>
                            <option value="X">X</option>
                        </select>
                    </div>
                </div>
                <div class="col-xl-2 col-md-12">
                    <div class="form-group employment_status">
                        <label>EMPLOYMENT STATUS:<span class="required">*</span></label>
                        <select name="employment_status" id="employment_status" class="form-control">
                            <option value="REGULAR">REGULAR</option>
                            <option value="PROJECT-BASED">PROJECT-BASED</option>
                            <option value="PROBATIONARY">PROBATIONARY</option>
                            <option value="TEMPORARY">TEMPORARY</option>
                        </select>
                    </div>
                </div>
                <div class="col-xl-2 col-md-12">
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
                        <select onchange="getPosition()" name="position_id" id="position_id" class="form-control">
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
                <div class="col-12 regular status-cont">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group hire_date">
                                <label for="hire_date">HIRE DATE:</label>
                                <input type="date" name="hire_date" id="hire_date" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group site_location">
                                <label for="site_location">SITE LOCATION:</label>
                                <select name="site_location" id="site_location" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group project_name">
                                <label for="project_name">PROJECT NAME:</label>
                                <select name="project_name" id="project_name" class="form-control"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 project status-cont hide">
                    <div class="row">
                        <div class="col-3">
                            <div class="form-group start_date">
                                <label for="start_date">START DATE:</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group end_date">
                                <label for="end_date">END DATE:</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group site_location">
                                <label for="site_location">SITE LOCATION:</label>
                                <select name="site_location" id="site_location" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group project_name">
                                <label for="project_name">PROJECT NAME:</label>
                                <select name="project_name" id="project_name" class="form-control"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 probationary status-cont hide">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group hire_date">
                                <label for="hire_date">HIRE DATE:</label>
                                <input type="date" name="hire_date" id="hire_date" class="form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <!-- <div class="form-group">
                <label>EARNINGS</label>
                <div class="earning">
                    @foreach ($earning as $item)
                        <span class="earning-item" id="earning_{{$item->id}}" onclick="earningSetup({{$item->id}})">{{$item->name}}</span>
                    @endforeach
                </div>
            </div> -->
            {{-- <div class="form-group">
                <label>ALLOWANCE</label>
                <div class="allowance">
                    @foreach ($allowance as $item)
                        <span class="allowance-item" id="allowance_{{$item->id}}" onclick="allowanceSetup({{$item->id}})">{{$item->name}}</span>
                    @endforeach
                </div>
            </div> --}}
        </div>  
        <div class="col-xl-3 col-md-12">
            <div class="form-group employment_date">
                <label>DATE OF EMPLOYMENT:<span class="required">*</span></label>
                <input type="date" class="form-control" id="employment_date" name="employment_date" max="9999-12-31">
            </div>
        </div>
        <div class="col-xl-3 col-md-12">
            <div class="form-group tax_rate">
                <label>TAX RATE:</label>
                <input type="number" class="form-control" id="tax_rate" name="tax_rate">
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
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
        <div class="col-xl-3 col-md-6">
            <div class="form-group employment_type">
                <label>PAYOUT SCHEDULE:<span class="required">*</span></label>
                <select name="employment_type" id="employment_type" class="form-control">
                    <option value="fixed_rate">FIXED RATE</option>
                    <option value="daily_rate">DAILY RATE</option>
                    <option value="monthly_rate">MONTHLY RATE</option>
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group birthdate">
                <label>BIRTHDATE <span class="required">*</span></label>
                <input type="date" class="form-control" name="birthdate" id="birthdate" max="9999-12-31"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group birthplace">
                <label>BIRTH PLACE <span class="required">*</span></label>
                <input type="text" class="form-control" name="birthplace" id="birthplace"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group gender">
                <label>GENDER <span class="required">*</span></label>
                <select name="gender" id="gender" class="form-control">
                    <option value="male">MALE</option>
                    <option value="female">FEMALE</option>
                </select>
            </div>
        </div>
        <div class="col-3">
        <div class="form-group citizenship">
            <label>CITIZENSHIP <span class="required">*</span></label>
            {{-- <select class="form-control" name="citizenship" id="citizenship">
            </select> --}}
            <input type="text" value="FILIPINO" class="form-control" name="citizenship" id="citizenship" disabled/>
        </div>
        </div>
        <div class="col-6">
            <div class="form-group phone1">
                <label>CONTACT NUMBER 1 <span class="required">*</span></label>
                <div class="input-group mb-3">
                    <span class="input-group-text">(63)</span>
                    <input type="text" class="form-control" name="phone1" id="phone1" data-mask="000 0000000" autocomplete="off" maxlength="11"/>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group phone2">
                <label>CONTACT NUMBER 2</label>
                <div class="input-group mb-3">
                    <span class="input-group-text">(63)</span>
                    <input type="text" class="form-control" name="phone2" id="phone2" data-mask="000 0000000" autocomplete="off" maxlength="11"/>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="form-group email">
                <label>EMAIL ADDRESS <span class="required">*</span></label>
                <input type="email" class="form-control" name="email" id="email"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group civil_status">
                <label>CIVIL STATUS <span class="required">*</span></label>
                <select name="civil_status" id="civil_status" class="form-control">
                    <option value="SINGLE">SINGLE</option>
                    <option value="MARRIED">MARRIED</option>
                    <option value="SOLO PARENT">SOLO PARENT</option>
                    <option value="WIDOWED">WIDOWED</option>
                    <option value="DIVORCED">DIVORCED</option>
                </select>
            </div>
        </div>

        <h3 class="col-12 form-title">ADDRESS 1</h3>
        <div class="form-group col-md-4 country_1">
            <label>REGION </label>
            <select name="country_1" id="country_1" class="form-control" onchange="selectRegion()">
                <option value=""></option>
                @foreach ($region as $item)
                    <option value="{{$item->region_id}}">{{$item->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-4 province_1">
            <label>PROVINCE </label>
            <select name="province_1" id="province_1" class="form-control" onchange="selectProvince()">
                <option value=""></option>
            </select>
        </div>
        <div class="form-group col-md-4 city_1">
            <label>CITY </label>
            <select name="city_1" id="city_1" class="form-control" onchange="selectCity()">
                <option value=""></option>
            </select>
        </div>
        <div class="form-group col-md-4 barangay_1">
            <label>BARANGAY </label>
            <select name="barangay_1" id="barangay_1" class="form-control">
                <option value=""></option>
            </select>
        </div>
        <div class="col-4">
            <div class="form-group street_1">
                <label>STREET NO. </label>
                <input type="text" class="form-control" name="street_1" id="street_1"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group zip_1">
                <label>ZIP CODE </label>
                <input type="text" class="form-control" name="zip_1" id="zip_1"/>
            </div>
        </div>

        <h3 class="col-12 form-title">EMERGENCY CONTACT</h3>
        <div class="col-4">
            <div class="form-group emergency_name">
                <label>NAME</label>
                <input type="text" class="form-control" name="emergency_name" id="emergency_name"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group emergency_no">
                <label>CONTACT NUMBER</label>
                <input type="number" class="form-control" name="emergency_no" id="emergency_no"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group emergency_relationship">
                <label>CONTACT NUMBER</label>
                <input type="text" class="form-control" name="emergency_relationship" id="emergency_relationship"/>
            </div>
        </div>

        <h3 class="col-12 form-title">BENEFITS AND OTHERS</h3>
        <div class="col-4">
            <div class="form-group bank_name">
                <label>BANK NAME</label>
                <input type="text" class="form-control" name="bank_name" id="bank_name"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group bank_account">
                <label>BANK ACCOUNT NAME</label>
                <input type="text" class="form-control" name="bank_account" id="bank_account"/>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group bank_account_no">
                <label>BANK ACCOUNT #</label>
                <input type="text" class="form-control" name="bank_account_no" id="bank_account_no"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group tin_number">
                <label>TIN NUMBER</label>
                <input type="number" class="form-control" name="tin_number" id="tin_number"/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group sss_number">
                <label>SSS NUMBER</label>
                <input type="text" class="form-control" name="sss_number" id="sss_number" placeholder="##-#######-#"/>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            var sssInput = document.getElementById('sss_number');

            sssInput.addEventListener('input', function (e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,7})(\d{0,1})/);
                    e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
                });

            });
        </script>
        <div class="col-6">
            <div class="form-group pagibig_number">
                <label>PAGIBIG NUMBER</label>
                <input type="text" class="form-control" name="pagibig_number" id="pagibig_number" placeholder="####-####-####" />
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var sssInput = document.getElementById('pagibig_number');

                sssInput.addEventListener('input', function (e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})/);
                    e.target.value = (x[1] ? x[1] : '') + (x[2] ? '-' + x[2] : '') + (x[3] ? '-' + x[3] : '');
                });
            });
        </script>
   
        <div class="col-6">
            <div class="form-group philhealth">
                <label>PHILHEALTH</label>
                <input type="text" class="form-control" name="philhealth" id="philhealth" placeholder="##-#########-#"/>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var sssInput = document.getElementById('philhealth');

                sssInput.addEventListener('input', function (e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,9})(\d{0,1})/);
                    e.target.value = (x[1] ? x[1] : '') + (x[2] ? '-' + x[2] : '') + (x[3] ? '-' + x[3] : '');
                });
            });
        </script>
    </div>
</div>
