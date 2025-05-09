// incident.js
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('search-product');
    const suggestionsContainer = document.getElementById('suggestions');
    const switchUnitsBtn = document.getElementById('switch-units');
    const unitsSection = document.getElementById('units-section');
    const wholeSection = document.querySelector('.whole-section');
    const reportBtn = document.getElementById('report-incident');

    // Hide units section initially
    unitsSection.style.display = 'none';

    let incidentList = [];

    setupEventListeners();

    function setupEventListeners() {
        // Product search
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length > 2) {
                fetchProducts(query);
            } else {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });

        // Close suggestions on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.search-container')) {
                suggestionsContainer.style.display = 'none';
            }
        });

        // Toggle units section
        if (switchUnitsBtn) {
            switchUnitsBtn.addEventListener('click', toggleSellingMode);
        }

        // Add product to list
        document.getElementById('add-product').addEventListener('click', addProduct);

        // Prevent negative values
        document.getElementById('whole-quantity').addEventListener('input', validateQuantity);
        document.getElementById('unit-quantity').addEventListener('input', validateQuantity);

        // Submit report
        reportBtn.addEventListener('click', submitIncidentReport);
    }

    function fetchProducts(query) {
        fetch('search-product.php?query=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                suggestionsContainer.innerHTML = '';
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                if (data.length > 0) {
                    data.forEach(product => {
                        const div = document.createElement('div');
                        div.classList.add('suggestion-item');
                        div.textContent = product.name;
                        div.addEventListener('click', function () {
                            fillProductDetails(product);
                            suggestionsContainer.style.display = 'none';
                        });
                        suggestionsContainer.appendChild(div);
                    });
                    suggestionsContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                suggestionsContainer.style.display = 'none';
            });
    }

    function fillProductDetails(product) {
        document.getElementById('whole-product-name').value = product.name;
        document.getElementById('whole-product-name').dataset.productId = product.product_id;
        document.getElementById('whole-quantified-as').value = product.quantified;
        document.getElementById('search-product').value = product.name;

        // Fetch and fill unit dropdown
        fetch('fetch-units.php?product_id=' + product.product_id)
            .then(response => response.json())
            .then(data => {
                const unitSelect = document.getElementById('unit-destroy-as');
                unitSelect.innerHTML = '<option value="" disabled selected>Select Unit</option>';
                if (data.length > 0) {
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.unit_id;
                        option.textContent = unit.name;
                        option.dataset.perSingleQuantity = unit.per_single_quantity;
                        unitSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching units:', error);
            });
    }

    function toggleSellingMode() {
        const chevronIcon = switchUnitsBtn.querySelector('i');
        if (unitsSection.style.display === 'none') {
            unitsSection.style.display = 'block';
            wholeSection.classList.add('disabled-input');
            chevronIcon.style.transform = 'rotate(180deg)';
        } else {
            unitsSection.style.display = 'none';
            wholeSection.classList.remove('disabled-input');
            chevronIcon.style.transform = 'rotate(0deg)';
        }
    }

    function validateQuantity() {
        if (this.value < 0) this.value = 0;
    }

    function addProduct() {
        const productId = document.getElementById('whole-product-name').dataset.productId;
        const wholeProductName = document.getElementById('whole-product-name').value;
        const wholeQuantity = parseFloat(document.getElementById('whole-quantity').value) || 0;
        const unitId = document.getElementById('unit-destroy-as').value;
        const unitQuantity = parseFloat(document.getElementById('unit-quantity').value) || 0;

        if (!productId) {
            alert('Please select a product first.');
            return;
        }

        if (wholeQuantity === 0 && unitQuantity === 0) {
            alert('Please enter a quantity to destroy.');
            return;
        }

        let incident = {
            product_id: productId,
            name: wholeProductName
        };

        if (wholeQuantity) {
            incident.quantity_destroyed = wholeQuantity;
            incident.unit_type = 'whole';
        } else {
            const selectedOption = document.getElementById('unit-destroy-as').selectedOptions[0];
            incident.unit_id = unitId;
            incident.units_destroyed = unitQuantity;
            incident.unit_type = 'unit';
            incident.unit_name = selectedOption.text;
        }

        incidentList.push(incident);
        updateIncidentList();
    }

    function updateIncidentList() {
        const tableBody = document.getElementById('incident-list').getElementsByTagName('tbody')[0];
        tableBody.innerHTML = '';
        incidentList.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><button onclick="removeIncident(${index})">X</button></td>
                <td>${index + 1}</td>
                <td>${item.name}${item.unit_name ? ` (${item.unit_name})` : ''}</td>
                <td>${item.quantity_destroyed || item.units_destroyed}</td>
                <td>${item.unit_type}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    window.removeIncident = function(index) {
        incidentList.splice(index, 1);
        updateIncidentList();
    }

    function submitIncidentReport() {
        if (incidentList.length === 0) {
            alert('No items in the incident list');
            return;
        }

        reportBtn.disabled = true;
        reportBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Reporting...';

        fetch('record-incident.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ incidentList: incidentList })
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert(result.message);
                incidentList = [];
                updateIncidentList();
                document.getElementById('incident-form').reset();
            } else {
                alert('Failed to report incident: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to report incident. Please try again.');
        })
        .finally(() => {
            reportBtn.disabled = false;
            reportBtn.innerHTML = '<i class="bx bx-error-circle"></i> Report Incident';
        });
    }
});
