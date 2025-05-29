//https://detail.chiebukuro.yahoo.co.jp/qa/question_detail/q12186608547
onload = function() {
  //変数
  var max = 1;
  var count = 4;

  //要素
  var box = document.getElementById('box');
  var msg = document.getElementById('msg');

  var timerID = setInterval(function() {
    if (count == max) {
      clearInterval(timerID);
      box.classList.remove("css1");
      box.classList.add("css2");
      msg.innerHTML = '<a style="color: white;">元のページに戻ります</a>';
    } else {
      count--;
      msg.textContent = count;
    }
  }, 1000);
}
