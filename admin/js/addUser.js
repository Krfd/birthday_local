$(document).ready(function () {
  $("#formAddUser").submit(function (e) {
    e.preventDefault();
    var userData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "functions/addUser.php",
      data: userData,
      success: function (res) {
        switch (res) {
          case "added":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "User has been created",
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
          case "exists":
            Swal.fire({
              icon: "error",
              title: "User already exists",
              text: "Please try again",
              confirmButtonText: "Try again",
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
