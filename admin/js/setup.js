$(document).ready(function () {
  $("#setupDate").submit(function (e) {
    e.preventDefault();
    var setupDate = $(this).serialize();

    const from = document.getElementById("from").value;
    const to = document.getElementById("to").value;

    console.log(`FROM: ${from}`);
    console.log(`TO: ${to}`);

    $.ajax({
      type: "POST",
      url: "functions/setup.php",
      data: setupDate,
      success: function (res) {
        switch (res) {
          case "updated":
            Swal.fire({
              icon: "success",
              iconColor: "#28a745",
              title: "Setup has been created",
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
          case "invalid":
            Swal.fire({
              icon: "error",
              title: "Invalid Date",
              text: "Please enter a valid date",
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
