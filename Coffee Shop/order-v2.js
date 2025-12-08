document.addEventListener('DOMContentLoaded', function ready() {
    // Remove buttons
    var removecartitembuttons = document.getElementsByClassName('btn-danger');
    for (var i = 0; i < removecartitembuttons.length; i++) {
        var button = removecartitembuttons[i];
        button.addEventListener('click', removecartitem);
    }

    // Quantity inputs
    var quantityinput = document.getElementsByClassName('cart-quantity-input');
    for (var j = 0; j < quantityinput.length; j++) {
        var input = quantityinput[j];
        input.addEventListener('change', quantitychanged);
    }

    // Add to cart buttons
    var addtocartButtons = document.getElementsByClassName('shop-item-button');
    for (var k = 0; k < addtocartButtons.length; k++) {
        var cartbutton = addtocartButtons[k];
        cartbutton.addEventListener('click', addtocartclicked);
    }
});

// --- Cart logic ---

function removecartitem(event) {
    var buttonclicked = event.target;
    buttonclicked.parentElement.parentElement.remove();
    updatecarttotal();
}

function quantitychanged(event) {
    var input = event.target;
    if (isNaN(input.value) || input.value <= 0) {
        input.value = 1;
    }
    updatecarttotal();
}

function addtocartclicked(event) {
    var button = event.target;
    var shopitem = button.parentElement.parentElement;
    var title = shopitem.getElementsByClassName('shop-item-title')[0].innerText;
    var price = shopitem.getElementsByClassName('shop-item-price')[0].innerText;
    var imagesrc = shopitem.getElementsByClassName('shop-item-image')[0].src;
    additemtocart(title, price, imagesrc);
    updatecarttotal();
}

function additemtocart(title, price, imagesrc) {
    var cartrow = document.createElement('div');
    cartrow.classList.add('cart-row');

    var cartitems = document.getElementsByClassName('cart-items')[0];
    var cartitemNames = cartitems.getElementsByClassName('cart-item-title');
    for (var i = 0; i < cartitemNames.length; i++) {
        if (cartitemNames[i].innerText == title) {
            alert('Its already added to the cart');
            return;
        }
    }

    var cartrowcontents = `
        <div class="cart-item cart-column">
            <img class="cart-item-image" src="${imagesrc}" width="100" height="100">
            <span class="cart-item-title">${title}</span>
        </div>
        <span class="cart-price cart-column">${price}</span>
        <div class="cart-quantity cart-column">
            <input class="cart-quantity-input" type="number" value="1">
            <button class="btn btn-danger" type="button">REMOVE</button>
        </div>`;

    cartrow.innerHTML = cartrowcontents;
    cartitems.append(cartrow);

    cartrow.getElementsByClassName('btn-danger')[0].addEventListener('click', removecartitem);
    cartrow.getElementsByClassName('cart-quantity-input')[0].addEventListener('change', quantitychanged);
}

function updatecarttotal() {
    var cartitemcontainer = document.getElementsByClassName('cart-items')[0];
    var cartrows = cartitemcontainer.getElementsByClassName('cart-row');
    var total = 0;

    for (var i = 0; i < cartrows.length; i++) {
        var cartrow = cartrows[i];
        var priceelement = cartrow.getElementsByClassName('cart-price')[0];
        var quatityelement = cartrow.getElementsByClassName('cart-quantity-input')[0];
        var price = parseFloat(priceelement.innerText.replace(/[₺$]/g, ''));
        var quantity = quatityelement.value;
        total = total + (price * quantity);
    }
    total = Math.round(total * 100) / 100;
    document.getElementsByClassName('cart-total-price')[0].innerText = '₺' + total;
}

// --- ORDER + database saving ---

async function orderclicked() {
    const cartitems = document.getElementsByClassName('cart-items')[0];
    const cartRows = cartitems.getElementsByClassName('cart-row');
    const items = [];
    let total = 0;

    for (let i = 0; i < cartRows.length; i++) {
        const row = cartRows[i];

        const titleEl = row.querySelector('.cart-item-title');
        const priceEl = row.querySelector('.cart-price');
        const qtyEl   = row.querySelector('.cart-quantity-input');

        if (!titleEl || !priceEl || !qtyEl) continue;

        const name = titleEl.innerText.trim();
        const price = parseFloat(priceEl.innerText.replace(/[₺$]/g, ''));
        const quantity = parseInt(qtyEl.value || '1', 10);

        items.push({ name, price, quantity });
        total += price * quantity;
    }

    if (items.length === 0) {
        alert('Your cart is empty.');
        return;
    }

    // Ask for customer details again (now that parsing works)
    const customerName  = prompt('Enter your name (optional):')  || '';
    const customerEmail = prompt('Enter your email (optional):') || '';

    try {
        const res = await fetch('save_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                customerName,
                customerEmail,
                items,
                total: Math.round(total * 100) / 100
            })
        });

        const data = await res.json();

        if (data.success) {
            alert('Thank you for your order , your order will be delivered shortly');

            while (cartitems.hasChildNodes()) {
                cartitems.removeChild(cartitems.firstChild);
            }
            updatecarttotal();
        } else {
            alert('Order error: ' + (data.error || 'Unknown error'));
        }
    } catch (err) {
        console.error('Fetch failed:', err);
        alert('Network error, please try again.');
    }
}
