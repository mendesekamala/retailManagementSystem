<link rel="stylesheet" href="css/sidebar.css">
<link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

<div class="sidebar">

    <!-- Add close button at the top of sidebar -->
    <div class="sidebar-close" id="sidebarClose">
        <i class='bx bx-x'></i>
    </div>

    <div class="logo-details">
        <a href=""> <i class='bx bx-store'></i> </a>
        <span class="logo_name">Chiades Co</span>
    </div>

    <ul>
        <li><a href="create.php"><i class="bx bx-plus"></i> Create Product</a></li>
        
        <li class="dropdown"><a href="#"><i class="bx bx-list-ul"></i> Orders <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="sell.php"> <i class="bx bx-plus"></i> create-oder</a></li>
                <li class="drops"><a href="orders.php"> <i class="bx bx-show"></i> view-orders</a></li>
            </ul>
        </li>

        <!-- Add the stock dropdown -->
        <li class="dropdown"><a href="#"><i class="bx bx-archive"></i> Stock <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="add-stock.php"> <i class="bx bx-plus"></i> add Stock</a></li>
                <li class="drops"><a href="products.php"> <i class="bx bx-show"></i> view Stock</a></li>
            </ul>
        </li>

        <li class="dropdown"><a href="#"><i class="bx bx-money"></i> Transactions <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="create-transaction.php"> <i class="bx bx-plus"></i> make a transaction</a></li>
                <li class="drops"><a href="view-transactions.php"> <i class="bx bx-show"></i> view transactions</a></li>
            </ul>
        </li>

        <li class="dropdown"><a href="#"><i class="bx bx-error"></i> Incident <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="incident.php"> <i class="bx bx-plus"></i> report an incident</a></li>
                <li class="drops"><a href="products.php"> <i class="bx bx-show"></i> view incidents</a></li>
            </ul>
        </li>

        <li class="dropdown"><a href="#"><i class="bx bx-money"></i> debt payments <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="debtors.php"> <i class="bx bx-user-voice"></i> debtors</a></li>
                <li class="drops"><a href="creditors.php"> <i class="bx bx-building-house"></i> creditors</a></li>
            </ul>
        </li>

        <li class="dropdown"><a href="#"><i class="bx bx-user"></i> users <i class="bx bx-chevron-right" id="d-arrow"></i></a>
            <ul>
                <li class="drops"><a href="user.php"> <i class="bx bx-user-circle"></i>my profile</a></li>
                <li class="drops"><a href="owner-users.php"> <i class="bx bx-list-ul"></i> manage users</a></li>
            </ul>
        </li>
    </ul>
</div>

<!-- Add the JavaScript for dropdown functionality -->
<script>
    var dropdowns = document.getElementsByClassName("dropdown");

    // Add event listener to each dropdown
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener("click", function() {
            // Close all other dropdowns
            for (var j = 0; j < dropdowns.length; j++) {
                if (dropdowns[j] !== this) {
                    dropdowns[j].classList.remove("clicked");
                    dropdowns[j].querySelector("ul").style.maxHeight = null;
                }
            }

            // Toggle the clicked dropdown
            this.classList.toggle("clicked");
            var panel = this.querySelector("ul");
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    }
</script>
