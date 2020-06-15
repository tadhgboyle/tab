// This is blackmagic code that (somehow) handles the live sidebar
const checked = [];
let total_gst = total_pst = total_tax_percent = total_price = 0;
const current_gst = parseFloat(document.getElementById('current_gst').value).toFixed(2);
const current_pst = parseFloat(document.getElementById('current_pst').value).toFixed(2);

const purchaser_balance = parseFloat(document.getElementById('purchaser_balance').value).toFixed(2);

$("#gst").html('GST: $' + total_gst.toFixed(2));
$("#pst").html('PST: $' + total_pst.toFixed(2));
$("#total_price").html('Total Price: $' + total_price.toFixed(2));
$("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));

// Edge Case: Page was reloaded with things selected
// TODO: in progress. working basically, but when the item is unselected the Total Price is wrong
// Also, very inefficient code. will fix when fully workin'
// TODO: Loading spinner while this page loads
$(document).ready(function () {
    $('#order').find(':input').each(function () {
        const input = document.getElementById($(this).attr('id'));
        if (input != null) {
            if ($(input).attr('type') == 'checkbox') {
                if (input.checked) {
                    const quantity = parseFloat(document.getElementById('quantity[' + input.value + ']').value);
                    const info = document.getElementsByName('product[' + input.value + ']')[0].id;
                    const price = parseFloat(info.split('$')[1]);
                    const pst_id = document.getElementById('pst[' + input.value + ']').value;

                    if (pst_id == 1) {
                        // I dont know why we need parseFloat(), but shit breaks without it
                        total_tax_percent += (parseFloat(current_pst) + parseFloat(current_gst) - 1);
                        total_pst += price * quantity * current_pst - price * quantity;
                    } else total_tax_percent += current_gst;
                    total_gst += price * quantity * current_gst - price * quantity;
                    total_price += price * quantity * total_tax_percent;

                    checked.push(info + ' (x' + quantity + ')<br>');

                    $("#items").html(checked);
                    $("#gst").html('GST: $' + total_gst.toFixed(2));
                    $("#pst").html('PST: $' + total_pst.toFixed(2))

                    if (total_price > purchaser_balance) {
                        // Disable things if they do not have enough money to proceed
                        $('.disableable').prop('disabled', true);
                        $("#total_price").html('<span style="color:red">Total Price: $' + total_price.toFixed(2) + '</span>');
                        $("#remaining_balance").html('<span style="color:red">Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2) + '</span>');
                    } else {
                        $('.disableable').prop('disabled', false);
                        $("#total_price").html('Total Price: $' + total_price.toFixed(2));
                        $("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));
                    }
                }
            }
        }
    });
})

// Handle clicks on items
$('.clickable').click(function () {

    const current_id = document.getElementById($(this).attr('id')).value;
    const quantity_id = document.getElementById('quantity[' + current_id + ']');
    const quantity = parseInt(quantity_id.value);
    const pst_id = document.getElementById('pst[' + current_id + ']').value;
    const current_price = parseFloat($(this).attr('id').split('$')[1]);
    const list_style = $(this).attr('id') + ' (x' + quantity + ')<br>';

    if (quantity_id == 0) return;
    if (quantity > 0) {
        if ($(this).is(':checked')) {
            if (pst_id == 1) {
                // I dont know why we need parseFloat(), but shit breaks without it
                total_tax_percent += (parseFloat(current_pst) + parseFloat(current_gst) - 1);
                total_pst += current_price * quantity * current_pst - current_price * quantity;
            } else total_tax_percent += current_gst;
            quantity_id.disabled = true;
            checked.push(list_style);
            total_gst += (current_price * quantity) * current_gst - current_price * quantity;
            total_price += (current_price * quantity) * total_tax_percent;
        } else {
            if (pst_id == 1) {
                // I dont know why we need parseFloat(), but shit breaks without it
                total_tax_percent += (parseFloat(current_pst) + parseFloat(current_gst) - 1);
                total_pst -= current_price * quantity * current_pst - current_price * quantity;
            } else total_tax_percent += current_gst;
            quantity_id.disabled = false;
            const index = checked.indexOf(list_style);
            if (index >= 0) {
                checked.splice(index, 1);
                total_gst -= (current_price * quantity) * current_gst - current_price * quantity;
                total_price -= (current_price * quantity) * total_tax_percent;
            }
        }
    }

    $("#items").html(checked);
    $("#gst").html('GST: $' + total_gst.toFixed(2));
    $("#pst").html('PST: $' + total_pst.toFixed(2))

    if (total_price > purchaser_balance) {
        // Disable things if they do not have enough money to proceed
        $('.disableable').prop('disabled', true);
        $("#total_price").html('<span style="color:red">Total Price: $' + total_price.toFixed(2) + '</span>');
        $("#remaining_balance").html('<span style="color:red">Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2) + '</span>');
    } else {
        $('.disableable').prop('disabled', checked.length < 1);
        $("#total_price").html('Total Price: $' + total_price.toFixed(2));
        $("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));
    }
    // This is needed for some silly reason
    total_tax_percent = 0;
});

// Removes the "disabled" attribute from quantity fields. 
// Without this, no selected items are sent to the controller
$('form').submit(function (e) {
    $(':disabled').each(function (e) {
        $(this).removeAttr('disabled');
    })
});