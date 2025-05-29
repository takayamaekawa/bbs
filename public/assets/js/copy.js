$(document).ready(function() {
  $.ajaxSetup({
    cache: false
  }); //なくても良いがキャッシュを読み込まないように?

  $('.ajax').each(function() {
    var src = $(this).attr('src');
    if (src != '') {
      $(this).load(src);
    }
  });
});
