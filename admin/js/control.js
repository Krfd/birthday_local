$(document).ready(function () {
  // DISABLE
  $(".disable").click(function (e) {
    var user_id = $(this).attr("data-id");

    Swal.fire({
      title: "Disable this user?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Yes, disable",
      confirmButtonColor: "#dc3545",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "GET",
          url: "functions/disable.php",
          data: {
            id: user_id,
          },
          success: function (res) {
            switch (res) {
              case "disabled":
                Swal.fire({
                  icon: "success",
                  title: "User has been disabled",
                  iconColor: "#28a745",
                  confirmButtonColor: "#45eb45",
                  confirmButtonText: "OKAY",
                }).then(() => {
                  location.reload();
                });
                break;
              case "error":
                Swal.fire({
                  icon: "error",
                  title: "Invalid action",
                  text: "Please try again",
                  iconColor: "#dc3545",
                  confirmButtonText: "OKAY",
                }).then(() => {
                  location.reload();
                });
                break;
              default:
                Swal.fire({
                  icon: "error",
                  title: "Something went wrong",
                  iconColor: "#dc3545",
                }).then(() => {
                  location.reload();
                });
            }
          },
        });
      }
    });
  });

  // ENABLE
  $(".enable").click(function (e) {
    var user_id = $(this).attr("data-id");

    Swal.fire({
      title: "Enable this user?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Yes, enable",
      confirmButtonColor: "#45eb45",
      cancelButtonText: "Cancel",
      cancelButtonColor: "#dc3545",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "GET",
          url: "functions/enable.php",
          data: {
            id: user_id,
          },
          success: function (res) {
            switch (res) {
              case "enabled":
                Swal.fire({
                  icon: "success",
                  title: "User has been enabled",
                  iconColor: "#28a745",
                  confirmButtonColor: "#45eb45",
                  confirmButtonText: "OKAY",
                }).then(() => {
                  location.reload();
                });
                break;
              case "error":
                Swal.fire({
                  icon: "error",
                  title: "Invalid action",
                  text: "Please try again",
                  iconColor: "#dc3545",
                  confirmButtonText: "OKAY",
                }).then(() => {
                  location.reload();
                });
                break;
              default:
                Swal.fire({
                  icon: "error",
                  title: "Something went wrong",
                  iconColor: "#dc3545",
                }).then(() => {
                  location.reload();
                });
            }
          },
        });
      }
    });
  });
});
