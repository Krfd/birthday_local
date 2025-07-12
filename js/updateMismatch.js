$(".modal").on("submit", "form[id^='updateMismatch_']", function (e) {
  e.preventDefault();
  var customer = $(this).serialize();

  $.ajax({
    type: "POST",
    url: "./functions/updateMismatch.php",
    data: customer,
    success: function (res) {
      switch (res) {
        case "updated":
          Swal.fire({
            icon: "success",
            iconColor: "#28a745",
            title: "Customer has been updated",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            willClose: () => {
              location.reload();
            },
            customClass: {
              popup: "small-alert",
              title: "small-title",
              content: "small-content",
            },
          }).then(() => {
            location.reload();
          });
          break;
        case "existed":
          Swal.fire({
            icon: "error",
            title: "Username is already taken",
            text: "Please try another username.",
            confirmButtonText: "Try again",
            confirmButtonColor: "#d33",
            allowOutsideClick: false,
          });
          break;
        default:
          Swal.fire({
            icon: "error",
            title: res,
            confirmButtonText: "Try again",
            confirmButtonColor: "#d33",
            allowOutsideClick: false,
          });
      }
    },
  });
});
