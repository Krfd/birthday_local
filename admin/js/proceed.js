// EXCLUDE CUSTOMER
$(".proceed").click(function (e) {
  e.preventDefault();

  var inv = $(this).attr("data-inv");
  var branch = $(this).attr("data-branch");

  Swal.fire({
    title: "Proceed this user to entries?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Proceed",
    confirmButtonColor: "#45eb45",
    cancelButtonText: "Back",
    cancelButtonColor: "#dc3545",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        type: "GET",
        url: "functions/entries.php",
        data: {
          inv: inv,
          branch: branch,
        },
        success: function (res) {
          switch (res) {
            case "proceed":
              Swal.fire({
                icon: "success",
                title: "Customer has been issued a ticket",
                iconColor: "#28a745",
                confirmButtonColor: "#45eb45",
                confirmButtonText: "Okay",
                allowOutsideClick: false,
              }).then(() => {
                location.reload();
              });
              break;
            default:
              Swal.fire({
                icon: "error",
                title: "Something went wrong",
                iconColor: "#dc3545",
                allowOutsideClick: false,
              });
          }
        },
      });
    }
  });
});
