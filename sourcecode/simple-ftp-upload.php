<?php
/*
Plugin Name: Simple FTP Uploads
Plugin URI: https://tech.hippo-lab.com/wp-21/
Description: Simple FTP plugin for wordpress
Author: shimada@hippo-lab.com
Version: 0.2
Author URI: https://hippo-lab.com
*/

/*
-Notice 1 
    Be sure to put "/" or "\" at the end of the directory specification. 

-Notice 2
    The direction is only compatible with "Linux-> Linux" or "Windows-> Linux".
    "Linux-> Windows" is not supported.
    
-How to use
    1. Make a directory with any name in the plugin directory and put this source in it.
    2. "Simple Static" is displayed in the dashboard menu.
    3. Select the submenu "Settings" and set the FTP connection information.
    4. Select the submenu "Run" and press the FTP start button.
    5. Confirm that "Finish FTP " is displayed on the screen.
*/

class SimpleFtpUpload {
    
    function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
    }

    //---------------------------------------------------------------------------------
    //Menu
    function add_plugin_admin_menu() {
        //Parent menu
         add_menu_page(
              'FTP Setting',   // page_title
              'Simple FTP',    // menu_title
              'edit_posts',    // capability
              'simple-ftp',    // menu_slug
              array($this,'ftp_upload_page') , // function
              '', // icon_url
              100 // position
         );
        //Submenu Run FTP
         add_submenu_page(
              'simple-ftp',     // parent_slug
              'Run FTP',        // page_title
              'Run FTP',        // menu_title
              'edit_posts',     // capability
              'simple-ftp',     // menu_slug
              array($this,'ftp_upload_page') // function
         );
         //Submenu　FTP Setting
         add_submenu_page(
              'simple-ftp',         // parent_slug
              'Simple FTP Setting', // page_title
              'Setting',            // menu_title
              'edit_posts',         // capability
              'simple-ftp-sub',     // menu_slug
              array($this,'ftp_login_option_page') // function
         );
         
         //remove_submenu_page
    }

    //---------------------------------------------------------------------------------
    //FTP Setting
    function ftp_login_option_page() {
    
        //$_POST['ftp_user_options'])
        if ( isset($_POST['ftp_user_options'])) {
            $opt1 = $_POST['ftp_user_options']; //USER
            $opt2 = $_POST['ftp_pass_options']; //PASS
            $opt3 = $_POST['ftp_serv_options']; //SERV
            $opt4 = $_POST['ftp_port_options']; //PORT
            $opt5 = $_POST['ftp_srcd_options']; //SRC DIR
            $opt6 = $_POST['ftp_dstd_options']; //DST DIR
            
            update_option('ftp_user_options', $opt1);
            update_option('ftp_pass_options', $opt2);
            update_option('ftp_serv_options', $opt3);
            update_option('ftp_port_options', $opt4);
            update_option('ftp_scrd_options', $opt5);
            update_option('ftp_dstd_options', $opt6);
            ?><div class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p></div><?php
        }
        ?>
        <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div><h2>FTP Setting</h2>
            <form action="" method="post">
                <?php
                // Ftp options from DataBase.
                $opt1 = get_option('ftp_user_options');
                $opt2 = get_option('ftp_pass_options');
                $opt3 = get_option('ftp_serv_options');
                $opt4 = get_option('ftp_port_options');
                $opt5 = get_option('ftp_scrd_options');
                $opt6 = get_option('ftp_dstd_options');
                $show_user = isset($opt1['text']) ? $opt1['text']: null;
                $show_pass = isset($opt2['text']) ? $opt2['text']: null;
                $show_serv = isset($opt3['text']) ? $opt3['text']: null;
                $show_port = isset($opt4['text']) ? $opt4['text']: 21;   //default
                $show_srcd = isset($opt5['text']) ? $opt5['text']: null;
                $show_dstd = isset($opt6['text']) ? $opt6['text']: null;

                // For Windows
                $show_srcd=str_replace("\\\\","\\",$show_srcd);
                $show_dstd=str_replace("\\\\","\\",$show_dstd);

                // The html for FTP Setting 
                ?> 
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">User</label></th>
                        <td><input name="ftp_user_options[text]" type="text" id="inputtext" value="<?php echo $show_user ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">Password</label></th>
                        <td><input name="ftp_pass_options[text]" type="password" id="inputtext" value="<?php echo $show_pass ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">Ftp server name (or address)</label></th>
                        <td><input name="ftp_serv_options[text]" type="text" id="inputtext" value="<?php echo $show_serv ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">PORT</label></th>
                        <td><input name="ftp_port_options[text]" type="text" id="inputtext" value="<?php echo $show_port ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">FTP Dir(From)</label></th>
                        <td><input name="ftp_srcd_options[text]" type="text" id="inputtext" value="<?php echo $show_srcd ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="inputtext">FTP Dir(To)</label></th>
                        <td><input name="ftp_dstd_options[text]" type="text" id="inputtext" value="<?php echo $show_dstd ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save the change" /></p>
            </form>
        <!-- /.wrap --></div>
        <?php
    }

    //---------------------------------------------------------------------------------
    //Run FTP
    function ftp_upload_page() {

        if (isset($_POST["SubmitFTP"])) {
            echo "<p>==== Start FTP =====</p>";
            
            //Read option from Database.
            $opt1 = get_option('ftp_user_options');
            $opt2 = get_option('ftp_pass_options');
            $opt3 = get_option('ftp_serv_options');
            $opt4 = get_option('ftp_port_options');
            $opt5 = get_option('ftp_scrd_options');
            $opt6 = get_option('ftp_dstd_options');
            $errmsg = "<p><font color='#FF0000'>Error</font>: FTP Setting</p>";
            $ftp_user = isset($opt1['text']) ? $opt1['text']: exit($errmsg);;
            $ftp_pass = isset($opt2['text']) ? $opt2['text']: exit($errmsg);;
            $ftp_serv = isset($opt3['text']) ? $opt3['text']: exit($errmsg);;
            $ftp_port = isset($opt4['text']) ? $opt4['text']: exit($errmsg);;
            $ftp_srcd = isset($opt5['text']) ? $opt5['text']: exit($errmsg);;
            $ftp_dstd = isset($opt6['text']) ? $opt6['text']: exit($errmsg);;
            
            //Conn FTP
            $conn = ftp_connect($ftp_serv,$ftp_port) or die("<p><font color='#FF0000'>Error</font>: FTP Connection</p>");;
            ftp_login($conn, $ftp_user, $ftp_pass)   or die("<p><font color='#FF0000'>Error</font>: FTP Login</p>");;
            ftp_pasv($conn, true)                    or die("<p><font color='#FF0000'>Error</font>: FTP pasv mode</p>");;
            
            //FTP Upload. ( The "ftp_lists" is recursive function)
            $ftp_flists = $this->ftp_lists($ftp_srcd) or die("<p><font color='#FF0000'>Error</font>: Not exist source dir</p>");
            foreach($ftp_flists as $ftp_srcfile){
                $ftp_dstfile = str_replace( $ftp_srcd, $ftp_dstd, $ftp_srcfile );
                
                //For Windows
                $ftp_dstfile = str_replace( "\\", "/", $ftp_dstfile);
                
                if (is_file( $ftp_srcfile )){
                    //If it's a file, do ftp_put.
                    ftp_put($conn, $ftp_dstfile, $ftp_srcfile, FTP_BINARY, false) or die("<p><font color='#FF0000'>Error</font>:FTP failuer [$ftp_dstfile]</p>");;
                    echo "<p>upload: $ftp_dstfile </p>";
                } else if(is_dir( $ftp_srcfile )){
                   //If it's a dir, do ftp_mkdir.
                    ini_set('display_errors', 'Off'); //Hide warning
                    ftp_mkdir($conn, $ftp_dstfile);   //Do not trap errors
                    ini_set('display_errors', 'On');
                    echo "<p>makedir: $ftp_dstfile </p>";
                }
            }

            //Cloese FTP
            ftp_close($conn);
            echo "<p>==== Finish FTP ====</p>";
        }
        //The html for run.
        ?>
            <form action="" method="post">
                <p class="submit"><input type="submit" name="SubmitFTP" class="button-primary" value="Do FTP" /></p>
            </form>
        <?php   
    }

    //Recursive function that creates a file list from a directory
    private function ftp_lists($dir){
        
        $retval=false;
        if( is_dir($dir) )
        {
            $list = array();
            $files = scandir($dir);
            foreach($files as $file){
                if($file == '.' || $file == '..'){
                    continue;
                } else if (is_file($dir . $file)){
                    $list[] = $dir . $file;
                } else if( is_dir($dir . $file) ) {
                    $list[] = $dir .$file;
                    $list = array_merge($list, $this->ftp_lists($dir . $file .  DIRECTORY_SEPARATOR));
                }
            }
            $retval = $list;
        }
        return $retval;
    }

}

$simpleFtpUpload = new SimpleFtpUpload;



?>
