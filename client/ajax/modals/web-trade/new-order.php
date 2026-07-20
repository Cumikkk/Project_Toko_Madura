<form action="" method="post">
    <div class="row mb-5">
        <div class="col-12">
            <label for="market-symbols" class="form-label">Symbol</label>
            <select id="market-symbols" class="form-select">
                <option selected>Pilih</option>
            </select>
        </div>
        <div class="col-12">
            <label for="market-type" class="form-label">Type</label>
            <select id="market-type" class="form-select">
                <option selected value="exchange_execution">Market Execution</option>
                <option value="pending_order">Pending Order</option>
            </select>
        </div>
    </div>

    <div id="exchange_execution" style="display: block;">
        <div class="row">
            <div class="col-12">
                <label for="exe-volume" class="form-label">Volume</label>
                <input type="number" name="exe-volume" value="0.01" id="exe-volume" class="form-control" min="0.01" max="12" step="0.01" required>
            </div>
            <!-- <div class="col-6">
                <label for="exe-sl" class="form-label">SL</label>
                <input type="number" name="exe-sl" id="exe-sl" class="form-control" value="0" min="0" step="0.01" required>
            </div>
            <div class="col-6">
                <label for="exe-tp" class="form-label">TP</label>
                <input type="number" name="exe-tp" id="exe-tp" class="form-control" value="0" min="0" step="0.01" required>
            </div> -->
            <div class="col-6">
                <button type="button" data-type="sell" class="btn btn-block btn-danger w-100 mb-3 exe-action">Sell</button>
            </div>
            <div class="col-6">
                <button type="button" data-type="buy" class="btn btn-block btn-success w-100 mb-3 exe-action">Buy</button>
            </div>
        </div>
    </div>

    <div id="pending_order" style="display : none;">
        <div class="row">
            <div class="col-12">
                <label for="po-type" class="form-label">Type</label>
                <select id="po-type" class="form-select">
                    <option value="buylimit">Buy Limit</option>
                    <option value="selllimit">Sell Limit</option>
                    <option value="buystop">Buy Stop</option>
                    <option value="sellstop">Sell Stop</option>
                </select>
            </div>
            <div class="col-12">
                <label for="po-volume" class="form-label">Volume</label>
                <input type="number" name="po-volume" id="po-volume" class="form-control" min="0.01" max="12" step="0.01" required>
            </div>
            <div class="col-12">
                <label for="po-price" class="form-label">Price</label>
                <input type="number" name="po-price" id="po-price" class="form-control" required>
            </div>
            <div class="col-6">
                <label for="po-sl" class="form-label">SL</label>
                <input type="number" name="po-sl" id="po-sl" class="form-control" value="0" required>
            </div>
            <div class="col-6">
                <label for="po-tp" class="form-label">TP</label>
                <input type="number" name="po-tp" id="po-tp" class="form-control" value="0" required>
            </div>
            <div class="col-12">
                <button type="button" id="po-place" name="po-place" class="btn btn-block btn-primary w-100 mb-3">Place</button>
            </div>
        </div>
    </div>
</form>