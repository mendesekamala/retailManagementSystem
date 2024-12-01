document.getElementById('search-product').addEventListener('keyup', function() {
    const query = this.value;

    if (query.length > 2) {
        // Send AJAX request to fetch products
        fetch('search-product.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                const suggestions = document.getElementById('suggestions');
                suggestions.innerHTML = ''; // Clear previous suggestions
                data.forEach(product => {
                    const div = document.createElement('div');
                    div.classList.add('suggestion-item');  // Add the correct class
                    div.textContent = product.name;

                    div.addEventListener('click', function() {
                        fillProductDetails(product);
                        suggestions.innerHTML = '';
                    });

                    suggestions.appendChild(div);
                });
            });
    }
});


function fillProductDetails(product) {
    const wholeProductNameInput = document.getElementById('whole-product-name');
    wholeProductNameInput.value = product.name;
    wholeProductNameInput.dataset.productId = product.product_id; // Store product_id

    document.getElementById('whole-quantified-as').value = product.quantified;
    document.getElementById('whole-buying-price').value = product.buying_price;
    document.getElementById('whole-selling-price').value = product.selling_price;

    // Fetch unit details
    fetch('fetch-units.php?product_id=' + product.product_id)
        .then(response => response.json())
        .then(data => {
            const unitSelect = document.getElementById('unit-sell-as');
            unitSelect.innerHTML = '<option disabled selected>Select Unit</option>';
            data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.unit_id;
                option.textContent = unit.name;
                option.dataset.perSingleQuantity = unit.per_single_quantity;
                option.dataset.buyingPrice = unit.buying_price;
                option.dataset.sellingPrice = unit.selling_price;
                unitSelect.appendChild(option);
            });
        });
}

document.getElementById('unit-sell-as').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('unit-relation').value = selectedOption.dataset.perSingleQuantity;
    document.getElementById('unit-buying-price').value = selectedOption.dataset.buyingPrice;
    document.getElementById('unit-selling-price').value = selectedOption.dataset.sellingPrice;
});


// Order list array to hold added items
let orderList = [];
let totalAmount = 0;
let totalProfit = 0;

document.getElementById('add-product').addEventListener('click', addProduct);

document.getElementById('add-product').addEventListener('click', addProduct);

document.getElementById('add-product').addEventListener('click', addProduct);

function addProduct() {
    const wholeProductName = document.getElementById('whole-product-name').value;
    const wholeQuantity = parseFloat(document.getElementById('whole-quantity').value) || 0;
    const wholeSellingPrice = parseFloat(document.getElementById('whole-selling-price').value) || 0;
    const wholeBuyingPrice = parseFloat(document.getElementById('whole-buying-price').value) || 0;
    const productId = document.getElementById('whole-product-name').dataset.productId;

    const unitSellAs = document.getElementById('unit-sell-as').value;
    const unitQuantity = parseFloat(document.getElementById('unit-quantity').value) || 0;
    const unitSellingPrice = parseFloat(document.getElementById('unit-selling-price').value) || 0;
    const unitBuyingPrice = parseFloat(document.getElementById('unit-buying-price').value) || 0;
    const unitId = document.getElementById('unit-sell-as').selectedOptions[0].value; // Get the selected unit_id

    if (!productId) {
        alert("Please select a product.");
        return;
    }

    // Fetch product details for validation
    fetch(`validate-product.php?product_id=${productId}&unit_id=${unitId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            const availableWholeQuantity = parseFloat(data.quantity);
            const availableUnitQuantity = parseFloat(data.available_units);

            // Validate whole quantity
            if (wholeQuantity > 0) {
                if (wholeQuantity > availableWholeQuantity) {
                    alert(`Input quantity to be sold is greater than the remaining quantity (${availableWholeQuantity}).`);
                    return;
                }

                const profit = (wholeSellingPrice - wholeBuyingPrice) * wholeQuantity;
                const productDetails = {
                    product_id: productId,
                    name: wholeProductName,
                    quantity: wholeQuantity,
                    price: wholeSellingPrice,
                    buying_price: wholeBuyingPrice,
                    sum: wholeQuantity * wholeSellingPrice,
                    profit: profit,
                    unit_type: 'whole',
                };

                orderList.push(productDetails);
            }

            // Validate unit quantity
            if (unitQuantity > 0) {
                if (unitQuantity > availableUnitQuantity) {
                    alert(`Unit quantity to be sold is greater than the remaining unit quantities (${availableUnitQuantity}).`);
                    return;
                }

                const unitRelation = document.getElementById('unit-relation').value;
                const selectedOption = document.getElementById('unit-sell-as').options[document.getElementById('unit-sell-as').selectedIndex];
                const unitName = selectedOption.text;

                const profit = (unitSellingPrice - unitBuyingPrice) * unitQuantity;
                const productDetails = {
                    product_id: productId,
                    name: `${wholeProductName} (${unitName})`,
                    quantity: unitQuantity,
                    price: unitSellingPrice,
                    buying_price: unitBuyingPrice,
                    sum: unitQuantity * unitSellingPrice,
                    profit: profit,
                    unit_type: 'unit',
                    unit_id: unitId,
                };

                orderList.push(productDetails);
            }

            if (wholeQuantity === 0 && unitQuantity === 0) {
                alert('Please provide quantity for either whole or unit.');
                return;
            }

            updateOrderList();
            resetOrderForm();
        })
        .catch(error => {
            console.error('Error validating product:', error);
            alert('Failed to validate product. Please try again.');
        });
}



function updateOrderList() {
    let orderTableBody = document.getElementById('order-list').getElementsByTagName('tbody')[0];
    orderTableBody.innerHTML = '';
    totalAmount = 0;
    totalProfit = 0;

    orderList.forEach((item, index) => {
        totalAmount += parseFloat(item.sum);
        totalProfit += parseFloat(item.profit);
        let row = `
            <tr>
                <td><button onclick="removeItem(${index})">X</button></td>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>@ ${item.price}</td>
                <td>${item.sum.toFixed(2)}</td>
            </tr>
        `;
        orderTableBody.innerHTML += row;
    });

    document.getElementById('total-sum').innerText = totalAmount.toFixed(2);
}

function removeItem(index) {
    orderList.splice(index, 1);
    updateOrderList();
}


function resetOrderForm() {
    document.getElementById('whole-product-name').value = '';
    document.getElementById('whole-quantified-as').value = '';
    document.getElementById('whole-quantity').value = '';
    document.getElementById('whole-buying-price').value = '';
    document.getElementById('whole-selling-price').value = '';
    document.getElementById('unit-sell-as').value = '';
    document.getElementById('unit-quantity').value = '';
    document.getElementById('unit-buying-price').value = '';
    document.getElementById('unit-selling-price').value = '';
    document.getElementById('unit-relation').value = '';
}




document.addEventListener("DOMContentLoaded", function () {
    // Fetch payment methods when modal opens
    function fetchPaymentMethods() {
        // Get company_id from session (embedded securely via PHP)
        const companyId = document.getElementById("session-company-id").value;
        console.log("Company ID passed to AJAX:", companyId);

        if (!companyId) {
            console.error("Company ID not found in session.");
            return;
        }

        // Make an AJAX request to fetch payment methods
        fetch("fetch_payment_methods.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ company_id: companyId }) // Pass company_id in the request body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePaymentSelects(data.methods); // Update select options with methods
                openFullPaymentSection(); // Open the full payment section by default
            } else {
                console.error("Error fetching payment methods:", data.error);
            }
        })
        .catch(error => console.error("AJAX error:", error));
    }

    // Update the payment method selects with the fetched options
    function updatePaymentSelects(methods) {
        const selects = document.querySelectorAll("#pay-via, #payment-one, #payment-two");

        selects.forEach(select => {
            select.innerHTML = ""; // Clear existing options
            methods.forEach(method => {
                const option = document.createElement("option");
                option.value = method.value; // Assuming methods contain an object with 'value' and 'label'
                option.textContent = method.label;
                select.appendChild(option);
            });
        });
    }

    // Open the modal
    window.openModal = function () {

            if (orderList.length === 0) {
                alert('No items in the order list');
                return;
            }

        const modal = document.getElementById("popup-modal");
        const suggestions = document.getElementById("suggestions"); // Get the suggestions div

        modal.style.display = "flex";
        suggestions.style.display = "none";  // This hides the suggestions div

        // Fetch payment methods
        fetchPaymentMethods();

        // Update the Grand Total in the modal from the total-sum in the order list
        const totalSum = document.getElementById("total-sum").textContent;
        const grandTotalElement = modal.querySelector(".grandtotal-section .amount");
        grandTotalElement.textContent = totalSum;  // Update Grand Total with the value from the table
        
        // Clear the "Double Payment" section inputs when opening the modal
        clearInputsAndSelects("double-payment"); // Ensure the double payment inputs are cleared
    };

    // Close the modal
    window.closeModal = function () {
        const modal = document.getElementById("popup-modal");
        modal.style.display = "none";
    };

    // Open the Full Payment Section by default after fetching data
    function openFullPaymentSection() {
        const fullPaymentSection = document.getElementById("full-payment");
        if (fullPaymentSection) {
            fullPaymentSection.style.display = "block";
            fullPaymentSection.style.opacity = 1;
            fullPaymentSection.style.maxHeight = "500px"; // Set a max-height for the slide animation
            clearInputsAndSelects('double-payment'); // Clear inputs in the full payment section
        }
    }

    // Function to clear inputs and selects in a section
    function clearInputsAndSelects(sectionId) {
        const section = document.getElementById(sectionId);
        
        // Clear text inputs
        const inputs = section.querySelectorAll('input[type="text"], input[type="number"]');
        inputs.forEach(input => input.value = '');

        // Reset select options to null or default
        const selects = section.querySelectorAll('select');
        selects.forEach(select => select.value = '');
    }

    // Section toggle functionality to close sections when one is opened
    window.toggleSection = function (sectionId) {
        const section = document.getElementById(sectionId);
        const arrow = section.previousElementSibling.querySelector("i");

        // Close any open sections that are not the one being clicked
        const openSections = document.querySelectorAll('.full-payment-section, .double-payment-section');
        openSections.forEach(openSection => {
            if (openSection !== section) {
                // Close the section
                closeSection(openSection);
            }
        });

        if (section.style.display === "none" || section.style.display === "") {
            // Open the clicked section
            section.style.display = "block";
            setTimeout(() => {
                section.style.opacity = 1;
                section.style.maxHeight = "500px"; // Animation
            }, 10);
            arrow.classList.remove("bx-chevron-down");
            arrow.classList.add("bx-chevron-up");
        } else {
            // Close the clicked section
            closeSection(section);
            arrow.classList.remove("bx-chevron-up");
            arrow.classList.add("bx-chevron-down");
        }
    };

    // Close a section and clear its inputs/selects
    function closeSection(section) {
        section.style.opacity = 0;
        section.style.maxHeight = "0"; // Animation
        setTimeout(() => {
            section.style.display = "none";
            clearInputsAndSelects(section.id); // Clear inputs in the closed section
        }, 300); // Wait for animation to finish before hiding
    }

    // Disable options in Payment Two select based on Payment One selection
    document.getElementById("payment-one").addEventListener("change", function () {
        const paymentOneValue = this.value;
        const paymentTwoSelect = document.getElementById("payment-two");

        for (let option of paymentTwoSelect.options) {
            if (option.value === paymentOneValue) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        }
    });

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === document.getElementById("popup-modal")) {
            closeModal();
        }
    };

        // Finish button event listener
        document.querySelector(".finish-btn").addEventListener("click", function() {
            // Fetch customer name, total, and payment method(s)
            const customerName = document.getElementById("customer-name").value;
            const total = parseFloat(document.getElementById("total-sum").textContent);
            const paymentMethod = document.getElementById("pay-via").value;
            const companyId = document.getElementById("session-company-id").value;
    
                // Validate customer name
                if (customerName === "") {
                    alert("Customer name cannot be empty. Please enter a name.");
                    return; // Stop further execution
                }

            let paymentData = {
                customer_name: customerName,
                orderList: orderList,
                total: total,
                total_profit: totalProfit,
                company_id: companyId
            };
    
            // Check if it's a double payment
            const isDoublePayment = document.getElementById("payment-one").value && document.getElementById("payment-two").value;
    
            if (isDoublePayment) {
                const paymentOneMethod = document.getElementById("payment-one").value;
                const paymentOneAmount = parseFloat(document.getElementById("amount-one").value);
                const paymentTwoMethod = document.getElementById("payment-two").value;
                const paymentTwoAmount = parseFloat(document.getElementById("amount-two").value);
    
                paymentData.payment_method = 'double';  // Set payment method as double
                paymentData.payment_one = {
                    method: paymentOneMethod,
                    amount: paymentOneAmount
                };
                paymentData.payment_two = {
                    method: paymentTwoMethod,
                    amount: paymentTwoAmount
                };
            } else {
                paymentData.payment_method = paymentMethod;
                paymentData.debt_amount = total;  // If full payment, debt amount is the total order amount
            }
    
            // Send data to complete-order.php via AJAX
            fetch("complete-order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(paymentData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Order completed successfully!");
                    closeModal(); // Close modal after successful order completion

                    window.location.reload();  // Reload the page (sell.php) after successful order completion
                } else {
                    alert("Error completing order: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while completing the order.");
            });
        });

    });



