<?php
  // DB接続
  $dsn = '**********';
	$user = '**********';
	$password = '**********';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  // DB内にテーブル作成
  $sql = "CREATE TABLE IF NOT EXISTS posts"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "date CHAR(32),"
    . "password CHAR(16)"
    .");";
  $stmt = $pdo->query($sql);

  function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }

  // CSRF対策
  session_start();

  function setToken() {
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;
  }

  function checkToken() {
    if (empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
      echo "不正なPOSTが行われました！";
      exit;
    }
  }

  // 投稿機能
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
  isset($_POST['comment']) &&
  isset($_POST['name']) &&
  isset($_POST['password'])) {

    $postName = trim($_POST['name']);
    $postComment = trim($_POST['comment']);
    $postPassword = trim($_POST['password']);
    $postDate = date('Y/m/d H:i:s');

    if ($postComment !== '') {

      $postName = ($postName === '') ? '名無しさん' : $postName;

      $sql = $pdo -> prepare("INSERT INTO posts (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
      $sql -> bindParam(':name', $name, PDO::PARAM_STR);
      $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
      $sql -> bindParam(':date', $date, PDO::PARAM_STR);
      $sql -> bindParam(':password', $password, PDO::PARAM_STR);
      $name = "$postName";
      $comment = "$postComment";
      $date = "$postDate";
      $password = "$postPassword";
      $sql -> execute();
    }
  }
  else {
    setToken();
  }

  // 消去機能
  if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
  isset($_POST['delete']) &&
  isset($_POST['delPassword'])) {

    $delete = $_POST["deleteNo"];
    $delPassword = trim($_POST['delPassword']);

    $id = (int)$delete;
    $password = (string)$delPassword;

    $sql = 'delete from posts where id=:id AND password=:password';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
  }

  // 編集機能
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
  isset($_POST['edit']) &&
  isset($_POST['editComment']) &&
  isset($_POST['editName']) &&
  isset($_POST['editPassword'])) {

    $edit = $_POST["editNo"];
    $editComment = trim($_POST['editComment']);
    $editName = trim($_POST['editName']);
    $editPassword = $_POST['editPassword'];
    $editDate = date('Y/m/d H:i:s');

    $id = (int)$edit;
    $name = (string)$editName;
    $comment = (string)$editComment;
    $password = (string)$editPassword;
    $date = (string)$editDate;

    $sql = 'UPDATE posts SET name=:name, comment=:comment, date=:date WHERE id=:id AND password=:password';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    
    $edit = null;
  }

  // 表示
  $sql = 'SELECT * FROM posts';
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ひとこと掲示板</title>
</head>
<body>
  <h1>ひとこと掲示板</h1>
  <form action="" method="post">
    <h2>○投稿</h2>
    コメント: <input type="text" name="comment" placeholder="コメント"><br>
    名前: <input type="text" name="name" placeholder="なまえ"><br>
    パスワード: <input type="password" name="password" placeholder="パスワード" maxlength="16"><br>
    <input type="submit" name="post" value="投稿" style="width: 250px; margin-top: 10px">
    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">

    <h2>○編集</h2>
    編集番号: <input type="number" name="editNo" min="1" max="" placeholder="0"><br>
    コメント: <input type="text" name="editComment" placeholder="コメント"><br>
    名前: <input type="text" name="editName" placeholder="なまえ"><br>
    パスワード: <input type="password" name="editPassword" placeholder="パスワード" maxlength="16"><br>
    <input type="submit" name="edit" value="編集" style="width: 250px; margin-top: 10px">

    <h2>○消去</h2>
    消去番号: <input type="number" name="deleteNo" min="1" max="" placeholder="0"><br>
    パスワード: <input type="password" name="delPassword" placeholder="パスワード" maxlength="16"><br>
    <input type="submit" name="delete" value="消去" style="width: 250px; margin-top: 10px">
  </form>

  <h2>投稿一覧（<?= count($results); ?>件）</h2>
  <ul>
    <?php if (count($results)) : ?>
      <?php foreach ($results as $row) : ?>
        <li>
          <?= h($row['id']); ?> <?= h($row['name']); ?> ( <?= h($row['comment']); ?> ) - <?= h($row['date']); ?>
        </li>
      <?php endforeach; ?>
    <?php else : ?>
      <li>まだ投稿はありません。</li>
    <?php endif; ?>
  </ul>
</body>
</html>