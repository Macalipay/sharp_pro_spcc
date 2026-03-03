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
                <table class="po-meta-table">
                    <tr>
                        <td class="po-meta-key">Date Issued</td>
                        <td class="po-meta-data"><span id="po_date1"></span></td>
                    </tr>
                    <tr>
                        <td class="po-meta-key">PO NO</td>
                        <td class="po-meta-data"><span id="po_no"></span></td>
                    </tr>
                    <tr>
                        <td class="po-meta-key">PROJECT CODE</td>
                        <td class="po-meta-data"><span id="project_list"></span></td>
                    </tr>
                    <tr>
                        <td class="po-meta-key">REF. NO</td>
                        <td class="po-meta-data"><span id="po_ref_no"></span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="spacer"></div>

        <div class="row" style="display:flex;">
            <div class="col-6" style="width:100%;">
                <table class="head-table payment-summary-table">
                    <tr>
                        <th>
                            Ordered To
                        </th>
                        
                    </tr>
                    <tr>
                        <td class="ordered-to-cell">
                            <div class="party-info-wrap">
                                <table class="info-table">
                                    <tr>
                                        <td class="info-label">Name:</td>
                                        <td class="info-value"><span id="po_vendor1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Address:</td>
                                        <td class="info-value"><span id="po_vendor_address1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Contact Person:</td>
                                        <td class="info-value"><span id="po_vendor_contact_person1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Contact No.:</td>
                                        <td class="info-value"><span id="po_vendor_contact_no1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Email Address:</td>
                                        <td class="info-value"><span id="po_vendor_email1"></span></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="head-table special-instruction-table">
                    <tr>
                        <th>Payment Terms</th>
                        <th>Payment Due Date</th>
                        <th>Delivery</th>
                        <th>Delivery Date</th>
                    </tr>
                    <tr>
                        <td><span id="po_terms1"></span></td>
                        <td><span id="po_due_date1"></span></td>
                        <td><span id="po_delivery1"></span></td>
                        <td><span id="po_delivery_date1"></span></td>
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
                        <td class="ship-to-cell">
                            <div class="party-info-wrap">
                                <table class="info-table">
                                    <tr>
                                        <td class="info-label">Project Name:</td>
                                        <td class="info-value"><span id="po_ship_to_project_name1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Address:</td>
                                        <td class="info-value"><span id="po_ship_to_address1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Contact Person:</td>
                                        <td class="info-value"><span id="po_ship_to_contact_person1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Contact No:</td>
                                        <td class="info-value"><span id="po_ship_to_contact_no1"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Email Address:</td>
                                        <td class="info-value"><span id="po_ship_to_email1"></span></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="head-table">
                    <tr>
                        <th>Special Instruction</th>
                    </tr>
                    <tr>
                        <td class="special-instruction-cell"><span id="po_special_instruction1" class="special-instruction-text"></span></td>
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
                            <th>Item Code</th>
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
                        <table class="desc-table totals-table">
                            <thead>
                            <tr>
                              
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="discount-container"></tr>
                            <tr>
                                <td class="total text-right">Sub-total</td>
                                <td class="total-amount text-right"><span id="po_subtotal"></span></td>
                            </tr>
                            <tr>
                                <td class="total text-right"><span id="po_tax_label">Tax</span></td>
                                <td class="total-amount text-right"><span id="po_tax"></span></td>
                            </tr>
                            <tr>
                                <td class="total text-right">Other Cost</td>
                                <td class="total-amount text-right"><span id="po_other_cost"></span></td>
                            </tr>
                            <tr class="other-cost-breakdown-row">
                                <td colspan="2">
                                    <div class="other-cost-breakdown-text" id="po_other_cost_breakdown_text">-</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="total text-right">Total Amount</td>
                                <td class="total-amount text-right"><span id="po_total"></span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="po-notes mb-3">
                    <div class="row">
                        <div class="col-8 pr-1 notes-plain">
                            <div class="notes-title">IMPORTANT NOTES</div>
                            <ol class="notes-list">
                                <li>Prices stated in the P.O are final and agreed upon</li>
                                <li>Please indicate the P.O number on all invoices and delivery receipts</li>
                                <li>Over-or under-delivery may be rejected</li>
                                <li>All items are subject to inspection upon delivery</li>
                                <li>Items must match the exact quantity, brand and specifications stated in the P.O. Defective or incorrect items will be returned at supplier's expense. Goods rejected on account of inferior quality, workmanship and hidden defect will be returned to the above supplier who shall deliver the replacement within 24 hours, unless a longer time is agreed upon by both parties, or unless SPCC notifies the supplier the rescission of this purchase transaction. In the event of rescission, the supplier shall return any and all amounts paid by SPCC on the same day the defective goods are returned to the supplier. Delay on the part of the supplier to return whatever amounts paid by SPCC shall make the supplier liable for interest of 1% per day of delay, out of the total amount owed to SPCC</li>
                                <li>Payment will only be processed upon complete and correct documentation accompanied by this Purchase Order</li>
                                <li>Supplier guarantees that the items are free from defects and covered by warranty</li>
                                <li>SPCC reserves the right to cancel the P.O if terms are not met</li>
                                <li>This Purchase Order is valid only when signed by an authorized representative</li>
                                <li>Penalty clause: In cases of delay in the delivery of goods, the supplier shall be liable to SPCC at the rate of 1% per day of delay</li>
                            </ol>
                        </div>
                        <div class="col-4 pl-1 remarks-plain">
                            <div class="remarks-title">REMARKS</div>
                            <div id="po_remarks"></div>
                        </div>
                    </div>
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
            font-size: 12px;
            color: #1e2d4d;
        }

        table.head-table td, table.desc-table td, table.footer-table td, table.head-table th, table.desc-table th, table.footer-table th {
            border: 1px solid #8795ad;
            text-align: left;
            padding: 7px 8px;
        }
        table.head-table th, table.desc-table th, table.footer-table th {
            background-color: #edf2fa;
            color: #223b67;
            font-weight: 700;
        }

        table.head-table td, table.desc-table td, table.footer-table td {
            background-color: white;
        }

        p.c-name {
            margin-bottom: 0px !important;
            font-size: 34px;
            font-weight: bold;
            color: #15305b;
        }
        p.c-address {
            font-size: 18px;
            line-height: 24px;
            color: #34486d;
        }
        img.logo-po {
            width: 100px;
            float: right;
            margin-right: 10px;
        }
        p.po-title {
            font-size: 28px;
            font-weight: bold;
            color: #15305b;
        }
        .po-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .po-meta-table td {
            border: 1px solid #8795ad;
            padding: 5px 7px;
            vertical-align: middle;
        }
        .po-meta-key {
            width: 40%;
            border-right: 1px solid #8795ad;
            background: #edf2fa;
            font-weight: bold;
        }
        .po-meta-data {
            width: 60%;
            background: #fff;
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
        .po-total {
            width: 33.34%;
            margin-left: auto;
        }
        .po-total .desc-table {
            width: 100%;
        }
        .totals-table {
            table-layout: fixed;
        }
        .totals-table td {
            height: 36px;
            padding: 6px 10px !important;
        }
        .totals-table td.total {
            width: 50%;
            font-size: 14px;
        }
        .totals-table td.total-amount {
            width: 50%;
            font-size: 14px;
            font-weight: bold;
            text-align: right !important;
            white-space: nowrap;
        }
        .other-cost-breakdown-row td {
            padding: 3px 6px !important;
            background: #fff !important;
        }
        .other-cost-breakdown-text {
            font-size: 10px;
            line-height: 1.2;
            color: #3a4f75;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .po-notes {
            margin-top: 8px;
        }
        .notes-title,
        .remarks-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 12px;
        }
        .notes-title {
            color: #b91c1c;
        }
        .notes-list {
            margin: 0;
            padding-left: 18px;
            color: #1e2d4d;
            font-size: 11px;
            line-height: 1.3;
        }
        .notes-list li {
            margin-bottom: 2px;
        }
        .remarks-plain #po_remarks {
            min-height: 96px;
            white-space: pre-line;
            word-break: break-word;
            font-size: 11px;
            line-height: 1.3;
            color: #1e2d4d;
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
            font-size: 14px;
        }
        td.total-amount {
            text-align: right;
            background: #f4f7fc;
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
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table.info-table td {
            border: none !important;
            padding: 3px 4px;
            vertical-align: top;
            background: white !important;
        }
        .info-label {
            width: 120px;
            font-weight: bold;
            white-space: nowrap;
            color: #223b67;
        }
        .info-value {
            white-space: pre-line;
            word-break: break-word;
        }
        .ordered-to-cell,
        .ship-to-cell {
            min-height: 155px;
            vertical-align: top;
        }
        .party-info-wrap {
            min-height: 155px;
        }
        .special-instruction-cell {
            vertical-align: top;
        }
        .special-instruction-text {
            display: block;
            min-height: 44px;
            white-space: pre-line;
            word-break: break-word;
        }
        .payment-summary-table,
        .special-instruction-table {
            table-layout: fixed;
        }
        .payment-summary-table th,
        .special-instruction-table th {
            min-height: 34px;
            vertical-align: middle;
        }
        .payment-summary-table td,
        .special-instruction-table td {
            min-height: 52px;
            vertical-align: top;
        }

@media print {

    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    .no-print {
        display: none !important;
    }

    /* Hide side panels / preview panes */
    .side-panel,
    .sidebar,
    .preview-panel {
        display: none !important;
    }

    body{
        margin: 0 !important;
        padding: 0 !important;
        background: white;
    }

    /* Keep content natural; footer is pinned to page bottom */
    #printPO{
        display: block;
        height: auto;
        padding-bottom: 0 !important;
        box-sizing: border-box;
    }

    .po-footer{
        margin-top: 0 !important;
    }

    .print-footer{
        position: fixed;
        left: 8mm;
        right: 8mm;
        bottom: 8mm;
        width: auto;
        background: white;
        padding: 0;
    }

    /* Prevent tables breaking */
    table{
        page-break-inside: avoid;
    }

    .po-footer{ margin-top: 0 !important; }
}
    </style>
    </div>
</div>
