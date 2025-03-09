    

            function updateQuantity(itemId, delta) {
                const quantityElement = document.getElementById(`quantity-${itemId}`);
                let quantity = parseInt(quantityElement.textContent) + delta;

                if (quantity < 1) return;

                quantityElement.textContent = quantity;
                document.getElementById(`input-quantity-${itemId}`).value = quantity;

                const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
                const newSubtotal = quantity * sellingPrice;
                document.getElementById(`subtotal-${itemId}`).textContent = ` => ${newSubtotal.toFixed(2)}`;

                grandTotal += delta * sellingPrice;
                document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
            }
_
            function removeItem(itemId) {
                document.getElementById(`item-${itemId}`).style.display = 'none';
                document.getElementById(`input-removed-${itemId}`).value = 1;

                const quantity = parseInt(document.getElementById(`input-quantity-${itemId}`).value);
                const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
                const subtotal = quantity * sellingPrice;

                grandTotal -= subtotal;
                document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
            }

            function updateStatusText() {
                const status = document.getElementById('orderStatus').value;
                const statusText = document.getElementById('statusText');
                const statusIcon = document.querySelector('.status-icon i');

                statusText.className = 'status-text text-' + status;
                statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);

                switch (status) {
                    case 'created':
                        statusIcon.className = 'bx bx-plus-circle icon-created';
                        break;
                    case 'sent':
                        statusIcon.className = 'bx bx-right-arrow-circle icon-sent';
                        break;
                    case 'delivered':
                        statusIcon.className = 'bx bx-check-circle icon-delivered';
                        break;
                    case 'cancelled':
                        statusIcon.className = 'bx bx-x-circle icon-cancelled';
                        break;
                }
            }







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



            //pop up modal scripts
            window.openModal = function () {

                const modal = document.getElementById("popup-modal");

                modal.style.display = "flex";

                // Fetch payment methods
                fetchPaymentMethods();

                // Update the Grand Total in the modal from the total-sum in the order list
                const totalSum = document.getElementById("grandTotal").textContent;
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