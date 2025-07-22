<?php
require_once 'db.php';
require_once 'config.php'; // already starts session

// auth.php now assumes session is started already, so no need to call session_start() again
require_once 'auth.php';

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get contact form submissions for dashboard
$contacts_count = 0;
$sql = "SELECT COUNT(*) as count FROM contact";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contacts_count = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="icon" type="image/x-icon" href="assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            /* --- Primary Theme Color --- */
            --primary: #1f83b4;         /* Your main theme color */
            --primary-blue-dark: rgb(4, 34, 63);         /* Your main theme color */
            --primary-rgb: 177, 44, 13; /* RGB version for opacity functions */
            --primary-dark: rgb(4, 34, 63);    /* A darker shade for hover/borders */
            --primary-light:rgb(31 131 180);   /* A lighter shade (optional use) */

            /* --- Standard Semantic Colors (Using Bootstrap 5 defaults for contrast) --- */
            --secondary: #6c757d;       /* Muted gray for secondary actions/text */
            --success: #198754;         /* Standard green for success messages */
            --info: #0dcaf0;            /* Standard cyan for informational alerts */
            --warning: #ffc107;         /* Standard yellow for warnings */
            --danger: #dc3545;          /* Standard red for errors (distinct from primary) */

            /* --- Light Theme Structure Colors (Using Bootstrap 5 defaults) --- */
            --light: #f8f9fa;           /* Light background shade */
            --dark: #212529;            /* Dark text color */
            --bs-body-bg: #f8f9fa;      /* Main page background */
            --bs-body-color: #212529;   /* Main text color */
            --component-bg: #ffffff;    /* Background for cards, modals, sidebar */
            --border-color: #dee2e6;    /* Standard border color */
            --text-muted: #6c757d;      /* Color for muted text */

            /* --- UI Interaction Colors --- */
            --hover-bg: rgba(0, 0, 0, 0.04);    /* Subtle dark overlay for hover on light elements */
            --shadow-color: rgba(0, 0, 0, 0.1); /* Standard shadow color for light theme */
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
        }
        button.btn{
            display: flex;
            align-items:center;
            gap: 3px;
        }
        .sidebar {
            background: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            z-index: 1;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #0a0a0ae8;
            color: white;
        }
        .sidebar-header .logo-holder{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 12px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        
        .sidebar-menu li:hover {
            background: rgba(0,0,0,0.03);
        }
        
        .sidebar-menu li.active {
            background: var(--primary-light);
            color: white;
        }
        
        .sidebar-menu li a {
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu li.active a {
            color: white;
        }
        
        .sidebar-menu li a .material-icons {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 500;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 8px 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-outline-danger {
            padding: 8px 16px;
            font-weight: 500;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 100;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .floating-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            min-width: 300px;
        }
        
        /* Dashboard stats cards */
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            margin-bottom: 20px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .stat-card .stat-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-blue-dark));
        }
        
        .stat-card-success {
            background: linear-gradient(135deg, var(--success), #0f6848);
        }
        
        .stat-card-info {
            background: linear-gradient(135deg, var(--info), #0a98c0);
        }
        .delete_button:hover{
            background-color: #dc3545;
            color: white!important;
            border-color: #dc3545;
        }
        .floating-menu-btn {
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex; /* Changed to flex for centering icon */
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        z-index: 1100; /* Above sidebar */
        cursor: pointer;
        transition: all 0.3s;
    }

    .floating-menu-btn:hover {
        transform: scale(1.05);
        background: var(--primary-dark);
    }

    .sidebar.hidden {
        transform: translateX(-250px);
    }

    .main-content.expanded {
        margin-left: 0;
    }


    /* Media queries for responsiveness */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-250px); /* Initially hidden on small screens */
        }
        .sidebar.show { /* Added a .show class to reveal the sidebar */
            transform: translateX(0);
            width: 137px;
        }
        .sidebar .logo-holder img{
            width: 100px !important;
        }
        .main-content {
            margin-left: 0; /* Main content always takes full width on small screens */
            padding: 0px;
            padding-top: 30px;
        }
        .floating-menu-btn {
            display: flex; /* Show the button on small screens */
        }
        .main-content.short {
            margin-left: 133px;
        }
    }

    @media (min-width: 769px) {
        .floating-menu-btn {
            display: none; /* Hide the button on larger screens by default */
        }
    }
    </style>
</head>
<body>
    <div class="floating-menu-btn" id="menuToggle">
        <span class="material-icons" id="menuIcon">menu</span>
    </div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-holder">
                <img src="images/logo.png" alt="Logo" style="width: 200px; height: auto;">
            </div>
            <small style="margin-top: 20px; display: block;" class="text-white-50">Admin panel</small>
        </div>
        <ul class="sidebar-menu">
            <li class="active"> <a href="admin.php">
                    <span class="material-icons">contact_mail</span>
                    Contacts
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <span class="material-icons">logout</span>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Contact Messages</h2>
                </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card stat-card-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value"><?php echo $contacts_count; ?></div>
                                <div class="stat-label">Total Contacts</div>
                            </div>
                            <i class="material-icons">contacts</i>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Contact Messages</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_contacts = "SELECT id, name, phone, email, message FROM contact ORDER BY id DESC";
                                $result_contacts = $conn->query($sql_contacts);

                                if ($result_contacts->num_rows > 0) {
                                    $row_number = 1; // Initialize a counter for row numbers
                                    while ($row_contact = $result_contacts->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td>' . $row_number . '</td>'; // Print the sequential row number
                                        echo '<td>' . htmlspecialchars($row_contact['name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row_contact['phone']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row_contact['email']) . '</td>';
                                        echo '<td>' . htmlspecialchars(substr($row_contact['message'], 0, 50)) . (strlen($row_contact['message']) > 50 ? '...' : '') . '</td>';
                                        echo '<td class="d-flex justify-center">';
                                        echo '  <button class="btn btn-sm btn-primary action-btn me-2" onclick="editContact(' . $row_contact['id'] . ')">';
                                        echo '    <span class="material-icons">edit</span>';
                                        echo '  </button>';
                                        echo '  <button class="btn btn-sm btn-danger action-btn" onclick="deleteContact(' . $row_contact['id'] . ')">';
                                        echo '    <span class="material-icons">delete</span>';
                                        echo '  </button>';
                                        echo '</td>';
                                        echo '</tr>';
                                        $row_number++; // Increment the counter for the next row
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No contact messages found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editContactForm" action="update_contact.php" method="POST">
                            <input type="hidden" id="editContactId" name="id">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="editContactName" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="editContactName" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editContactPhone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="editContactPhone" name="phone">
                                </div>
                                <div class="mb-3">
                                    <label for="editContactEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editContactEmail" name="email">
                                </div>
                                <div class="mb-3">
                                    <label for="editContactMessage" class="form-label">Message</label>
                                    <textarea class="form-control" id="editContactMessage" name="message" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Contact</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="alert-container">
        <?php
        if (isset($_SESSION['message'])) {
            $alertType = $_SESSION['message_type'] ?? 'success';
            echo '<div class="alert alert-'.$alertType.' alert-dismissible fade show" role="alert" id="autoCloseAlert">';
            echo $_SESSION['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            // Add JavaScript to auto-close the alert after 3 seconds
            echo '<script>
                setTimeout(function() {
                    var alert = document.getElementById("autoCloseAlert");
                    if (alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 3000);
            </script>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
    </div>
    <script>
    // Contact functions
    function editContact(id) {
        $.ajax({
            url: 'get_contact.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editContactId').val(response.data.id);
                    $('#editContactName').val(response.data.name);
                    $('#editContactPhone').val(response.data.phone);
                    $('#editContactEmail').val(response.data.email);
                    $('#editContactMessage').val(response.data.message);
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editContactModal'));
                    editModal.show();
                } else {
                    alert('Error loading contact: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading contact: ' + error);
            }
        });
    }

    function deleteContact(id) {
        if (confirm('Are you sure you want to delete this contact?')) {
            $.ajax({
                url: 'delete_contact.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Error deleting contact: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error deleting contact: ' + error);
                }
            });
        }
    }
    </script>   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menuIcon');
            const mainContent = document.getElementById('mainContent');
            

            // Initial state for larger screens: sidebar visible, button hidden
            if (window.innerWidth > 768) {
                sidebar.classList.remove('hidden');
                mainContent.classList.remove('expanded');
                menuToggle.style.display = 'none'; // Hide button on large screens initially
            } else {
                // For smaller screens, sidebar starts hidden
                sidebar.classList.add('hidden');
                mainContent.classList.add('expanded');
                menuIcon.textContent = 'menu'; // Ensure correct icon on load
                menuToggle.style.display = 'flex'; // Show button on small screens
            }

            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('short');
                mainContent.classList.toggle('expanded');
                if (sidebar.classList.contains('show')) {
                    menuIcon.textContent = 'close'; // Hamburger icon
                    //  mainContent.classList.add('short');
                } else {
                    menuIcon.textContent = 'menu'; // Close icon
                    // mainContent.classList.remove('short');
                }
            });

            // Adjust sidebar and main content on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('expanded');
                   
                    menuToggle.style.display = 'none'; // Hide button on large screens
                    menuIcon.textContent = 'menu'; // Reset icon when sidebar is forced open
                } else {
                    // If resizing to small screen, ensure sidebar is hidden and button shown
                    sidebar.classList.add('hidden');
                    
                    mainContent.classList.add('expanded');
                    menuToggle.style.display = 'flex'; // Show button on small screens
                    menuIcon.textContent = 'menu'; // Ensure hamburger icon
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </body>
</html>