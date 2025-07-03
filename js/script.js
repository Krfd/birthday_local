document.addEventListener("DOMContentLoaded", function () {
  const usernameInputs = document.querySelectorAll("input[name='username']");

  usernameInputs.forEach((input) => {
    input.addEventListener("input", function () {
      // Allow letters (a-z, A-Z), numbers (0-9), and space
      this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, "");
    });
  });
});

document.getElementById("showPassword").addEventListener("change", function () {
  const passwordInput = document.getElementById("password");
  passwordInput.type = this.checked ? "text" : "password";
});
