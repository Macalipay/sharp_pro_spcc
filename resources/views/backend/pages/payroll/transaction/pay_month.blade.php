@extends('backend.master.index')

@section('title', '13th MONTH PAY')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">13 MONTH PAY</span>
@endsection

@section('content')
<div class="row" style="overflow-x:hidden;">
    <div class="col-md-12">
        <div class="card">
            @include('backend.partial.flash-message')
            <div class="row">
                <div class="col-8 mb-2">
                    <div class="year-selected">
                        Year Selected 
                        <select name="year" id="year">
                            <option value="2024">2024</option>
                        </select>
                    </div>
                    <div class="year-total-pay">
                        Total 13th month pay 
                        <span id="total_pay">0</span>
                    </div>
                </div>
                <div class="col-4 text-right mb-2">
                    <button class="btn btn-success btn-sm" onclick="releasePay()">RELEASE 13th MONTH</button>
                </div>
                <div class="col-12 filter-action">
                    <button class="btn btn-sm btn-light selected" id="all_btn" onclick="allView()">ALL</button>
                    <button class="btn btn-sm btn-light" id="fixed_btn" onclick="filterView('fixed')">FIXED RATE</button>
                    <button class="btn btn-sm btn-light" id="daily_btn" onclick="filterView('daily')">DAILY RATE</button>
                    <button class="btn btn-sm btn-light" id="monthly_btn" onclick="filterView('monthly')">MONTHLY RATE</button>
                </div>
                <div class="col-12 filter-view" id="fixed_container">
                    <hr>
                    <h5>FIXED RATE</h5>
                    <table id="fixed_rate_table" class="table table-striped" style="width:100%">
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right">Total Amount</td>
                                <td class="data-total">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-12 filter-view" id="daily_container">
                    <hr>
                    <h5>DAILY RATE</h5>
                    <table id="daily_rate_table" class="table table-striped" style="width:100%">
                        <tfoot>
                            <tr>
                                <td colspan="10" class="text-right">Total Amount</td>
                                <td class="data-total">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-12 filter-view" id="monthly_container">
                    <hr>
                    <h5>MONTHLY RATE</h5>
                    <table id="pay_table" class="table table-striped" style="width:100%">
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right">Total Amount</td>
                                <td class="data-total">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="13th_month_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">13th Month Pay</span>
            <span class="sc-close" onclick="scion.create.sc_modal('13th_month_modal').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <table id="summary_pay_table">
                <thead>
                    <th>Month</th>
                    <th>Absence</th>
                    <th>Salary</th>
                </thead>
                <tbody>
                    <tr class="month-1">
                        <td>January</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-2">
                        <td>February</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-3">
                        <td>March</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-4">
                        <td>April</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-5">
                        <td>May</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-6">
                        <td>June</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-7">
                        <td>July</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-8">
                        <td>August</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-9">
                        <td>September</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-10">
                        <td>October</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-11">
                        <td>November</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                    <tr class="month-12">
                        <td>December</td>
                        <td class="abs">No Absences</td>
                        <td class="val">0</td>
                    </tr>
                </tbody>
                <tfoot style="background:#eee;">
                    <tr>
                        <td class="text-right" colspan="2" style="font-weight:bold;">Total Annual Salary:</td>
                        <td class="annual-total">0</td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan="2" style="font-weight:bold;">13th Month Pay</td>
                        <td class="pay-total-tbl" style="font-weight:bold;">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="absent_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Absents Adjustment</span>
            <span class="sc-close" onclick="scion.create.sc_modal('absent_modal').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 text-right">
                    <button class="btn btn-success btn-sm" onclick="absentAdjustment()">ABSENT ADJUSTMENTS</button>
                </div>
            </div>
            <table id="absent_table" class="table table-striped" style="width:100%"></table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="absent_adjustments">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Absents Adjustment</span>
            <span class="sc-close" onclick="scion.create.sc_modal('absent_adjustments').hide(modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    <label>Date:</label>
                    <input type="date" class="form-control form-control-sm" name="date" id="date"/>
                </div>
                <div class="col-12 form-group">
                    <label>Remarks:</label>
                    <textarea class="form-control form-control-sm" name="remarks" id="remarks"></textarea>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="late_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Late Adjustment</span>
            <span class="sc-close" onclick="scion.create.sc_modal('late_modal').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 text-right">
                    <button class="btn btn-success btn-sm" onclick="lateAdjustment()">LATE ADJUSTMENTS</button>
                </div>
            </div>
            <table id="late_table" class="table table-striped" style="width:100%"></table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="late_adjustments">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Late Adjustment</span>
            <span class="sc-close" onclick="scion.create.sc_modal('late_adjustments').hide(modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    <label>Date:</label>
                    <input type="date" class="form-control form-control-sm" name="late_date" id="late_date"/>
                </div>
                <div class="col-12 form-group">
                    <label>Late (minutes):</label>
                    <input type="number" class="form-control form-control-sm" name="late" id="late"/>
                </div>
                <div class="col-12 form-group">
                    <label>Remarks:</label>
                    <textarea class="form-control form-control-sm" name="late_remarks" id="late_remarks"></textarea>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="release_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Release 13th Month</span>
            <span class="sc-close" onclick="scion.create.sc_modal('release_modal').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <ul id="id_rate_list">
                        <li id="fixed_rate_list" onclick="selectRate(this)">FIXED RATE <input type="checkbox"/></li>
                        <li id="daily_rate_list" onclick="selectRate(this)">DAILY RATE <input type="checkbox"/></li>
                        <li id="monthly_rate_list" onclick="selectRate(this)">MONTHLY RATE <input type="checkbox"/></li>
                    </ul>
                </div>
                <div class="col-12 text-right">
                    <button class="btn btn-primary" onclick="submitPay()">SUBMIT</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/transaction/pay_month.js"></script>
@endsection

@section('styles')
    <link rel="stylesheet" href="/css/custom/paymonth.css">
@endsection