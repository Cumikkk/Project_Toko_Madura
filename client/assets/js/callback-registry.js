import Store from "./web-trade/store"
import Market from "./web-trade/market";
import Trade from "./web-trade/trade";

const modal = $('#dynamicModalDefault');
const CallbackRegistry = {
    assignSymbolNewOrder: (element) => {
        let defaultSymbol = element?.dataset?.default;
        let symbols = Store.symbolStore().get()

        $('#market-symbols').empty().html(
            Array.from(
                symbols
                .values())
                .map(s => `<option value="${s.symbol}" ${s.symbol === defaultSymbol ? 'selected' : ''} data-min="${s.volume_min}" data-max="${s.volume_max}" data-digits="${s.digits}">${s.symbol}</option>`)
                .join('')
        )

        function showExchangeExecution() {
            document.getElementById('exchange_execution').style.display = 'block';
            document.getElementById('pending_order').style.display = 'none';
        }

        function showPendingOrder() {
            document.getElementById('exchange_execution').style.display = 'none';
            document.getElementById('pending_order').style.display = 'block';
        }

        $('#market-type').on('change', function() {
            if (this.value === 'exchange_execution') {
                showExchangeExecution();
            } else if (this.value === 'pending_order') {
                showPendingOrder();
            }  
        });

        $('#market-symbols').on('change', function() {
            let selectedOption = $(this).find('option:selected');
            let minVolume = selectedOption.data('min');
            let maxVolume = selectedOption.data('max');
            $('#exe-volume').attr('min', minVolume);
            $('#exe-volume').attr('max', maxVolume);
            $('#exe-volume').val(minVolume);
            
            // /** Set SL and TP step according to symbol digits */
            // $('#exe-sl').val(0);
            // $('#exe-sl').attr('step', Math.pow(10, -selectedOption.data('digits')));
            
            // $('#exe-tp').val(0);
            // $('#exe-tp').attr('step', Math.pow(10, -selectedOption.data('digits')));

            $('#po-volume').attr('min', minVolume);
            $('#po-volume').attr('max', maxVolume);
            $('#po-volume').val(minVolume);
            
            /** Set SL, TP, and price step according to symbol digits */
            $('#po-sl').val(0);
            $('#po-sl').attr('step', Math.pow(10, -selectedOption.data('digits')));
            
            $('#po-tp').val(0);
            $('#po-tp').attr('step', Math.pow(10, -selectedOption.data('digits')));
            
            $('#po-price').val(0);
            $('#po-price').attr('step', Math.pow(10, -selectedOption.data('digits')));
        }).change();

        // $('#exchange_execution').on('focus', '#exe-sl, #exe-tp', function() {
        //     this.value = symbols.get($('#market-symbols').val()).bid || 0
        // })

        $('#pending_order').on('focus', '#po-price, #po-sl, #po-tp', function() {
            if(!this.value || this.value == 0) {
                this.value = symbols.get($('#market-symbols').val()).bid || 0
            }
        })

        $('.exe-action').on('click', function(event) {
            let button = $(event.currentTarget);
            let type = button.data('type');

            Swal.fire({
                text: "Order send...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            let orderData = {
                account: Store.accountStore().get(),
                symbol: $('#market-symbols').val(),
                volume: $('#exe-volume').val(),
                operation: type,
                // sl: $('#exe-sl').val(),
                // tp: $('#exe-tp').val(),
                price: $('#exe-price').val()
            };
            
            Market.open(orderData).then((response) => {
                Swal.fire(response.alert);
                modal.modal('hide');
            }).catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error || 'An error occurred while placing the order.'
                });
            });
        });

        $('#po-place').on('click', function() {
            Swal.fire({
                text: "Placing order...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            let orderData = {
                account: Store.accountStore().get(),
                symbol: $('#market-symbols').val(),
                volume: $('#po-volume').val(),
                operation: $('#po-type').val(),
                sl: $('#po-sl').val(),
                tp: $('#po-tp').val(),
                price: $('#po-price').val()
            };

            Market.open(orderData).then((response) => {
                Swal.fire(response.alert);
                modal.modal('hide');
            }).catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error || 'An error occurred while placing the order.',
                    didOpen: () => {
                        Swal.hideLoading();
                    }
                });
            });
        });
    },
    modifyOrder: (element) => {
        let modifyForm = $('#modify-order-form');
        let orderData = JSON.parse(atob(element.dataset.orders));

        if(!orderData) {
            modal.modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid order data.'
            });
            return;
        }

        if(!Object.hasOwn(orderData, 'is_pending') || typeof orderData.is_pending !== 'boolean') {
            orderData.is_pending = false;
        }

        let symbolInfo = Store.symbolStore().get().get(orderData.symbol);
        if(!symbolInfo) {
            modal.modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid symbol info.'
            });
            return;
        }

        let price = orderData.is_pending ? orderData.price_order : orderData.open_price;
        let operation = Trade.type[orderData.type].trim()?.toLowerCase().replace(/\s+/g, '');
        modifyForm.find('input[name="price"]').val(price);
        modifyForm.find('input[name="volume"]').val(orderData.volume);
        modifyForm.find('input[name="sl"]').val(orderData.stop_loss);
        modifyForm.find('input[name="tp"]').val(orderData.take_profit);

        $('#close-order').on('click', function() {
            Swal.fire({
                text: `${orderData.is_pending ? 'Cancel' : 'Closing'} order...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            let closeData = {
                account: Store.accountStore().get(),
                ticket: orderData.ticket,
                is_pending: +(orderData.is_pending)
            };

            Market.close(closeData).then((response) => {
                Swal.fire(response.alert);
                modal.modal('hide');
            }).catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error || 'An error occurred while closing the order.',
                    didOpen: () => {
                        Swal.hideLoading();
                    }
                });
            });
        })

        $('#modify-order-action').on('click', function() {
            Swal.fire({
                text: "Modifying order...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })
            
            let sl = modifyForm.find('input[name="sl"]').val();
            let tp = modifyForm.find('input[name="tp"]').val();
            let modifyData = {
                account: Store.accountStore().get(),
                symbolInfo: symbolInfo,
                ticket: orderData.ticket,
                sl: Number(sl) || 0,
                tp: Number(tp) || 0,
                price: price,
                operation: operation,
                is_pending: +(orderData.is_pending)
            };

            Market.modify(modifyData).then((response) => {
                Swal.fire(response.alert);
                modal.modal('hide');
            }).catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error || 'An error occurred while modifying the order.',
                    didOpen: () => {
                        Swal.hideLoading();
                    }
                });
            });
        })
    }
}

export default CallbackRegistry;