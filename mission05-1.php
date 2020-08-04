<!--編集の受信処理&DB各処理-->
<?php  
  //　変数の初期化
  $name = "";
  $comment = "";
  $pass = "";

  // DB接続設定
  $dsn = 'データベース名';
  $user = 'ユーザー名';
  $password = 'パスワード';
  $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
  //テーブル作成(CREATE)
  $sql = "CREATE TABLE IF NOT EXISTS tbdata"
  ." ("
  . "id INT AUTO_INCREMENT PRIMARY KEY,"
  . "name char(32),"
  . "comment TEXT,"
  . "date datetime,"
  . "pass char(16)"
  .");";
  $stmt = $pdo->query($sql);
  //編集番号指定用のフォームの受信
  if(isset($_POST["edit"]) && isset($_POST["pass_edi"])){
    $edit_number = $_POST["edit"]; #編集番号の設定
    $pass = $_POST["pass_edi"]; #パスワードの代入
    //保存されているパスワードを取得
    $sql = "SELECT * FROM tbdata WHERE id=:id"; 
    $stmt = $pdo->prepare($sql);//SQLの準備
    $stmt->bindParam(':id', $edit_number, PDO::PARAM_INT);//差し替えるパラメータの値を指定
    $stmt->execute();                             
    $results = $stmt->fetchAll(); //投稿データの配列の作成
    if($results[0]['pass'] == $pass){ //パスワードが一致するならば
        $name = $results[0]['name'];
        $comment = $results[0]['comment'];
    }
  }
?>

<!--ページのテンプレート-->
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission5-1</title>
    </head>
    <body>
        <h1>テーマ:ゲームについて</h1>
        <h2>フォーム</h2>
        <form action="mission05-1.php" method="post"><!--新規送信フォーム-->
            新規送信・編集フォーム <input type="hidden" name="edit_number" value= "<?php if($edit_number){echo $edit_number;}?>">
            <input type="text" name="name" placeholder="名前" value= "<?php if($name !== ""){echo $name;}?>">
            <input type="text" name="comment" placeholder="コメント" value= "<?php if($comment !== ""){echo $comment;}?>">
            <input type="text" name="pass_new"placeholder="パスワード" value= "<?php if($pass !== ""){echo $pass;}?>">
            <input type="submit" value="送信">
        </form>
        <form action="mission05-1.php" method="post"><!--編集番号指定用フォーム-->
            編集番号指定用フォーム <input type="number" name="edit" placeholder="編集したい投稿の番号">
            <input type="text" name="pass_edi" placeholder="パスワード">
            <input type="submit" value="編集">
        </form>
        <form action="mission05-1.php" method="post"><!--削除フォーム-->
            削除番号指定用フォーム <input type="number" name="delete" placeholder="削除したい投稿の番号">
            <input type="text" name="pass_del" placeholder="パスワード">
            <input type="submit" value="削除">
        </form>
        <br>
        <!--残りの各処理-->
　　　　　<?php
          //新規送信・編集フォームの受信
          if(isset($_POST["comment"]) && isset($_POST["name"]) && isset($_POST["pass_new"])){
            if($_POST["edit_number"] != 0){ //編集時の処理(UPDATE)
                $edit_number = $_POST["edit_number"];
                $name = $_POST["name"];#名前を変数に代入
                $comment = $_POST["comment"];#コメントを変数に代入
                $pass = $_POST["pass_new"];
                $sql = "UPDATE tbdata SET name=:name,comment=:comment,pass=:pass WHERE id=:id";//SQL文の準備
                $stmt = $pdo->prepare($sql);
                //各カラムの指定
                $stmt->bindParam(':name', $name, PDO::PARAM_STR); 
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR); 
                $stmt->bindParam(':pass', $pass, PDO::PARAM_STR); 
                $stmt->bindParam(':id', $edit_number, PDO::PARAM_INT);
                $stmt->execute();//SQLの実行
                echo "投稿を編集しました<br>";
            }else{//新規投稿時の処理(INSERT)
                $name = $_POST["name"];#名前を変数に代入
                $comment = $_POST["comment"];#コメントを変数に代入
                $pass = $_POST["pass_new"];#パスワードを代入
                $date = date("Y/m/d H:i:s"); #投稿日時を変数に代入
                $sql = $pdo -> prepare("INSERT INTO tbdata (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)"); //SQL文の準備
                //各カラムの指定
                $sql -> bindParam(':name', $name, PDO::PARAM_STR); 
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                $sql -> execute();//SQLの実行
                echo "投稿しました<br>";
            }
            
        }
        
        #削除フォームの受信(DELETE)
        if(isset($_POST["delete"])){
            $delete_number = $_POST["delete"]; #削除番号の設定
            $pass = $_POST["pass_del"]; #パスワードの代入
            //保存されているパスワードを取得
            $sql = "SELECT * FROM tbdata WHERE id=:id"; 
            $stmt = $pdo->prepare($sql);//SQLの準備
            $stmt->bindParam(':id', $delete_number, PDO::PARAM_INT);//差し替えるパラメータの値を指定
            $stmt->execute();                             
            $results = $stmt->fetchAll(); //投稿データの配列の作成
            if($results[0]['pass'] == $pass){ //パスワードが一致するならば
                $sql = "delete from tbdata where id=:id"; 
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $delete_number, PDO::PARAM_INT);//差し替えるパラメータの値を指定
                $stmt->execute();
            }
        }
        echo "<br>";
        echo "<hr>";
        echo "<h2>投稿一覧</h2>";
        #テキスト表示(SELECT)          
          $sql = 'SELECT * FROM tbdata'; //DBから投稿データの取得
          $stmt = $pdo->query($sql);
          $results = $stmt->fetchAll(); //投稿データの配列の作成
          if(!empty($results[0]['id'])){ //最初の投稿が存在すれば
            foreach($results as $row){ //各投稿ごとの表示処理
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].' ';
                echo $row['name'].' ';
                echo $row['comment'].' ';
                echo $row['date']."<br>";
            }
          } 
        ?>
    </body>
</html>