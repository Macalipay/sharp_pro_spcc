<div class="row header-bar">
    <div class="col-12">
        <div class="action-button">
            <button type="button" onclick="scion.record.new()" class="create" id="nw" >
                <i class="fas fa-plus"></i> New
            </button>
            <button type="submit" onclick="scion.record.save(generateData, success, error)" class="save" id="sv" >
                <i class="fas fa-save"></i> Save
            </button>
            <button type="button" onclick="scion.record.delete(generateDeleteItems)" class="delete" id="dlt" >
                <i class="fas fa-trash-alt"></i> Delete
            </button>
            <button type="button" onclick="scion.print()" class="print" id="prnt" >
                <i class="fas fa-print"></i> Print
            </button>
            <span id="additional_buttons"></span>
        </div>
    </div>
</div>


<div id="deleteModal" class="delete-modal">
    <div class="delete-message">
        <div class="message">
            <div class="message-icon"><i class="fas fa-question"></i></div>
            YOU'VE SELECTED <span id="selectedCount">0</span> RECORD/S. <br>
            ARE YOU SURE YOU WANT TO DELETE THIS RECORD?
        </div>
        <div class="confirm-action text-right">
            <button class="btn btn-primary" onclick="scion.record.delete().yes(delete_success, delete_error)">YES</button>
            <button class="btn btn-secondary" onclick="scion.record.delete().no()">NO</button>
        </div>
    </div>
</div>