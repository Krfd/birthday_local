$(document).ready(function () {
  $("#updatePasswordForm").submit(function (e) {
    e.preventDefault();
    var update = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "functions/update.php",
      data: update,
      success: function (res) {
        switch (res) {
          case "updated":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "Password has been updated",
              text: "Please wait...",
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false,
              allowOutsideClick: false,
              customClass: {
                popup: "small-alert",
                title: "small-title",
                content: "small-content",
              },
            }).then(() => {
              location.reload();
            });
            break;
          case "mismatch":
            Swal.fire({
              icon: "error",
              title: "Password does not match",
              text: "Please confirm your password and try again",
              confirmButtonText: "TRY AGAIN",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          case "invalid":
            Swal.fire({
              icon: "error",
              title: "Invalid updating password",
              text: "Please verify your password and try again.",
              confirmButtonText: "Try again",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          case "disabled":
            Swal.fire({
              icon: "error",
              title: "Unable to update",
              text: "Please try again later.",
              confirmButtonText: "OKAY",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          default:
            Swal.fire({
              icon: "error",
              title: res,
              confirmButtonText: "OKAY",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Something went wrong",
          text: "Please try again later.",
          confirmButtonText: "OKAY",
          confirmButtonColor: "#d33",
          allowOutsideClick: false,
        });
      },
    });
  });
});
