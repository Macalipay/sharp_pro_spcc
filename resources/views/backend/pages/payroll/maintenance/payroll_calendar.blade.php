@extends('backend.master.index')

@section('title', 'PAYROLL CALENDAR')

@section('breadcrumbs')
    <span>SETUP / PAYROLL</span> / <span class="highlight">PAYROLL CALENDAR</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="payroll_calendar_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="payroll_calendar_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('payroll_calendar_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payrollCalendarForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 title">
                        <label>TITLE:</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="TITLE"/>
                    </div>
                    <div class="form-group col-md-12 type">
                        <label>CALENDAR TYPE:</label>
                        <select name="type" id="type" class="form-control">
                            <option value="1">MONTHLY</option>
                            <option value="2">BI-MONTHLY</option>
                            <option value="3">BI-WEEKLY</option>
                            <option value="4">WEEKLY</option>
                        </select>
                    </div>
                    <div class="col-12" id="date_field">
                        <div class="row">
                            <div class="form-group col-md-6 start_date">
                                <label>START DATE:</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"/>
                            </div>
                            <div class="form-group col-md-6 end_date">
                                <label>END DATE:</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"/>
                            </div>
                            <div class="form-group col-md-12 payment_date">
                                <label>PAYMENT DATE:</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 hide" id="day_field">
                        <div class="row">
                            <div class="form-group col-md-6 start_day">
                                <label>PAY START(DAY):</label>
                                <select name="start_day" id="start_day" class="form-control">
                                    <option value="sunday">SUNDAY</option>
                                    <option value="monday">MONDAY</option>
                                    <option value="tuesday">TUESDAY</option>
                                    <option value="wednesday">WEDNESDAY</option>
                                    <option value="thursday">THURSDAY</option>
                                    <option value="friday">FRIDAY</option>
                                    <option value="saturday">SATURDAY</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 end_day">
                                <label>PAY END(DAY):</label>
                                <select name="end_day" id="end_day" class="form-control">
                                    <option value="sunday">SUNDAY</option>
                                    <option value="monday">MONDAY</option>
                                    <option value="tuesday">TUESDAY</option>
                                    <option value="wednesday">WEDNESDAY</option>
                                    <option value="thursday">THURSDAY</option>
                                    <option value="friday">FRIDAY</option>
                                    <option value="saturday">SATURDAY</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 payment_day">
                                <label>PAYMENT DAY:</label>
                                <select name="payment_day" id="payment_day" class="form-control">
                                    <option value="sunday">SUNDAY</option>
                                    <option value="monday">MONDAY</option>
                                    <option value="tuesday">TUESDAY</option>
                                    <option value="wednesday">WEDNESDAY</option>
                                    <option value="thursday">THURSDAY</option>
                                    <option value="friday">FRIDAY</option>
                                    <option value="saturday">SATURDAY</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-12 set_as_default">
                        <label class="form-check">
                            <input class="form-check-input" id="set_as_default" name="set_as_default" value="0" type="checkbox" onchange="$('#'+this.id).val($('#'+this.id).prop('checked') === true?'1':'0');">
                            <span class="form-check-label">
                            SET AS DEFAULT
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>
@endsection

@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/maintenance/payroll_calendar.js"></script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/payroll_calendar.css')}}" rel="stylesheet">
@endsection