document.addEventListener("DOMContentLoaded", function () {
  const usernameInputs = document.querySelectorAll("input[name='username']");

  usernameInputs.forEach((input) => {
    input.addEventListener("input", function () {
      // Modify the regex to allow space
      this.value = this.value.replace(/[^a-zA-Z ]/g, "");
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const departmentInput = document.querySelector("input[name='department']");

  departmentInput.addEventListener("input", function () {
    // Convert the input value to uppercase
    this.value = this.value.toUpperCase();
  });
});
