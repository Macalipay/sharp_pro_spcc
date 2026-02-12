@extends('backend.master.index')

@section('title', 'EMPLOYEE 201 FILE')

@section('breadcrumbs')
    <span>TRANSACTION</span>  /  <span class="highlight">EMPLOYEE 201 FILE</span>
@endsection

@section('left-content')
    @include('backend.partial.component.tab_list', [
        'id'=>'personnelfile_menu', 
        'data'=>array(
            array('id'=>'general', 'title'=>'GENERAL', 'icon'=>' fas fa-file-alt', 'active'=>true, 'disabled'=>false, 'function'=>true),
            array('id'=>'attachment', 'title'=>'ATTACHMENT', 'icon'=>' fas fa-portrait', 'active'=>false, 'disabled'=>false, 'function'=>true),
            array('id'=>'history', 'title'=>'HISTORY', 'icon'=>' fas fa-sign-out-alt', 'active'=>false, 'disabled'=>false, 'function'=>true),
            array('id'=>'contracts', 'title'=>'CONTRACTS', 'icon'=>' fas fa-sign-out-alt', 'active'=>false, 'disabled'=>false, 'function'=>true),
            array('id'=>'trainings', 'title'=>'TRAININGS', 'icon'=>' fas fa-sign-out-alt', 'active'=>false, 'disabled'=>true, 'function'=>true),
        )
    ])
@endsection


@section('content')
<div class="row" style="height:100%;">
    <div class="col-12" style="height:100%;">
        <div class="tab" style="height:100%;">
            <div class="tab-content">
                <form class="form-record" method="post" id="employeeInformation">
                    @include('backend.pages.payroll.transaction.201_file.tabs.general_tab')
                    @include('backend.pages.payroll.transaction.201_file.tabs.attachments_tab')
                    @include('backend.pages.payroll.transaction.201_file.tabs.history_tab')
                    @include('backend.pages.payroll.transaction.201_file.tabs.contracts_tab')
                    @include('backend.pages.payroll.transaction.201_file.tabs.trainings_tab')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/payroll/transaction/201_file.js"></script>
@endsection
