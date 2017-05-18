<?php
/*
Plugin Name: Test plugin
Description: A test plugin to demostrate wordpress funcionality nuevoo
Author: Alfre
Version: 0.1
*/

add_action('admin_menu', 'test_plugin_setup_menu');




// Add Shortcode
function bold_text_shortcode() {
  $content = "contenido";

  echo '<br><strong>' . $content . '</strong>';

}

function muestra(){
  echo '<h1> HOLA </h1>';
  
}

//GLOBAL $results;

add_shortcode( 'cta', 'bold_text_shortcode');
['cta'];

//en estructura admin-panel
function test_plugin_setup_menu(){
        add_menu_page( 'Test Plugin Page', 'Test Plugin', 'manage_options', 'test-plugin', 'test_init' );
}
 //--------------
function test_init(){

  $servername = "localhost";
  $username = "root";
  $password = "root";
  $dbname = "learning";
  //$dbname = "easyfolio_dev";
  //global $wpdb;
  $mydb = new wpdb($username,$password,$dbname,$servername);
  $dir = admin_url('admin.php?page=test-plugin');

  $store_image_path = "";
  $upload_dir = wp_upload_dir();
  $site = site_url();
  $ocurrence = strpos($upload_dir['path'],'wp-content');
  $upload_path = $site."/".substr($upload_dir['path'], $ocurrence)."/"; 
  
  //check if id is set
  if(isset($_GET['id'])){
    if($_GET['flag'] == "edit"){
      
        //select from data base and load the data to edit

        $row = $mydb->get_row('SELECT * FROM person WHERE id = '.$_GET['id'].'');
        $id = $row->id;
        $firstname = $row->name;
        $lastname = $row->last_name;
        $age = $row->age;
        $image_path = $row->image_path;
        $comment = $row->comment;
        $store_image_path = $image_path;
    }

    if($_GET['flag'] == "delete"){
        //delete from data base
        $mydb->delete('person', array(
                                    'id'=>$_GET['id']
                                    ));
    }
  }

      //get the path to directory and modify to get the new path store in database
  
  if(isset($_POST['submit']) && ($_POST['firstname']!="") && 
    ($_POST['lastname']!="") && ($_POST['age']>"0")){

    

         
    if(($_FILES['image']['name'] ==="") && ($image_path == "")){
      echo "Choose an image";

    }

    if(($_FILES['image']['name'] ==="") && ($image_path != "")){
      
        $store_image_sub_path = substr(strrchr($store_image_path,"/"),1);
        $_FILES['image']['name']= $store_image_sub_path;
    }
    
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $age = $_POST['age'];
    $comment = $_POST['comment'];


      $errors= array();
      $file_name = $_FILES['image']['name'];
      $file_size = $_FILES['image']['size'];
      $file_tmp = $_FILES['image']['tmp_name'];
      $file_type = $_FILES['image']['type'];
      $tmp_end = explode('.',$_FILES['image']['name']);
      $file_ext = strtolower(end($tmp_end));
      
      $extensions= array("jpeg","jpg","png");
      
      if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
      }
      
      
      if (in_array($file_ext, $extensions)) {

        $uploadedfile = $_FILES['image'];
        $upload_overrides = array( 'test_form' => false );
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
      //update

          $mydb->update('person', array(
                                  'name'=>$_POST["firstname"],
                                  'last_name'=>$_POST["lastname"],
                                  'age'=>$_POST["age"],
                                  'image_path' => $upload_path.$_FILES['image']['name'],
                                  'comment' => $_POST["comment"]
                                ),
                              array('id'=> $_POST['id']));
          $image_path = $upload_path.$_FILES['image']['name'];
          echo "DATA UPDATED";

        } else {
        //create
      
            $mydb->insert('person', array(
                                'name' => $_POST["firstname"],
                                'last_name' => $_POST["lastname"],
                                'age' => $_POST["age"],
                                'image_path' => $upload_path.$_FILES['image']['name'],
                                'comment' => $_POST["comment"]
                              ));
            $image_path = $upload_path.$_FILES['image']['name'];
            echo "<h2>Values Inserted!</h2>";  
        }

        echo "extension allowed! ";
      }

    }else{
        //echo "<h3>extension not allowed, please choose a JPEG, JPG or PNG file.</h3>";
        echo "<h3>Please Complete all fields</h3>";
      }
  
  $results = $mydb->get_results("SELECT * FROM person");

?>
  <table>
    <tr><th>ID</th><th>Firstnme</th><th>Lastname</th><th>Age</th><th>Path</th><th>Comment</th><tr>
<?php

  $images="";

  $my_results = $results;
  foreach ($my_results as $result) {
    echo '<tr>
            <td>' .$result->id. '</td>
            <td>' .$result->name. '</td>
            <td>' .$result->last_name. '</td>
            <td>' .$result->age. '</td>
            <td>' .$result->image_path. '</td>
            <td>' .$result->comment. '</td>
            <td><a href="'.$dir.'&nombre='.$result->name.'&apel='.$result->last_name.'&id='.$result->id.'&flag=edit">edit</a>
                         &nbsp;|&nbsp;
                <a href="'.$dir.'&nombre='.$result->name.'&apel='.$result->last_name.'&id='.$result->id.'&flag=delete" >delete</a>
          </tr>';
  $images .=  '<li> <img src="' . $result->image_path . '"> </li>';
  }  

//en estrucutra--

  echo'
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="/wp-content/plugins/admin-panel/js/jquery.bxslider.js"></script>
    <link href="/wp-content/plugins/admin-panel/lib/jquery.bxslider.css" rel="stylesheet" />
    <script>
    $(document).ready(function(){
        $(".bxslider").bxSlider();
    });
    </script>
    
    <ul class="bxslider">
      '.$images.'
    </ul>';
//--------------
?>
    

<div class="fillForm">
  <form enctype="multipart/form-data" method="post">
        Path <?php echo isset($image_path) ? $image_path : 'No se guardo nada'; ?> </br>
        <img src="<?php echo isset($image_path) ? $image_path : null; ?>" /> </br>
        First name:<br> 
        <input type="text" name="firstname" placeholder="Pepe" value="<?php echo isset($firstname) ? $firstname : null; ?>" /> <br>
        Last name:<br>
        <input type="text" name="lastname" placeholder="Grillo" value="<?php echo isset($lastname) ? $lastname : null; ?>" /> <br>
        age:<br>
        <input type="number" name="age" placeholder="20" value="<?php echo isset($age) ? $age : null; ?>"/> <br>
        Comment:<br>
        <input type="text" name="comment" placeholder="Comentario" value="<?php echo isset($comment) ? $comment : null; ?>" /> <br>
        <br>
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : null; ?>"/>
        image: <br>
        <input type="file" name="image" />
        <input type="submit" name="submit" value="Submit" />
        </form>
</div>  

<?php

}


//en estructura--
function show_images(){

  $servername = "localhost";
  $username = "root";
  $password = "root";
  $dbname = "learning";
  $mydb = new wpdb($username,$password,$dbname,$servername);

    $images="";
    $results = $mydb->get_results("SELECT * FROM person");

    foreach ($results as $result) {
      $images .=  '<li> <img src="' . $result->image_path . '"> </li>';
    }

    echo'
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="/wp-content/plugins/admin-panel/js/jquery.bxslider.js"></script>
    <link href="/wp-content/plugins/admin-panel/lib/jquery.bxslider.css" rel="stylesheet" />
    <script>
    $(document).ready(function(){
        $(".bxslider").bxSlider();
    });
    </script>
    
    <ul class="bxslider">
      '.$images.'
    </ul>';
}

add_shortcode( 'plug', 'show_images');
['plug'];
//-----------
?>
