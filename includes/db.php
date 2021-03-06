<?php
 class DB
 {
   public static function IsAuthenticated($username, $password) {
     $flag = false;
     $hashed_password = password_hash($password, PASSWORD_BCRYPT);
     $conn = DB::CreateConnection();
     $sql = $conn->prepare("SELECT username, pw FROM User WHERE username=?");
     $sql->bind_param("s", $username);
     $sql->execute();
     $sql->bind_result($returnedUsername, $returnedPassword);
     $sql->fetch();
     if(!is_null($returnedUsername) && !is_null($returnedPassword)){
       if($returnedUsername == $username && password_verify($password, $returnedPassword)) {
         return true;
       }
     }
     return false;
   }

   public static function isUsernameExist($username) {
     $flag = false;
     $conn = DB::CreateConnection();
     $sql = $conn->prepare('SELECT username FROM User WHERE username=?');
     $sql->bind_param("s", $username);
     $sql->execute();
     $sql->bind_result($result);
     $sql->fetch();

     if($username == $result) {
       $flag = true;
     }
     return $flag;
   }

   public static function CreateUser($username, $password, $email) {
     //  TODO Check if the username already exists
     $conn = DB::CreateConnection();
     $sql = $conn->prepare('INSERT INTO User (username, pw, email) VALUES (?, ?, ?)');
     $sql->bind_param("sss", $username, $password, $email);
     $sql->execute();
     $sql->close();
   }

   public static function GetSubjects() {
     $conn = DB::CreateConnection();
     $rawResults = $conn->query('SELECT subjects FROM Subject');
     $subjects = [];
     while ($row = $rawResults->fetch_assoc()) {
       $subjects[] = $row;
     }
     return $subjects;
   }

   public static function GetLatestBookId()
   {
     $conn = DB::CreateConnection();
     if(!$sql = $conn->prepare('SELECT max(id) FROM Book')) {
       die($conn->error);
     }
     if(!$sql->execute()) {
       die($sql->error);
     }
     if(!$sql->bind_result($id)) {
       die($sql->error);
     }
     $sql->fetch();
     return $id;
   }

   public static function CreateBook($title, $edition, $isbn, $instructor,
                                     $department, $major, $courseNumber, $condition) {
     // Book Table
     $conn = DB::CreateConnection();
     $sql = $conn->prepare("
      INSERT INTO Book (title, edition, ISBN, instructor, user_id, department, major, course_num, book_condition)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
     ");
     $sql->bind_param("sississss", $title, $edition, $isbn, $instructor, $USERIDTODO, $department, $major,
                                   $courseNumber, $condition);
     $sql->execute();
     $sql->close();
   }

    public static function CreateAuthorMultiple($authors) {
      $conn = DB::CreateConnection();
      $latestBookId = DB::GetLatestBookId();
      $conn->close();
      for ($i=0; $i < count($authors); $i++) {
        DB::CreateAuthor($i, $latestBookId);
      }
    }

    public static function CreateAuthor($author, $bookId) {
      $conn = DB::CreateConnection();
      $sql = $conn->prepare("
        INSERT INTO Author (book_id, `name`) VALUES (?, ?);
      ");
      $sql->bind_param("is", $bookId, $author);
      $sql->execute();
      $sql->close();
    }

    public static function CreateConnection() {
      $connection = new mysqli(
      "fredbooks.cbyhnokab1r4.us-east-2.rds.amazonaws.com",
       "lee5250",
        "uH2D2H6u",
         "fredbooks");
      if($connection->connect_error) {
        // stop all php, there is a connection error.
        die("Connection error: " . $connection->connect_error);
      }
      return $connection;
    }
 }
 ?>
