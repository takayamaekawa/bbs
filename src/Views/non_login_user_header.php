<header>
  <br>
  <div class="header-area">
    <h1 class="green" style="z-index: 5;"><a href="/">&nbsp;&nbsp;絶・掲示板</a></h1>
    <div class="absolute_right">
      <div class="trim_header profile_icon profile" style="z-index: 5;">
        <img src="/assets/img/default_icon.png" alt="デフォルトアイコン">
      </div>
    </div>

    <ul class="slide-menu">
      <br>
      <li>
        <div class="f-flex">
          <div class="welcome">
            <a class="font1-5">&nbsp;&nbsp;ななしさん、ようこそ</a>
          </div>
        </div>
      </li>
      <br><br>
      <li>
        <a href="/profile/login.php" class="kh-dougen16 font1-5 repo-btn-blue white">ログインはこちら</a>
      </li>
    </ul>
    <script>
      document.querySelector('.profile').addEventListener('click', function() {
        this.classList.toggle('active');
        document.querySelector('.slide-menu').classList.toggle('active');
      })
    </script>
  </div>
  <style>
    .flex nyanya img {
      margin-top: 5px;
    }

    .flex nyanyanya img {
      margin-top: 10px;
    }
  </style>
  <div class="font1-0 white headscroll" style="line-height: 10px!important;letter-spacing: 5px;padding-right:40px;">
    <span>
      <a class="" href="/counter/index.php">
        <div class="flex">
          <nyanya>
            <img src="/assets/img/icon/link3.png" width="20px" height="25px">
          </nyanya>
          <nyanyanya style="padding-top:-3px;!important;">
            絶・掲示板へようこそ
          </nyanyanya>
        </div>
      </a>
    </span>
  </div>
</header>
