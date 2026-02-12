<div id="taxBenefitsScreen" class="content-employee">
    <h5>TAX AND BENEFITS</h5>
    <ul class="second-tab">
        <li><a href="#" id="sss" class="active">SSS</a></li>
        <li><a href="#" id="pagibig">PAG-IBIG</a></li>
        <li><a href="#" id="philhealth">PHILHEALTH</a></li>
        <li><a href="#" id="withholdingtax">WITHHOLDING TAX</a></li>
        <li><a href="#" id="allowance">ALLOWANCE</a></li>
    </ul>
    <div class="sub-content">
        @include('backend.pages.employee.content.tax_and_benefits.sss')
        @include('backend.pages.employee.content.tax_and_benefits.pagibig')
        @include('backend.pages.employee.content.tax_and_benefits.philhealth')
        @include('backend.pages.employee.content.tax_and_benefits.withholdingtax')
        @include('backend.pages.employee.content.tax_and_benefits.allowance')
    </div>
</div>