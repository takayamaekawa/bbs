<?php
require_once __DIR__ . '/../../bootstrap.php';

use Root\Composer\Core\Database\Connection;

header('Content-Type: text/css; charset=utf-8');

$pdo = Connection::getConnection();
if ($pdo) {
    $posts = $pdo->query('SELECT name, posts.* FROM users JOIN posts ON users.id=posts.created_by ORDER BY posts.created_at DESC');
}

// word-wrap: break-all;
?>
.under {
text-decoration: underline;
}

header li {
font-family: kh-dougen16 !important;
}

.slide-menu {
background-color: rgba(0, 0, 0, .8);
position: fixed;
top: 65px;
width: 100%;
left: 0;
transform: translateX(100%);
transition: .5s;
/* 追記 */
}

.slide-menu li {
color: #fff;
line-height: 200% !important;
/* 間隔調整はこちら */
text-align: center;
font-size: 20px;
}

.hamburger {
width: 40px;
height: 25px;
right: 20px;
position: relative;
transition: .5s;
/* 追記 */
}

.trim {
position: relative;
overflow: hidden;
width: 300px;
height: 300px;
border-radius: 50%;
}

.trim img {
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
-webkit-transform: translate(-50%, -50%);
-ms-transform: translate(-50%, -50%);
height: 100%;
}

.welcome {
position:
absolute;
left: 0;
}

.profile_icon {
margin-left: 5px;
margin-right: 5px;
margin-bottom: 5px;
width: 45px;
height: 45px;
}

.name_icon {
margin-left: 5px;
margin-right: 5px;
width: 30px;
height: 30px;
}

.hr1 {
border-top: 1px solid #aaa;
}

.hr2 {
border-top: 2px solid white;
}

.hr3 {
border-top: 2px solid orange;
background-color: rgb(232, 243, 131);
}

.dotted01 {
border-bottom: dotted 2px;
}

.dotted02 {
color: white;
border-bottom: dotted 5px;
}

body {
word-break: break-all;
}

textarea {
width: 70%;
height: 100px;
}

.flex {
display: flex;
}

div .child {
margin: 5px;
}

.absolute_right {
position: absolute;
right: 0;
}

.j-flex {
display: flex;
justify-content: space-between;
}

.f-flex {
display: flex;
flex-flow: column;
}

.verti {
margin-left: -100px;
writing-mode: vertical-rl;
}

.block {
display: inline-block;
}

.form {
padding-left: 7%;
}

.form_reverse {
padding-right: 7%;
}

.comment {
padding-left: 7%;
padding-right: 7%;
}

.reply_comment {
padding-left: 4%;
padding-right: 4%;
}

.name {
padding-left: 2%;
}

.num {
padding-left: 1%;
position: absolute;
left: 1%
}

.num_reverse {
padding-right: 1%;
}

.time {
font-size: 1.6rem;
}

.edit {
font-size: 1.8rem;
padding-left: 5%;
}

.post_btn {
padding-left: 5%
}

.right_btn {
position: absolute !important;
right: 1% !important;
}

.select_btn {
position: absolute !important;
right: 5% !important;
}

.non_user_post_btn {
padding-left: 25%;
}

.img img {
width: 100%;
height: auto;
}

.img {
padding-left: 5%;
padding-right: 5%;
}

video {
width: 100%;
height: auto;
}

.video {
margin-left: 5%;
margin-right: 5%;
}

.anchor {
padding-top: 100px;
margin-top: 100px;
}

.anchor2 {
padding-top: 60px;
margin-top: -60px;
}

.anchor2 div {
margin-bottom: 40px;
padding: 5px 20px 10px;
}

.anchor3::before {
display: block;
height: 13rem;
margin-top: -13rem;
content: "";
}

.jump_point {
height: 1px;
display: block;
padding-top: 40px;
margin-top: -40px;
}

.text {
display: none;
}

.text01 {
display: block;
}

<?php $height = 30;
$width = $height * 2.2;
$left = $width - $height;

?>
.toggle_input {
position: absolute;
left: 0;
top: 0;
width: 100%;
height: 100%;
z-index: 5;
opacity: 0;
cursor: pointer;
}

.toggle_label {
width: <?php echo $width;
        ?>px;
height: <?php echo $height;
        ?>px;
background: #aaa;
position: relative;
display: inline-block;
border-radius: 40px;
transition: 0.4s;
box-sizing: border-box;
}

.toggle_label:after {
content: "";
position: absolute;
width: <?php echo $height;
        ?>px;
height: <?php echo $height;
        ?>px;
border-radius: 100%;
left: 0;
top: 0;
z-index: 2;
background: #fff;
box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
transition: 0.4s;
}

.toggle_input:checked+.toggle_label {
background-color: #4BD865;
}

.toggle_input:checked+.toggle_label:after {
left: <?php echo $left;
      ?>px;
}

.toggle_button {
position: relative;
width: <?php echo $width;
        ?>px;
height: <?php echo $height;
        ?>px;
margin: auto;
margin-right: 0px;
}

<?php //ここからボタンリバース
?>

.reverse_toggle_input {
position: absolute;
left: 0;
top: 0;
width: 100%;
height: 100%;
z-index: 5;
opacity: 0;
cursor: pointer;
}

.reverse_toggle_label {
width: <?php echo $width;
        ?>px;
height: <?php echo $height;
        ?>px;
background: #4BD865;
position: relative;
display: inline-block;
border-radius: 40px;
transition: 0.4s;
box-sizing: border-box;
}

.reverse_toggle_label:after {
content: "";
position: absolute;
width: <?php echo $height;
        ?>px;
height: <?php echo $height;
        ?>px;
border-radius: 100%;
right: 0;
top: 0;
z-index: 2;
background: #fff;
box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
transition: 0.4s;
}

.reverse_toggle_input:checked+.reverse_toggle_label {
background-color: #aaa;
/*exchange #4BD865*/
}

.reverse_toggle_input:checked+.reverse_toggle_label:after {
right: <?php echo $left;
        ?>px;
}

.reverse_toggle_button {
position: relative;
width: <?php echo $width;
        ?>px;
height: <?php echo $height;
        ?>px;
margin: auto;
margin-right: 0px;
}

a:link {
text-decoration: none
}

a.page_number:visited {
color: black;
text-decoration: none
}

.pagination {
display: flex;
justify-content: center;
margin: 15px;
}

.page_feed {
width: 30px;
margin: 0 10px;
padding: 5px 10px;
text-align: center;
background: #b8b8b8;
color: black;
}

.first_last_page {
width: 30px;
margin: 0 10px;
padding: 5px 10px;
text-align: center;
background: #f0f0f0;
color: black;
}

a:link {
text-decoration: none
}

a.page_number:visited {
color: black;
text-decoration: none
}

.page_number {
width: 30px;
margin: 0 10px;
padding: 5px;
text-align: center;
background: #b8b8b8;
color: black;
}

.now_page_number {
width: 30px;
margin: 0 10px;
padding: 5px;
text-align: center;
background: #f0f0f0;
color: black;
font-weight: bold;
}
