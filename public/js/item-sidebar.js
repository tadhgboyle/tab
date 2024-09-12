let ITEMS = [];
const PST_AMOUNT = document.getElementById('current_pst').value;
const GST_AMOUNT = document.getElementById('current_gst').value;
const PURCHASER_ID = document.getElementById('purchaser_id').value;

const APPLY_GIFT_CARD_BUTTON = document.getElementById('apply_gift_card');
const REMOVE_GIFT_CARD_BUTTON = document.getElementById('remove_gift_card');
const GIFT_CARD_CODE_INPUT = document.getElementById('gift_card_code_input');
let GIFT_CARD = null;

window.onload = () => {
    const storedItems = localStorage.getItem(`items-${PURCHASER_ID}`);
    if (storedItems !== null) {
        ITEMS = JSON.parse(storedItems);
    }

    const storedGiftCard = localStorage.getItem(`gift-card-${PURCHASER_ID}`);
    if (storedGiftCard !== null) {
        GIFT_CARD = JSON.parse(storedGiftCard);
        GIFT_CARD_CODE_INPUT.value = GIFT_CARD.code;
        giftCardHelpNotice(true);
    }

    render();
};

const addProduct = async (productId, variantId = 0) => {
    // TODO: check stock and don't add if it would be over the limit
    await fetch(`/admin/products/${productId}/info?variantId=${variantId}`)
        .then(resp => resp.json())
        .then(async (product) => {
            let quantity = 1;

            let existingIndex;
            if (variantId === 0) {
                existingIndex = indexByProductId(productId);
            } else {
                existingIndex = indexByProductIdAndVariantId(productId, variantId);
            }
            if (existingIndex !== -1) {
                quantity += ITEMS[existingIndex].quantity;
            }

            const products = {};

            productsByCategory(product.categoryId).forEach(item => {
                products[item.id] = {};
                products[item.id][item.variantId] = item.quantity;
            });

            products[productId] = {};
            products[productId][variantId] = quantity;

            await fetch(`/admin/users/${PURCHASER_ID}/check-limit/${product.categoryId}?products=${JSON.stringify(products)}`)
                .then(resp => resp.json())
                .then(data => {
                    if (data.can_spend) {
                        if (existingIndex !== -1) {
                            ITEMS[existingIndex].quantity = quantity;
                        } else {
                            ITEMS.push({
                                id: product.id,
                                variantId: variantId,
                                variantDescription: product.variantDescription,
                                categoryId: product.categoryId,
                                name: product.name,
                                price: product.price,
                                tax: {
                                    pst: product.pst,
                                    gst: product.gst,
                                },
                                quantity: quantity,
                            });    
                        }

                        cacheItems();
                        render();
                    } else {
                        alert(`You can't spend more than ${data.limit} on this category per ${data.duration}.`);
                    }
                });
        });
};

const indexByProductId = (productId) => {
    return ITEMS.findIndex(item => item.id === productId);
};

const indexByProductIdAndVariantId = (productId, variantId) => {
    return ITEMS.findIndex(item => item.id === productId && item.variantId === variantId);
};

const productsByCategory = (categoryId) => {
    return ITEMS.filter(item => item.categoryId === categoryId);
};

const removeProductSingle = (productId, variantId) => {
    let index;
    if (variantId === 0) {
        index = indexByProductId(productId);
    } else {
        index = indexByProductIdAndVariantId(productId, variantId);
    }

    if (index === -1) {
        return;
    }

    const REMOVING = 1;

    const currentQuantity = ITEMS[index].quantity;
    const newQuantity = currentQuantity - REMOVING;

    if (newQuantity === 0) {
        removeProductAll(productId, variantId);
        return;
    }

    ITEMS[index].quantity = newQuantity;

    cacheItems();
    render();
};

const removeProductAll = (productId, variantId) => {
    let index;
    if (variantId === 0) {
        index = indexByProductId(productId);
    } else {
        index = indexByProductIdAndVariantId(productId, variantId);
    }

    if (index === -1) {
        return;
    }

    ITEMS = ITEMS.filter(item => item.id !== productId || item.variantId !== variantId);

    cacheItems();
    render();
};

const handleSubmit = () => {
    document.getElementById('products').value = JSON.stringify(
        ITEMS.map(item => {
            return {
                id: item.id,
                variantId: item.variantId,
                quantity: item.quantity,
            }
        }),
    );
    document.getElementById('gift_card_code').value = GIFT_CARD ? GIFT_CARD.code : null;

    document.forms.namedItem('order').submit();
};

const cacheItems = () => {
    localStorage.setItem(`items-${PURCHASER_ID}`, JSON.stringify(ITEMS));
};

const cacheGiftCard = () => {
    localStorage.setItem(`gift-card-${PURCHASER_ID}`, JSON.stringify(GIFT_CARD));
};

const removeCachedGiftCard = () => {
    localStorage.removeItem(`gift-card-${PURCHASER_ID}`);
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
            nameTd.innerText = (item.variantId ? item.variantDescription : item.name);
            tr.appendChild(nameTd);
            const quantityTd = document.createElement('td');
            quantityTd.innerText = `${item.quantity} `;

            const removeSingleButton = document.createElement('button');
            removeSingleButton.setAttribute('onclick', `removeProductSingle(${item.id}, ${item.variantId});`);
            removeSingleButton.classList.add('button', 'is-small');
            const removeSingleButtonIconSpan = document.createElement('span');
            removeSingleButtonIconSpan.classList.add('icon', 'is-small');
            const removeSingleButtonIcon = document.createElement('i');
            removeSingleButtonIcon.classList.add('fas', 'fa-minus');
            removeSingleButtonIconSpan.appendChild(removeSingleButtonIcon);
            removeSingleButton.appendChild(removeSingleButtonIconSpan);
            quantityTd.appendChild(removeSingleButton);

            const removeAllButton = document.createElement('button');
            removeAllButton.setAttribute('onclick', `removeProductAll(${item.id}, ${item.variantId});`);
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
    document.getElementById('gst-total').innerText = `$${gstTotal.toFixed(2)}`;
    document.getElementById('pst-total').innerText = `$${pstTotal.toFixed(2)}`;
    document.getElementById('total-price').innerText = `$${calculateTotalPrice().toFixed(2)}`;
    if (GIFT_CARD) {
        document.getElementById('gift-card-row').style.display = 'table-row';
        document.getElementById('gift-card-balance').innerText = `-$${GIFT_CARD.remaining_balance.toFixed(2)}`;
    } else {
        document.getElementById('gift-card-row').style.display = 'none';
    }
    document.getElementById('purchaser-amount').innerText = `$${calculatePurchaserAmount().toFixed(2)}`;
    document.getElementById('remaining-balance').innerText = `$${remainingBalance.toFixed(2)}`;

    if (remainingBalance < 0) {
        document.getElementById('remaining-balance').classList.add('has-text-danger');
    } else {
        document.getElementById('remaining-balance').classList.remove('has-text-danger');
    }

    if (ITEMS.length === 0) {
        document.getElementById('submit-button').disabled = true;
    } else {
        if (remainingBalance < 0) {
            document.getElementById('submit-button').disabled = true;
        } else {
            document.getElementById('submit-button').disabled = false;
        }
    }

    GIFT_CARD_CODE_INPUT.addEventListener('keyup', toggleGiftCardApplyButton);
    APPLY_GIFT_CARD_BUTTON.addEventListener('click', addGiftCard);
    REMOVE_GIFT_CARD_BUTTON.addEventListener('click', removeGiftCard);

    toggleGiftCardApplyButton();
};

const calculateSubtotal = () => {
    return ITEMS.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
};

const calculateGiftCardTotal = () => {
    if (!GIFT_CARD) {
        return 0;
    }

    return GIFT_CARD.remaining_balance;
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

    const { pstTotal, gstTotal } = calculateTaxTotals();
    const taxTotal = pstTotal + gstTotal;

    return subtotal + taxTotal;
};

const calculatePurchaserAmount = () => {
    const total = calculateTotalPrice();

    const giftCardTotal = calculateGiftCardTotal();

    if (giftCardTotal > total) {
        return 0;
    } else if (giftCardTotal > 0) {
        return total - giftCardTotal;
    } else {
        return total;
    }
};

const calculateRemainingBalance = () => {
    return document.getElementById('purchaser_balance').value - calculatePurchaserAmount();
};

const toggleGiftCardApplyButton = () => {
    APPLY_GIFT_CARD_BUTTON.disabled = GIFT_CARD || GIFT_CARD_CODE_INPUT.value.length === 0;

    REMOVE_GIFT_CARD_BUTTON.style.display = GIFT_CARD ? 'block' : 'none'
    APPLY_GIFT_CARD_BUTTON.style.display = GIFT_CARD ? 'none' : 'block'
}

const addGiftCard = async () => {
    const giftCardCode = GIFT_CARD_CODE_INPUT.value;

    await fetch(`/admin/gift-cards/check-validity?code=${giftCardCode}&purchaser_id=${PURCHASER_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                GIFT_CARD = {
                    ...data, code: giftCardCode
                };
                cacheGiftCard();
                render();
            }
            giftCardHelpNotice(data.valid, data.message);
        });
};

const removeGiftCard = () => {
    removeCachedGiftCard();
    removeGiftCardHelpNotice();
    GIFT_CARD = null;
    GIFT_CARD_CODE_INPUT.value = '';
    GIFT_CARD_CODE_INPUT.disabled = false;
    render();
};

const giftCardHelpNotice = (success, message = null) => {
    removeGiftCardHelpNotice();

    GIFT_CARD_CODE_INPUT.classList.add(success ? 'is-success' : 'is-danger');

    if (success) {
        GIFT_CARD_CODE_INPUT.disabled = true;
        APPLY_GIFT_CARD_BUTTON.disabled = true;
    }

    const help = document.createElement('p');
    help.classList.add('help', success ? 'is-success' : 'is-danger');
    help.classList.add('is-pulled-left');
    help.innerText = success ? 'Gift card applied' : message;
    GIFT_CARD_CODE_INPUT.parentNode.appendChild(help);
};

const removeGiftCardHelpNotice = () => {
    GIFT_CARD_CODE_INPUT.classList.remove('is-success', 'is-danger');

    const existingHelp = document.querySelector('.help');
    if (existingHelp) {
        existingHelp.remove();
    }
}
