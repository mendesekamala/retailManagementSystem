// incident.js
document.getElementById('search-product').addEventListener('keyup', function() {
    const query = this.value;

    if (query.length > 2) {
        fetch('search-product.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                const suggestions = document.getElementById('suggestions');
                suggestions.innerHTML = '';
                data.forEach(product => {
                    const div = document.createElement('div');
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
    document.getElementById('whole-product-name').value = product.name;
    document.getElementById('whole-product-name').dataset.productId = product.product_id;
    document.getElementById('whole-quantified-as').value = product.quantified;

    fetch('fetch-units.php?product_id=' + product.product_id)
        .then(response => response.json())
        .then(data => {
            const unitSelect = document.getElementById('unit-destroy-as');
            unitSelect.innerHTML = '<option disabled selected>Select Unit</option>';
            data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.unit_id;
                option.textContent = unit.name;
                option.dataset.perSingleQuantity = unit.per_single_quantity;
                unitSelect.appendChild(option);
            });
        });
}

let incidentList = [];

document.getElementById('add-product').addEventListener('click', function() {
    const productId = document.getElementById('whole-product-name').dataset.productId;
    const wholeProductName = document.getElementById('whole-product-name').value;
    const wholeQuantity = document.getElementById('whole-quantity').value;

    const unitId = document.getElementById('unit-destroy-as').value;
    const unitQuantity = document.getElementById('unit-quantity').value;

    if (wholeQuantity || unitQuantity) {
        let incident = { product_id: productId, name: wholeProductName };
        
        if (wholeQuantity) {
            incident.quantity_destroyed = wholeQuantity;
            incident.unit_type = 'whole';
        } else if (unitQuantity) {
            incident.unit_id = unitId;
            incident.units_destroyed = unitQuantity;
            incident.unit_type = 'unit';
        }
        
        incidentList.push(incident);
        updateIncidentList();
    } else {
        alert('Please provide quantity destroyed.');
    }
});

function updateIncidentList() {
    let tableBody = document.getElementById('incident-list').getElementsByTagName('tbody')[0];
    tableBody.innerHTML = '';
    incidentList.forEach((item, index) => {
        let row = `<tr>
                    <td><button onclick="removeIncident(${index})">X</button></td>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.quantity_destroyed || item.units_destroyed}</td>
                   </tr>`;
        tableBody.innerHTML += row;
    });
}

function removeIncident(index) {
    incidentList.splice(index, 1);
    updateIncidentList();
}

document.getElementById('report-incident').addEventListener('click', function() {
    if (incidentList.length === 0) {
        alert('No items in the incident list');
        return;
    }

    fetch('record-incident.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ incidentList: incidentList })
    })
    .then(response => response.text())
    .then(result => {
        alert(result);
        incidentList = [];
        updateIncidentList();
        document.getElementById('incident-form').reset();
    });
});
