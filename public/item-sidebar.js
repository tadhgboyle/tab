// This is blackmagic code that (somehow) handles the live sidebar
const checked = [];
let total_gst = 0.00;
let total_pst = 0.00;
const current_gst = parseFloat(document.getElementById('current_gst').value).toFixed(2);
const current_pst = parseFloat(document.getElementById('current_pst').value).toFixed(2);
let total_tax_percent = 0.00;
let total_price = 0.00;
const purchaser_balance = parseFloat(document.getElementById('purchaser_balance').value).toFixed(2);
let quantity = 1;

$("#gst").html('GST: $' + total_gst.toFixed(2));
$("#pst").html('PST: $' + total_pst.toFixed(2));
$("#total_price").html('Total Price: $' + total_price.toFixed(2));
$("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));

$('.clickable').click(function () {

    const current_id = document.getElementById($(this).attr('id')).value;
    const quantity_id = document.getElementById('quantity[' + current_id + ']');
    const quantity = parseInt(quantity_id.value);
    const pst_id = document.getElementById('pst[' + current_id + ']').value;
    const current_price = parseFloat($(this).attr('id').split('$')[1]);

    if (quantity_id == 0) return;
    // If we click to check an item
    if (quantity > 0) {
        if ($(this).is(':checked')) {
            if (pst_id == 1) {
                total_tax_percent += (parseFloat(current_pst) + parseFloat(current_gst) - 1).toFixed(2);
                total_pst += parseFloat((current_price * quantity) * current_pst) - current_price * quantity;
            } else {
                total_tax_percent += parseFloat(current_gst).toFixed(2);
            }
            // Set the quantity input to disabled
            quantity_id.disabled = true;
            // Get quantity of this item and add to checked array
            checked.push($(this).attr('id') + ' (x' + quantity + ')<br>');
            // Add prices * quantity for taxes and total cost
            total_gst += parseFloat((current_price * quantity) * current_gst) - current_price * quantity;
            total_price += (current_price * quantity) * total_tax_percent;
        }
        // If we unclick an item
        else {
            // If the item has PST, remove it from total_pst
            if (pst_id == 1) {
                total_tax_percent += (parseFloat(current_pst) + parseFloat(current_gst) - 1).toFixed(2);
                total_pst -= parseFloat((current_price * quantity) * current_pst) - current_price * quantity;
            } else {
                total_tax_percent += parseFloat(current_gst).toFixed(2);
            }
            // Allow editing of quantity again
            quantity_id.disabled = false;
            // Find the current item in the checked array and remove it
            const index = checked.indexOf($(this).attr('id') + ' (x' + quantity + ')<br>');
            if (index >= 0) {
                checked.splice(index, 1);
                // Then subtract cost from taxes and price
                total_gst -= parseFloat((current_price * quantity) * current_gst) - current_price * quantity;
                total_price -= (current_price * quantity) * total_tax_percent;
            }
        }
    }

    $("#items").html(checked);
    $("#gst").html('GST: $' + total_gst.toFixed(2));
    $("#pst").html('PST: $' + total_pst.toFixed(2))
    $("#total_price").html('Total Price: $' + total_price.toFixed(2));
    $("#remaining_balance").html('Remaining Balance: $' + purchaser_balance);

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
    total_tax_percent = 0.00;
});

// Removes the "disabled" attribute from quantity fields. 
// Without this, no selected items are sent to the controller
$('form').submit(function (e) {
    $(':disabled').each(function (e) {
        $(this).removeAttr('disabled');
    })
});