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
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/incident.css">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Incident Reporting</h1>
        </header>

        <div class="incident-content">
            <div class="card incident-form-card">
                <form id="incident-form">
                    <div class="form-section">
                        <h3>Product Information</h3>
                        <div class="form-group">
                            <label for="search-product">Search Product</label>
                            <div class="search-container">
                                <input type="text" id="search-product" name="search-product" placeholder="Enter product name">
                                <div id="suggestions" class="suggestions-dropdown"></div>
                            </div>
                        </div>

                        <div class="form-section-inner whole-section">
                            <h4>Destroy in Whole Quantities</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="whole-product-name">Product Name</label>
                                    <input type="text" id="whole-product-name" placeholder="Product Name" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="whole-quantified-as">Quantified As</label>
                                    <input type="text" id="whole-quantified-as" placeholder="Quantified As" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="whole-quantity">Quantity Destroyed</label>
                                    <input type="number" id="whole-quantity" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="toggle-section">
                            <div class="toggle-btn" id="switch-units">
                                <span>Destroy in Units</span>
                                <i class='bx bx-chevron-down'></i>
                            </div>
                        </div>

                        <div class="form-section-inner units-section" id="units-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit-destroy-as">Unit Type</label>
                                    <select id="unit-destroy-as">
                                        <option value="" disabled selected>Select Unit</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="unit-relation">Relation to Whole</label>
                                    <input type="text" id="unit-relation" placeholder="Relation to One" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit-quantity">Unit Quantities Destroyed</label>
                                    <input type="number" id="unit-quantity" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="add-product" class="btn-primary">
                                <i class='bx bx-plus'></i> Add Incident
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class='bx bx-reset'></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card incident-summary-card">
                <div class="form-section">
                    <h3>Incident Summary</h3>
                </div>

                <div class="incident-table-container">
                    <table id="incident-list">
                        <thead>
                            <tr>
                                <th>X</th>
                                <th>S/N</th>
                                <th>Product Name</th>
                                <th>Qty Destroyed</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Incident list will be populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <button id="report-incident" class="btn-primary complete-order-btn">
                    <i class='bx bx-error-circle'></i> Report Incident
                </button>
            </div>
        </div>
    </div>

    <script src="scripts/incident.js"></script>
</body>
</html>