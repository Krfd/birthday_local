$(document).ready(function () {
  $("#login").submit(function (e) {
    e.preventDefault();
    var loginData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "functions/login.php",
      data: loginData,
      success: function (res) {
        switch (res) {
          case "user":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "Logging in",
              text: "Please wait...",
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false,
              allowOutsideClick: false,
              willClose: () => {
                window.location = "./dashboard.php";
              },
              customClass: {
                popup: "small-alert",
                title: "small-title",
                content: "small-content",
              },
            });
            break;
          case "admin":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "Logging in",
              text: "Please wait...",
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false,
              allowOutsideClick: false,
              willClose: () => {
                window.location = "admin/dashboard.php";
              },
              customClass: {
                popup: "small-alert",
                title: "small-title",
                content: "small-content",
              },
            });
            break;
          case "ar":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "Logging in",
              text: "Please wait...",
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false,
              allowOutsideClick: false,
              willClose: () => {
                window.location = "./ar.php";
              },
              customClass: {
                popup: "small-alert",
                title: "small-title",
                content: "small-content",
              },
            });
            break;
          case "game":
            window.location = "game.php";
            break;
          case "controller":
            window.location = "control.php";
            break;
          case "invalid":
            Swal.fire({
              icon: "error",
              title: "Invalid user.",
              text: "Please verify the details and try again.",
              confirmButtonText: "Try again",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          case "banned":
            Swal.fire({
              icon: "error",
              title: "Account has been disabled.",
              text: "For more assistance, please contact the system's administrator.",
              confirmButtonText: "OKAY",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          case "unknown":
            Swal.fire({
              icon: "error",
              title: "No account has been found",
              text: "Please verify the details and try again.",
              confirmButtonText: "OKAY",
              confirmButtonColor: "#d33",
              allowOutsideClick: false,
            });
            break;
          // SAFETY NET
          // case "":
          //   Swal.fire({
          //     icon: "online",
          //     title: "",
          //     text: "Please verify the details and try again.",
          //     confirmButtonText: "OKAY",
          //     confirmButtonColor: "#d33",
          //     allowOutsideClick: false,
          //   });
          //     break;
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
