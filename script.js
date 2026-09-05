const menuButton = document.querySelector('.menu-toggle');
const navigationMenu = document.querySelector('.nav-menu');

if (menuButton && navigationMenu) {
    menuButton.addEventListener('click', function () {
        const isOpen = navigationMenu.classList.toggle('is-open');
        menuButton.classList.toggle('is-open', isOpen);
        menuButton.setAttribute('aria-expanded', String(isOpen));
    });

    navigationMenu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            navigationMenu.classList.remove('is-open');
            menuButton.classList.remove('is-open');
            menuButton.setAttribute('aria-expanded', 'false');
        });
    });
}

// =====================================================
// Product Quantity Selector
// =====================================================

const quantityInput = document.querySelector(
    ".quantity-selector input"
);

const decreaseButton = document.querySelector(
    ".quantity-minus"
);

const increaseButton = document.querySelector(
    ".quantity-plus"
);

if (quantityInput && decreaseButton && increaseButton) {

    decreaseButton.addEventListener("click", function () {
        const currentQuantity = parseInt(
            quantityInput.value,
            10
        );

        const minimumQuantity = parseInt(
            quantityInput.min,
            10
        ) || 1;

        if (currentQuantity > minimumQuantity) {
            quantityInput.value = currentQuantity - 1;
        }
    });


    increaseButton.addEventListener("click", function () {
        const currentQuantity = parseInt(
            quantityInput.value,
            10
        );

        const maximumQuantity = parseInt(
            quantityInput.max,
            10
        );

        if (
            !maximumQuantity ||
            currentQuantity < maximumQuantity
        ) {
            quantityInput.value = currentQuantity + 1;
        }
    });


    quantityInput.addEventListener("change", function () {
        const minimumQuantity = parseInt(
            quantityInput.min,
            10
        ) || 1;

        const maximumQuantity = parseInt(
            quantityInput.max,
            10
        );

        let enteredQuantity = parseInt(
            quantityInput.value,
            10
        );

        if (
            isNaN(enteredQuantity) ||
            enteredQuantity < minimumQuantity
        ) {
            enteredQuantity = minimumQuantity;
        }

        if (
            maximumQuantity &&
            enteredQuantity > maximumQuantity
        ) {
            enteredQuantity = maximumQuantity;
        }

        quantityInput.value = enteredQuantity;
    });

}

// =====================================================
// Password Show and Hide Buttons
// =====================================================

const passwordButtons = document.querySelectorAll(
    ".password-toggle"
);

passwordButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        const targetId = button.dataset.passwordTarget;

        const passwordInput =
            document.getElementById(targetId);

        if (!passwordInput) {
            return;
        }

        const passwordIsHidden =
            passwordInput.type === "password";

        passwordInput.type = passwordIsHidden
            ? "text"
            : "password";

        button.textContent = passwordIsHidden
            ? "Hide"
            : "Show";

        button.setAttribute(
            "aria-label",
            passwordIsHidden
                ? "Hide password"
                : "Show password"
        );

    });

});

// =====================================================
// Checkout Fulfilment Interaction
// =====================================================

const fulfilmentOptions = document.querySelectorAll(
    'input[name="fulfilment_method"]'
);

const deliveryAddressField = document.getElementById(
    "delivery-address-field"
);

const deliveryAddressInput = document.getElementById(
    "delivery-address"
);

const deliveryFeeDisplay = document.getElementById(
    "delivery-fee-display"
);

const checkoutTotalDisplay = document.getElementById(
    "checkout-total-display"
);


function updateCheckoutFulfilment() {

    const selectedFulfilment = document.querySelector(
        'input[name="fulfilment_method"]:checked'
    );

    if (!selectedFulfilment) {
        return;
    }

    const isDelivery =
        selectedFulfilment.value === "Delivery";

    const deliveryFee = isDelivery ? 12 : 0;


    // Show or hide the delivery address
    if (deliveryAddressField) {
        deliveryAddressField.classList.toggle(
            "is-hidden",
            !isDelivery
        );
    }


    // Require an address only for delivery
    if (deliveryAddressInput) {
        deliveryAddressInput.required = isDelivery;
    }


    // Update displayed delivery fee
    if (deliveryFeeDisplay) {
        deliveryFeeDisplay.textContent =
            "RM " + deliveryFee.toFixed(2);
    }


    // Update displayed grand total
    if (checkoutTotalDisplay) {

        const subtotal = parseFloat(
            checkoutTotalDisplay.dataset.subtotal
        ) || 0;

        const newTotal = subtotal + deliveryFee;

        checkoutTotalDisplay.textContent =
            "RM " + newTotal.toFixed(2);
    }

}


// Listen for Pickup or Delivery selection
fulfilmentOptions.forEach(function (option) {

    option.addEventListener(
        "change",
        updateCheckoutFulfilment
    );

});


// Set the correct appearance when the page loads
updateCheckoutFulfilment();