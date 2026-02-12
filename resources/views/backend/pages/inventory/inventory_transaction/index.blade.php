@extends('backend.master.index')

@section('title', 'EMPLOYEE INFORMATION')

@section('breadcrumbs')
    <span>TRANSACTION</span>  /  <span class="highlight">EMPLOYEE INFORMATION</span>
@endsection

@section('left-content')
    @include('backend.partial.component.tab_list', [
        'id'=>'employee_menu',
        'data'=>array(
            array('id'=>'transaction', 'title'=>'INVENTORY TRANSCTION', 'icon'=>' fas fa-file-alt', 'active'=>true, 'disabled'=>false, 'function'=>true),
            array('id'=>'general', 'title'=>'GENERAL', 'icon'=>' fas fa-file-alt', 'active'=>false, 'disabled'=>false, 'function'=>true),
        )
    ])
@endsection

@section('content')
<div class="row" style="height:100%;">
@include('backend.partial.flash-message')
    <div class="col-12" style="height:100%;">
        <div class="tab" style="height:100%;">
            <div class="tab-content">
                <form class="form-record" method="post" id="employeeInformation">
                    @include('backend.pages.payroll.transaction.employee_information.tabs.transaction')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.leaves_tab')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('sc-modal')
@parent
<div class="sc-modal-content" id="rfid_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">RFID CODE DETECTED</span>
            <span class="sc-close" onclick="scion.create.sc_modal('rfid_modal').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <label>RFID CODE:</label>
                    <div class="rfid-code">-</div>
                </div>
                <div class="col-12 text-right">
                    <button class="btn btn-sm new-rfid btn-success">REGISTER</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="{{asset('/css/custom/cv.css')}}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">
@endsection('')

@section('scripts')
<script src="{{asset('/plugins/onscan.js')}}" ></script>
<script src="{{asset('/plugins/onscan.min.js')}}" ></script>
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/inventory/inventory_transaction/inventory_transaction.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const citizenshipSelect = document.getElementById('citizenship');

        fetch('https://restcountries.com/v3.1/all')
        .then(response => response.json())
        .then(data => {
            const citizenshipOptions = data.map(country => {
                // Check if 'demonyms' is available and extract the nationality name
                const demonym = country.demonyms && country.demonyms.eng ? country.demonyms.eng.m : country.name.common;
                return demonym.toUpperCase();
            });

            citizenshipOptions.sort();

            citizenshipOptions.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.textContent = option;
                optionElement.value = option;
                citizenshipSelect.appendChild(optionElement);
            });

        })
        .catch(error => {
            console.error('Error fetching citizenship options:', error);
        });

        setTimeout(() => {
                $('#citizenship').val('FILIPINO');
        }, 1000);
    });
</script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/employee.css')}}" rel="stylesheet">
@endsection
