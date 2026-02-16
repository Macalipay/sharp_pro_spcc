<div id="auditTrailScreen" class="content-employee">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">AUDIT TRAIL</h5>
        <button type="button" class="btn btn-sm btn-primary" onclick="printAuditTrail()">
            <i class="fas fa-print"></i> PRINT AUDIT TRAIL
        </button>
    </div>

    <div id="auditTrailPrintSection">
        <div class="table-responsive">
            <table class="table table-sm table-bordered audit-trail-table">
                <thead>
                    <tr>
                        <th>USER</th>
                        <th>TYPE OF CHANGE</th>
                        <th>DESCRIPTION</th>
                        <th>TIMESTAMP</th>
                    </tr>
                </thead>
                <tbody id="auditTrailBody">
                    <tr>
                        <td colspan="4" class="text-center">No Data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
