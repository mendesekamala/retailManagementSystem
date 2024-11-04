<!-- incident.php -->
<?php
session_start();

include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Incident</title>
    <link rel="stylesheet" href="css/incident.css">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="incident-container">
        <div class="left-section">
            <form id="incident-form">
                <div class="section">
                    <label for="search-product">Search Product</label>
                    <input type="text" id="search-product" name="search-product" placeholder="Search product...">
                    <div id="suggestions" class="suggestions"></div>
                </div>

                <div class="section">
                    <p>Destroy in Whole Quantities</p>
                    <hr>
                    <div class="row">
                        <input type="text" id="whole-product-name" placeholder="Product Name" readonly>
                        <input type="text" id="whole-quantified-as" placeholder="Quantified As" readonly>
                    </div>
                    <div class="row">
                        <input type="number" id="whole-quantity" placeholder="Quantity Destroyed">
                    </div>
                </div>

                <div class="section">
                    <p>Destroy in Units</p>
                    <hr>
                    <div class="row">
                        <select id="unit-destroy-as">
                            <option value="" disabled selected>Select Unit</option>
                        </select>
                        <input type="text" id="unit-relation" placeholder="Relation to One" readonly>
                    </div>
                    <div class="row">
                        <input type="number" id="unit-quantity" placeholder="Unit Quantities Destroyed">
                    </div>
                </div>

                <div class="row buttons">
                    <button type="button" id="add-product">Add Incident</button>
                    <button type="reset">Reset</button>
                </div>
            </form>
        </div>

        <div class="right-section">
            <table id="incident-list">
                <thead>
                    <tr>
                        <th>X</th>
                        <th>S/N</th>
                        <th>Product Name</th>
                        <th>Qnt</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Incident list will be populated dynamically -->
                </tbody>
            </table>
            <button id="report-incident">Report Incident</button>
        </div>
    </div>

    <script src="scripts/incident.js"></script>
</body>
</html>
