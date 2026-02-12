@extends('backend.master.index')

@section('title', 'PAYSLIP')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span>  /  <span class="highlight">PAYSLIP</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="col-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="title">UNITED LABORATORIES, Inc</h5>
                            <h6 class="sub-text">66 United St., Mandaluyong City, Philippines 1550</h6>
                            <h6 class="sub-text">0912 456 7891</h6>
                        </div>
                    </div>
                    <div style="padding: 1em;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="title">Employee Details</h5>
                            <h6 class="sub-text">Arvin Olivas</h6>
                            <h6 class="sub-text">107 J.P Rizal St. Sta Lucia Novaliches Quezon City Philippines</h6>
                            <h6 class="sub-text">Registered Nurse</h6>

                        </div>
                        <div class="col-md-6">
                            <h5 class="title">Direct Credit Details</h5>
                            <h6 class="sub-text">Primary Bank Account:</h6>
                            <h6 class="sub-text">01-0123-0123456-012</h6>
                        </div>
                    </div>
                    <div style="padding: 1em;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="wks"  style="border-top: 1px solid #cbcbcb; border-bottom: 1px solid #cbcbcb;">
                                <tr>
                                    <th>PAYROLL PERIOD</th>
                                    <th>TO: March 01, 2023</th>
                                    <th>FROM: March 15, 2023</th>
                                    <th>TAX CODE: XX</th>
                                    
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div style="padding: 1em;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="wks">
                                <tr>
                                    <th>EARNINGS</th>
                                    <th></th>
                                    <th>ROLE</th>
                                    <th>HOURS</th>
                                    <th></th>
                                    <th>TOTAL</th>
                                </tr>
                                <tr>
                                    <td>REGULAR EARNINGS</td>
                                    <td>-</td>
                                    <td>Registered Nurse</td>
                                    <td>80</td>
                                    <td>-</td>
                                    <td>20,000.00</td>
                                </tr>
                                <tr>
                                    <td>ALLOWANCE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>5,000.00</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">TOTAL GROSS EARNINGS</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td style="font-weight: bold;">25,000.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div style="padding: 1em;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="wks">
                                <tr>
                                    <th>DEDUCTIONS</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>TOTAL</th>
                                </tr>
                                <tr>
                                    <td>MANDATED BENEFITS</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>SSS</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>600.00</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>PhilHealth</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>200.00</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>Pag-ibig</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>300.00</td>
                                </tr>
                                <tr>
                                    <td>Other Deductions</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>Cash Advance</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>1,000.00</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">TOTAL DEDUCTIONS</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td style="font-weight: bold;">2,300.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div style="padding: 1em;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="wks">
                                <tr>
                                    <td>Taxable Amount</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>18,700.00</td>
                                </tr>
                                <tr>
                                    <td>Withholding Tax</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>957.50</td>
                                </tr>
                                <tr style="background: #6eafdb; color: white;">
                                    <td style="font-weight: bold; font-size: 20px;">Net Pay</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td style="font-weight: bold; font-size: 20px;">21,762.50</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/payroll/transaction/payrun.js"></script>
@endsection


@section('styles')
<style>
    h6.sub-text {
        font-size: 12px;
        margin-bottom: 0px;
    }
    h5.title {
        background: #6eafdb;
        padding: 5px;
        color: white;
    }
    .main {
        overflow-x: hidden;
    }
    .card-header {
        background: #e9e9e9;
    }
    h5.card-title {
        color: #3282b8;
    }
    .text-percentage {
        font-size: 9px;
        color: gray;
    }
    .text-primary {
        color: #3282b8;
        font-size: 20px;
        font-weight: bold;
    }
    p.section-title {
        font-size: 12px;
        color: #b6b6b6;
        margin-bottom: 0px;
    }
    p.notif-title {
        font-weight: bold;
        color: #e02828;
        font-size: 13px;
        margin-bottom: 0px;
    }
    p.notif-title-green {
        font-weight: bold;
        color: #28e04a;
        font-size: 13px;
        margin-bottom: 0px;
    }
    p.notif-description {
        font-size: 10px;
        color: #b6b6b6;
    }
    p.section-desc {
        font-weight: bold;
        color: #6eafdb;
    }
    label.section-label {
        font-weight: bold;
    }
    .payroll-title {
        color: #b6b6b6;
        margin-bottom: 0px !important;
    }
    .payroll-date {
        color: #6eafdb;
        font-weight: bold;
    }
    .employment-status {
        color: #b6b6b6;
        font-size: 12px;
    }
    .job-title {
        font-weight: bold;
        color: #6eafdb;
        margin-bottom: 0px !important;
    }
    .account-balance {
        font-weight: bold;
        font-size: 20px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 10px;
    }
    
    td, th {
        padding: 5px;
    }
    th {
        color: #6eafdb;
        font-weight: bold;
    }
    table.dtl tr:nth-child(even) {
        background: #d3d3d3;
    }
    table.dtl td, th {
        /* border: 1px solid #dddddd; */
        text-align: left;
        padding: 6px;
    }
    table.wks tr:nth-child(even) {
        background: #e7f5ff;
    }
    table.wks td, th {
        /* border: 1px solid #dddddd; */
        text-align: left;
        padding: 6px;
    }
    label.section-label {
        color: #3282b8;
    }
</style>
@endsection