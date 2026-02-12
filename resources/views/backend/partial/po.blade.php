<div class="po-form">
    <div id="printPO" class="form-layout" style="width: 11.5in; margin: auto; background: white; padding: 20px;">
        <div class="row" style="display:flex;">
            <div class="col-8" style="display: flex;" style="width:70%;">
                <div class="logo-container">
                    <img src="/images/logo-dark.png" class="logo-po" alt="company-logo" width="100%"/>
                </div>
                <div class="company-details">
                    <p class="c-name">SP CONSTRUCTION CORPORATION</p>
                    <p class="c-address">Lot 14 Blk 2 Yakal St. Agapito Subd. Brgy Santalon <br>MN 1610</p>
                </div>
            </div>
            <div class="col-4" style="width:30%;">
                <p class="po-title">Purchase Order</p>
                <table class="head-table">
                    <tr>
                        <th>
                            Date
                        </th>
                        <th>
                            P.O No.
                        </th>
                    </tr>
                    <tr>
                        <td><span id="po_date1"></span></td>
                        <td><span id="po_no"></span></td>
                    </tr>
                </table>
                <table class="head-table">
                    <tr>
                        <th>
                            Old P.O No.
                        </th>
                    </tr>
                    <tr>
                        <td><span id="old_po"></span></td>
                    </tr>
                </table>
                <br>
                <table class="head-table">
                    <tr>
                        <th>
                            Project Code/s
                        </th>
                    </tr>
                    <tr>
                        <td><span id="project_list"></span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="spacer"></div>

        <div class="row" style="display:flex;">
            <div class="col-6" style="width:100%;">
                <table class="head-table">
                    <tr>
                        <th>
                            Vendor
                        </th>
                        
                    </tr>
                    <tr>
                        <td>
                            <span id="po_vendor1"></span> <br>
                            <span id="po_vendor_address1"></span>
                        </td>
                    </tr>
                </table>
                <table class="head-table">
                    <tr>
                        <td>Terms</td>
                        <td><span id="po_terms1"></span></td>
                        <td>Due Date</td>
                        <td><span id="po_due_date1"></span></td>
                    </tr>
                </table>
            </div>
            <div class="col-6" style="width:100%;">
                <table class="head-table">
                    <tr>
                        <th>
                            Ship To
                        </th>
                        
                    </tr>
                    <tr>
                        <td>
                            {{-- <span id="po_ship_to1"></span> <br> --}}
                            <span id="po_ship_to_address1"></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="spacer"></div>

        <div class="row row-details">
            <div class="col-12">
                <table id="details-table" class="desc-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>U/M</th>
                            @if(!$user->hasRole('STOCK CLERK'))
                            <th>Unit Price</th>
                            <th>Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <!-- <tfoot>
                        <tr class="discount-container"></tr>
                        <tr>
                            @if($user->hasRole('STOCK CLERK'))
                            <td class="total text-right" colspan="3">Total</td>
                            @else
                            <td class="total text-right" colspan="4">Total</td>
                            <td class="total-amount text-center"><span id="po_total"></span></td>
                            @endif
                        </tr>
                    </tfoot> -->
                </table>
            </div>
        </div>
        <div class="spacer"></div>

        <div class="row">
            <div class="col-12 po-footer">
                <div class="po-total mb-5">
                        <table class="desc-table">
                            <thead>
                            <tr>
                              
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="discount-container"></tr>
                            <tr>
                                @if($user->hasRole('STOCK CLERK'))
                                <td class="total text-right" colspan="3">Total</td>
                                @else
                                <td class="total text-right" width="75%">Total</td>
                                <td class="total-amount text-center" width="25%;"><span id="po_total"></span></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="print-footer">
                    <table class="footer-table">
                    <tr>
                        <th>Prepared by:</th>
                        <th>Checked by:</th>
                        <th>Approved by:</th>
                    </tr>
                    <tr>
                        <td>
                            <p class="data" id="po_prepared_by"></p>
                            <p class="data" id="po_prepared_by_date"></p>
                            <p class="data">Name & Signature w/ Date</p>
                        </td>
                        <td>
                            <p class="data" id="po_checked_by"></p>
                            <p class="data" id="po_checked_by_date"></p>
                            <p class="data">Name & Signature w/ Date</p>
                        </td>
                        <td>
                            <p class="data" id="po_approved_by"></p>
                            <p class="data" id="po_approved_by_date"></p>
                            <p class="data">Name & Signature w/ Date</p>
                        </td>
                    </tr>
                </table>
                <table class="footer-table dr-footer" style="display:none;">
                    <tr>
                        <th>Checked by:</th>
                        <th>Received by:</th>
                    </tr>
                    <tr>
                        <td>
                            <p class="data" id="po_checked_by_2"></p>
                            <p class="data" id="po_checked_by_date_2"></p>
                            <p class="data">Name & Signature w/ Date</p>
                        </td>
                        <td>
                            <p class="data" id="po_received_by"></p>
                            <p class="data" id="po_received_by_date"></p>
                            <p class="data">Name & Signature w/ Date</p>
                        </td>
                    </tr>
                </table>
                </div>
                
            </div>
        </div>
        
    <style>
        

        table.head-table, table.desc-table, table.footer-table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        table.head-table td, table.desc-table td, table.footer-table td, table.head-table th, table.desc-table th, table.footer-table th {
            border: 1px solid black;
            text-align: left;
            padding: 8px;
        }
        table.head-table tr:nth-child(odd), table.desc-table tr:nth-child(odd), table.footer-table tr:nth-child(odd) {
            background-color: #dddddd;
        }

        table.head-table td, table.desc-table td, table.footer-table td {
            background-color: white;
        }

        p.c-name {
            margin-bottom: 0px !important;
            font-size: 34px;
            font-weight: bold;
        }
        p.c-address {
            font-size: 20px;
            line-height: 25px;
        }
        img.logo-po {
            width: 100px;
            float: right;
            margin-right: 10px;
        }
        p.po-title {
            font-size: 28px;
            font-weight: bold;
        }
        .spacer {
            padding: 1em;
        }
        table.desc-table>tbody>tr>th {
            text-align: center;
        }
        table.footer-table>tbody>tr>th {
            text-align: center;
        }
        .po-footer{
            margin-top: 0;
        }

        
        table.desc-table>tbody>tr>td {
            /* height: 650px; */
        }
        table.footer-table>tbody>tr>td {
            height: 75px;
            text-align: center;
            vertical-align: bottom;
        }
        td.total {
            font-weight: bold;
            font-size: 22px;
        }
        td.total-amount {
            text-align: right;
            background: #dddddd;
            font-weight: bold;
        }
        .sc-xl {
            max-width: 1340px;
            width: 100%;
        }
        p.data {
            margin-bottom: 0px;
        }
        p#po_prepared_by,
        #po_checked_by,
        #po_approved_by,
        #po_received_by,
        #po_checked_by_2 {
            font-weight: bold;
        }
        table.desc-table>tbody>tr>td {
            vertical-align: baseline;
            text-align: center;
        }

        @media print {

    /* Hide side panels / preview panes */
    .side-panel,
    .sidebar,
    .preview-panel {
        display: none !important;
    }

    body{
        margin: 10mm;
        background: white;
    }

    /* Push content up so footer has space */
    #printPO{
        padding-bottom: 180px; /* adjust based on footer height */
    }

    /* Stick footer to bottom */
    .print-footer{
        position: fixed;
        bottom: 10mm;
        left: 0;
        width: 100%;
        background: white;
        padding: 0 20px;
    }

    /* Prevent tables breaking */
    table{
        page-break-inside: avoid;
    }

    .po-footer{
        margin-top: 0;
    }
}
    </style>
    </div>
</div>