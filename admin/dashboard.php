<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/dashboard.css">
</head>



<!-- message toast  -->
<div class="toast-container position-fixed top-0 end-0 p-3">

    <div id="myToast" class="toast" role="alert">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <small>just now </small>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>

        <div class="toast-body" id="toastMessage">
            Hello! This is a notification.
        </div>
    </div>

</div>



<body class=" px-5 py-1 ">

 <div class="container-fluid">

        <div class="dashboard-header">
            <h2>Payment Management</h2>
            <p>Manage customer payments and subscriptions</p>
        </div>

      <div class=" mb-4 payment-filters">

    <button class="btn btn-primary active" data-filter="all">
        All Payments
    </button>

    <button class="btn btn-outline-primary" data-filter="lumpsum">
        Lumpsum
    </button>

    <button class="btn btn-outline-primary" data-filter="installment">
        Installments
    </button>

      </div>

      <!-- message box   -->
        <div id="actionMessage" class="alert d-none" role="alert"></div>

        <!-- payment records  -->
<div id="paymentRecords">

  <p class="text-center text-muted">Loading payments...</p>

  



</div>


    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="js/dashboard.js"></script>

</body>

</html>