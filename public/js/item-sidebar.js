let ITEMS = [];
const PST_AMOUNT = document.getElementById('current_pst').value - 1;
const GST_AMOUNT = document.getElementById('current_gst').value - 1;
const PURCHASER_ID = document.getElementById('purchaser_id').value

window.onload = () => {
    const storedItems = localStorage.getItem(`items-${PURCHASER_ID}`);
    if (storedItems !== null) {
        ITEMS = JSON.parse(storedItems);
    }

    render();
};

const addProduct = async (productId) => {
    await fetch(`/products/${productId}`)
        .then(resp => resp.json())
        .then(product => {
            let quantity = 1;

            const existingIndex = indexByProductId(productId);
            if (existingIndex !== -1) {
                quantity += ITEMS[existingIndex].quantity;
                ITEMS = ITEMS.filter(item => item.id !== productId);
            }

            ITEMS.push({
                id: product.id,
                name: product.name,
                price: product.price,
                tax: {
                    pst: product.pst,
                    gst: product.gst,
                },
                quantity: quantity,
            });

            cacheItems();
            render();
        });
};

const indexByProductId = (productId) => {
    return ITEMS.findIndex(item => item.id === productId);
};

const removeProductSingle = (productId) => {
    const index = indexByProductId(productId);
    if (index === -1) {
        return;
    }

    const REMOVING = 1;

    const currentQuantity = ITEMS[index].quantity;
    const newQuantity = currentQuantity - REMOVING;

    if (newQuantity === 0) {
        removeProductAll(productId);
        return;
    }

    ITEMS[index].quantity = newQuantity;

    cacheItems();
    render();
};

const removeProductAll = (productId) => {
    const index = indexByProductId(productId);
    if (index === -1) {
        return;
    }

    ITEMS = ITEMS.filter(item => item.id !== productId);

    (productId, 0);
    cacheItems();
    render();
};

const handleSubmit = () => {
    document.getElementById('products').value = JSON.stringify(
        ITEMS.map(item => {
            return {
                id: item.id,
                quantity: item.quantity,
            }
        }),
    );

    document.forms.namedItem('order').submit();
};

const cacheItems = () => {
    localStorage.setItem(`items-${PURCHASER_ID}`, JSON.stringify(ITEMS));
};

const render = () => {
    for (const itemRow of document.getElementsByClassName('item-row')) {
        itemRow.style.display = 'none';
    }

    if (ITEMS.length > 0) {
        document.getElementById('no-items').style.display = 'none';

        ITEMS.forEach(item => {
            const tr = document.createElement('tr');
            tr.classList.add('item-row');

            const nameTd = document.createElement('td');
            nameTd.innerText = item.name;
            tr.appendChild(nameTd);
            const quantityTd = document.createElement('td');
            quantityTd.innerText = `${item.quantity} `;

            const removeSingleButton = document.createElement('button');
            removeSingleButton.setAttribute('onclick', `removeProductSingle(${item.id});`);
            removeSingleButton.classList.add('button', 'is-small');
            const removeSingleButtonIconSpan = document.createElement('span');
            removeSingleButtonIconSpan.classList.add('icon', 'is-small');
            const removeSingleButtonIcon = document.createElement('i');
            removeSingleButtonIcon.classList.add('fas', 'fa-minus');
            removeSingleButtonIconSpan.appendChild(removeSingleButtonIcon);
            removeSingleButton.appendChild(removeSingleButtonIconSpan);
            quantityTd.appendChild(removeSingleButton);

            const removeAllButton = document.createElement('button');
            removeAllButton.setAttribute('onclick', `removeProductAll(${item.id});`);
            removeAllButton.classList.add('button', 'is-small');
            const removeAllButtonIconSpan = document.createElement('span');
            removeAllButtonIconSpan.classList.add('icon', 'is-small');
            const removeAllButtonIcon = document.createElement('i');
            removeAllButtonIcon.classList.add('fas', 'fa-times');
            removeAllButtonIconSpan.appendChild(removeAllButtonIcon);
            removeAllButton.appendChild(removeAllButtonIconSpan);
            quantityTd.appendChild(removeAllButton);

            tr.appendChild(quantityTd);

            const priceTd = document.createElement('td');
            priceTd.innerText = `$${(item.price * item.quantity).toFixed(2)}`;
            tr.appendChild(priceTd);

            document.getElementById('items-table').appendChild(tr);
        });
    } else {
        document.getElementById('no-items').style.display = 'block';
    }

    const { pstTotal, gstTotal } = calculateTaxTotals();
    const remainingBalance = calculateRemainingBalance();

    document.getElementById('subtotal-total').innerText = `$${calculateSubtotal().toFixed(2)}`;
    document.getElementById('pst-total').innerText = `$${pstTotal.toFixed(2)}`;
    document.getElementById('gst-total').innerText = `$${gstTotal.toFixed(2)}`;
    document.getElementById('total-price').innerText = `$${calculateTotalPrice().toFixed(2)}`;
    document.getElementById('remaining-balance').innerText = `$${remainingBalance.toFixed(2)}`;

    if (remainingBalance < 0) {
        document.getElementById('remaining-balance').classList.add('has-text-danger');
        document.getElementById('submit-button').disabled = true;
    } else {
        document.getElementById('remaining-balance').classList.remove('has-text-danger');
        document.getElementById('submit-button').disabled = false;
    }
};

const calculateSubtotal = () => {
    return ITEMS.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
};

const calculateTaxTotals = () => {
    let pstItemTotal = 0;
    let gstItemTotal = 0;

    ITEMS.forEach(item => {
        const { pst, gst } = item.tax;
        const itemTotal = item.price * item.quantity;

        if (pst) {
            pstItemTotal += itemTotal;
        }

        if (gst) {
            gstItemTotal += itemTotal;
        }
    });

    return {
        pstTotal: pstItemTotal * PST_AMOUNT,
        gstTotal: gstItemTotal * GST_AMOUNT,
    };
};

const calculateTotalPrice = () => {
    const subtotal = calculateSubtotal();

    let taxTotal = 0;
    const { pstTotal, gstTotal } = calculateTaxTotals();
    taxTotal = pstTotal + gstTotal;

    return subtotal + taxTotal;
};

const calculateRemainingBalance = () => {
    return document.getElementById('purchaser_balance').value - calculateTotalPrice();
};
