/** @format */

[].forEach.call(document.getElementsByTagName("button"), function (b) {
  b.addEventListener("click", function (ev) {
    window.location.href = "?t=" + ev.target.value;
  });
});
