var button = document.getElementById("submit");
document.getElementById('submit').addEventListener('click', () => {
  button.className = "click";
  button.innerHTML = '<img src="/assets/img/icon/nowloading.gif"><span></span>送信中<input type="submit" style="display:none;">';
  setTimeout(function() {
    button.className = button.className.replace("click", "");
    button.innerHTML = '<span></span>再送信<input type="submit" style="display:none;">';
  }, 3000);
});
