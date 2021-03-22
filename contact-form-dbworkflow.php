<?php
/*
Plugin name: Contact Form Workflowmax
Description: Save and manage Contact Form 7 messages.Contact Form  plugin is an add-on for the Contact Form 7 plugin to save and send data to Workflowmax.
Author: Aruljothi
Author URI: http://stallioni.com/
Text Domain: contact-form-workflow
Domain Path: /languages/
Version: 1.0.1
*/

include_once(ABSPATH.'wp-admin/includes/plugin.php');
if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
  }
  else
  {
    echo 'Please active the Contact Form 7 plugin ';
  }
define( 'WORKFLOWMAX_DIR', plugin_dir_path( __FILE__ ) );

  $table_name_log = $wpdb->prefix . 'cfdbworkflow_logs';
define( 'LOG_TBNAME', $table_name_log);

 $table_name_details = $wpdb->prefix . 'cfdbworkflow_details';
define( 'DETAILS_TBNAME', $table_name_details);

 $table_name_forms = $wpdb->prefix . 'cfdbworkflow_forms';
define( 'FORM_TBNAME', $table_name_froms);

register_activation_hook( __FILE__, 'cfdb7workflow_on_activate' );
function cfdb7workflow_on_activate(){
   global $wpdb;
   // cfdb7_create_table();
  // $role = get_role( 'administrator' );
  // $role->add_cap( 'cfdb7workflow_access' );
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  
    //$cfdb       = apply_filters( 'cfdb7_database', $wpdb );
    $table_name = $wpdb->prefix.'cfdbworkflow_forms';

    $table_namew = $wpdb->prefix.'cfdbworkflow_details';

    $table_log = $wpdb->prefix.'cfdbworkflow_logs';

 
        $charset_collate = $wpdb->get_charset_collate();
       
        $sql = "CREATE TABLE IF NOT EXISTS ". $table_name ."(
            form_id bigint(20) NOT NULL AUTO_INCREMENT,
            form_post_id bigint(20) NOT NULL,
            form_value longtext NOT NULL,
            form_value_array longtext NOT NULL,
            form_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            workflow_clientid  VARCHAR(100) NOT NULL,
            PRIMARY KEY  (form_id)
        ) $charset_collate;";
 dbDelta( $sql );


         $sqlzz = "CREATE TABLE IF NOT EXISTS ".$table_namew ."(
            id bigint(20) NOT NULL AUTO_INCREMENT,
            client_id longtext NOT NULL,
            client_secret longtext NOT NULL,
            id_token longtext NOT NULL,
            access_token longtext NOT NULL,
            expires_in text NOT NULL,
            token_type text NOT NULL,
            refersh_token longtext NOT NULL,
            scope longtext NOT NULL,
            authEventId TEXT NOT NULL,
            tenantId TEXT NOT NULL , 
            tenantName TEXT NOT NULL ,
             createdDateUtc TEXT NOT NULL,
             created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)) $charset_collate;";
          dbDelta( $sqlzz );


            
    $sqls="CREATE TABLE IF NOT EXISTS  ".$table_log." (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `log_type` varchar(250) DEFAULT NULL,
      `log_details` varchar(250) DEFAULT NULL,
      `created_on` datetime NOT NULL DEFAULT current_timestamp(),
     PRIMARY KEY (`log_id`)
    ) $charset_collate;";

    dbDelta( $sqls );
     

    $upload_dir    = wp_upload_dir();
    $cfdb7_dirname = $upload_dir['basedir'].'/cfdbworkflow_uploads';
    if ( ! file_exists( $cfdb7_dirname ) ) {
        wp_mkdir_p( $cfdb7_dirname );
        $fp = fopen( $cfdb7_dirname.'/index.php', 'w');
        fwrite($fp, "<?php \n\t // Silence is golden.");
        fclose( $fp );
    }
    add_option( 'cfdbworkflow_view_install_date', date('Y-m-d G:i:s'), '', 'yes');
}

 


function cfdb7_on_deactivate() {
  // Remove custom capability from all roles
  global $wp_roles;
  foreach( array_keys( $wp_roles->roles ) as $role ) {
    $wp_roles->remove_cap( $role, 'cfdb7workflow_access' );
  }
}

add_action( 'admin_menu', 'contactsevenworkflow_admin_menu_page' );


function contactsevenworkflow_admin_menu_page() {
    add_menu_page('Contactform Workflow', 'Contactform Workflow', 'manage_options', 'contactformworkflow_page', 'contactformworkflow_page','dashicons-dashboard',200);
   add_submenu_page('contactformworkflow_page', 'Logs', 'Logs', 'manage_options', 'logs_page', 'logs_page'); 
}
add_action( 'admin_init', 'workflow_api_admin_init' );
function workflow_api_admin_init() {
    add_settings_section(
        'workflow_api_settings',
        'Workflow Max API Settings',
        'fj_options_page_description',
        'workflow_api_settings'
    );

    add_settings_field(
        'fj_infusionsoft_api_client_id',
        'Workflow Max API Client ID',
        'fj_workflow_api_client_id_field',
        'workflow_api_settings',
        'workflow_api_settings'
    );

    add_settings_field(
        'fj_workflow_api_client_secret',
        'Workflow Max API  Client Secret',
        'fj_workflow_api_client_secret_field',
        'workflow_api_settings',
        'workflow_api_settings'
    );
     add_settings_field(
        'fj_workflow_api_client_redirect',
        'Workflow Max API redirect url',
        'fj_workflow_api_client_redirect_field',
        'workflow_api_settings',
        'workflow_api_settings'
    );


      add_settings_field(
        'fj_workflow_api_client_staff',
        'Workflow Max API STAFF ',
        'fj_workflow_api_client_staff_field',
        'workflow_api_settings',
        'workflow_api_settings'
    );

    register_setting( 'workflow_api_settings', 'workflow_api', 'workflow_api_sanitize' );



}

function workflow_api_sanitize( $value ) {
    return  $value ;// preg_replace( "/[^a-zA-Z0-9]+/", "", $value );
}

function fj_workflow_api_client_id_field() {
    echo '<input type="text" name="workflow_api[client_id]" value="' . get_option( 'workflow_api' )['client_id'] . '" size="30" /><br />';
}
function fj_workflow_api_client_redirect_field(){
     echo '<input type="text" name="workflow_api[redirect_uri]" value="' . get_option( 'workflow_api' )['redirect_uri'] . '" size="30" /><br />'; 
}
function fj_workflow_api_client_staff_field()
{
  global $wpdb;
   $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
     
if(isset($user))
{
  $access_token = $user->access_token;
  if(!empty($access_token))
  {
     $Workflowmax = new Workflowmax();
    if($Workflowmax->is_token_expiring())
         {
             $Workflowmax->try_refresh_access_token();
              
         }else{
             //nothing do 
             // $Workflowmax->try_refresh_access_token();
         }
  $staffdata = $Workflowmax->get_staffdetails();
  //print_R($staffdata);
  echo '<select name="workflow_api[staffid]" class="fomr-control"><option value="">Select staff</option>';
  foreach($staffdata as $staffval)
  {
    $name= $staffval['name'];
    $id= $staffval['id'];
   echo $value=get_option( 'workflow_api' )['staffid'];
    if($value==$id){ $seel = 'selected';}else{ $seel ='';}
     echo '<option name="workflow_api[staffid]" '.$seel.' value="'.$id.'" />'.$name.'</option>'; 
   }
  echo '<select>';
  }
}else{

   echo '<input type="text" name="workflow_api[staffid]" value="' . get_option( 'workflow_api' )['staffid'] . '" size="30" /><br />'; 
}
           
 
     
}
/**
 * Display the Client Secret field.
 */
function fj_workflow_api_client_secret_field() {
    echo '<input type="text" name="workflow_api[client_secret]" value="' . get_option( 'workflow_api' )['client_secret'] . '" size="80" /><br />';
}
function fj_options_page_description() {
    ?>
 
    <?php
}

if (!function_exists('logs_page'))  
{
    function logs_page(){
      
       include_once(WORKFLOWMAX_DIR.'stl_user_log.php'); 
    }
}
 
function contactformworkflow_page()
{
    global $wpdb;
       echo '<form method="POST" action="options.php">';
     settings_fields( 'workflow_api_settings' );
     do_settings_sections( 'workflow_api_settings' );
     submit_button();
     echo '</form>';
  
        $clientId = get_option( 'workflow_api' )['client_id'];
        $clientSecret = get_option( 'workflow_api' )['client_secret'];
        $redirectUri = get_option( 'workflow_api' )['redirect_uri'];
        $scope = 'workflowmax offline_access';

// if(isset( $clientId))
// {
//   $wpdb->query("INSERT INTO `wp_cfdbworkflow_details` (`id`, `client_id`, `client_secret`) VALUES ('1', '".$clientId."', '".$clientSecret."' )");
// }
     $table_namew = $wpdb->prefix . 'cfdbworkflow_details';    
if( $clientId)
{
       $selectquery = $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE 1");
       // print_R($selectquery);
       if(!isset($selectquery))
       {
       echo 'table not exitst';
         //nothing do 
       }else{
         $wpdb->query($wpdb->prepare("INSERT INTO ".$table_namew." (id,client_id, client_secret) VALUES (1,'".$clientId."', '".$clientSecret."')"));
       }
}
       // $wpdb->query("INSERT")
    $table_namew = $wpdb->prefix.'cfdbworkflow_details';
   $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
     
if(isset($user))
{
  $client_id = $user->client_id;
  if(!empty($client_id))
  {
       // $Workflowmax = new Workflowmax();
        if(isset($_GET['code']))
         {
              $Workflowmax = new Workflowmax();
              $Workflowmax->request_access_token($_GET['code']);
         }
          $access_token = $user->access_token;
          if(!empty($access_token))
          {
            $Workflowmax = new Workflowmax();
             $Workflowmax->is_token_expiring();
             if($Workflowmax->is_token_expiring())
             {
                 $Workflowmax->try_refresh_access_token();
                  
             }else{
                 //nothing do 
                 // $Workflowmax->try_refresh_access_token();
             }
          }
       /****************************************/     
      }
}
echo '<br><br><h4> Authorize the Xero and Workflowmax Api using below button
<br> If once authorize the tokens are set and saved to the database</h4>
';

   echo '<a target="_blank" class="button button-primary" href="https://login.xero.com/identity/connect/authorize?response_type=code&client_id='. $clientId.'&redirect_uri='.$redirectUri .'&scope='.$scope.'&state=123">Authorize url</a>';
  $code=$_GET['code'];
 
 }
add_action( 'wpcf7_before_send_mail', 'cfdbworkflow_before_send_mail' );
function cfdbworkflow_before_send_mail( $form_tag ) {
  global $wpdb;
      $table_name = $wpdb->prefix.'cfdbworkflow_forms';
    $upload_dir    = wp_upload_dir();
    $cfdb7_dirname = $upload_dir['basedir'].'/cfdbworkflow_uploads';
    $time_now      = time();

  $submission   = WPCF7_Submission::get_instance();
    $contact_form = $submission->get_contact_form();
    $tags_names   = array();
    $strict_keys  = apply_filters('cfdb7_strict_keys', false);  
    if ( $submission ) {
       $allowed_tags = array();
         if( $strict_keys ){
            $tags  = $contact_form->scan_form_tags();
            foreach( $tags as $tag ){
                if( ! empty($tag->name) ) $tags_names[] = $tag->name;
            }
            $allowed_tags = $tags_names;
        }

        $not_allowed_tags = apply_filters( 'cfdbworkflow_not_allowed_tags', array( 'g-recaptcha-response' ) );
        $allowed_tags     = apply_filters( 'cfdbworkflow_allowed_tags', $allowed_tags );
        $data             = $submission->get_posted_data();
        $files            = $submission->uploaded_files();
        $uploaded_files   = array();

        foreach ($_FILES as $file_key => $file) {
            array_push($uploaded_files, $file_key);
        }
        foreach ($files as $file_key => $file) {
            $file = is_array( $file ) ? reset( $file ) : $file;
            if( empty($file) ) continue;
            copy($file, $cfdb7_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($file));
        }

        $form_data   = array();

        $form_data['cfdbworkflow_status'] = 'unread';
        foreach ($data as $key => $d) {
            
            if( $strict_keys && !in_array($key, $allowed_tags) ) continue;

            if ( !in_array($key, $not_allowed_tags ) && !in_array($key, $uploaded_files )  ) {

                $tmpD = $d;

                if ( ! is_array($d) ){
                    $bl   = array('\"',"\'",'/','\\','"',"'");
                    $wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');
                    $tmpD = str_replace($bl, $wl, $tmpD );
                }

                $form_data[$key] = $tmpD;
            }
            if ( in_array($key, $uploaded_files ) ) {
                $file = is_array( $files[ $key ] ) ? reset( $files[ $key ] ) : $files[ $key ];
                $file_name = empty( $file ) ? '' : $time_now.'-'.$key.'-'.basename( $file ); 
                $form_data[$key.'cfdb7_file'] = $file_name;
            }
        }
        $form_post_id = $form_tag->id();
        $form_valuearr = $form_data ;
        $form_value   = serialize( $form_data );
        $form_date    = current_time('Y-m-d H:i:s');
 



        $wpdb->insert( $table_name, array(
            'form_post_id' => $form_post_id,
            'form_value'   => $form_value,
            'form_value_array'   =>  serialize($data) ,
            'form_date'    => $form_date
        ) );

        /* cfdb7 after save data */
        $insert_id = $wpdb->insert_id;
        do_action( 'cfdb7_after_save_data', $insert_id );
        
        send_contactfromdetails(serialize($data),$insert_id);

     }

    
}
function send_contactfromdetails($dataarray,$dataid)
{
   global $wpdb;
   

   $Workflowmax = new Workflowmax();
    
      if($Workflowmax->is_token_expiring())
         {
             $Workflowmax->try_refresh_access_token();
              
         }else{
             //nothing do 
             // $Workflowmax->try_refresh_access_token();
         }
         $valuesdata = unserialize($dataarray);
         $email = $valuesdata['client-email']?$valuesdata['client-email']:$valuesdata['email'];
        
      $userexitst = $Workflowmax->check_contact_Alreadyexists($email);
   if( $userexitst =='notexist')
   {
       $Workflowmax->post_client_to_workflowmax($dataarray,$dataid);
      //insert the data to the workflowmax
    }else{
      $table_name = LOG_TBNAME;
      $data=array('log_type' => 'alreedy data in workflow','log_details' =>'User already exists','created_on' => date('Y-m-d H:i:s'));
     $wpdb->insert( $table_name, $data);
    //  $Workflowmax->post_client_to_workflowmax($dataarray,$dataid);
     //nothing do 
   }

}

/*******************************************************************
                 WORKFLOWMAX CLASS START
********************************************************************/
class Workflowmax 
{
     public $auth = 'https://login.xero.com/identity/connect/authorize';
     public $tokenUri = 'https://identity.xero.com/connect/token';
     public $clientId;
     public $clientSecret;
     public $redirectUri;
    public function __construct()
    {
        global $wpdb,$option;
         $clientId = get_option( 'workflow_api' )['client_id'];
         $clientSecret = get_option( 'workflow_api' )['client_secret'];
         $scope = 'offline_access workflowmax';
         $redirectUri =get_option( 'workflow_api' )['redirect_uri'];

      $config = array();
      $config['clientId'] = $clientId;
      $config['clientSecret']=  $clientSecret;
      $config['redirectUri'] = $redirectUri ;
      $config['debug']= true;
      if (isset($config['clientId'])) {
            $this->clientId = $config['clientId'];
        }

        if (isset($config['clientSecret'])) {
            $this->clientSecret = $config['clientSecret'];
        }

        if (isset($config['redirectUri'])) {
            $this->redirectUri = $config['redirectUri'];
        }

        if (isset($config['debug'])) {
            $this->debug = $config['debug'];
        }
    }
    public function request_access_token( $code ) {
         try {
             
               $token = $this->requestAccessToken( $code );   
           }
         catch (Exception $e) {
                 return  'error';
         }
     }
   public function requestAccessToken($code)
    {

        $params = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUri,
        );
   //print_R($params);
        $clientid= $this->clientId;
        $clientSecret = $this->clientSecret;
        $redirectUri = $this->redirectUri;

    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://identity.xero.com/connect/token?grant_type=authorization_code&code='.$code.'&redirect_uri='.$redirectUri,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'grant_type=authorization_code&code='.$code.'&redirect_uri='.$redirectUri,
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic '.base64_encode($clientid.':'.$clientSecret) ,
    'Content-Type: application/x-www-form-urlencoded',
     
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;
        $respondedata  = json_decode($response);
    
        $id_token = $respondedata->id_token;
      $access_token =$respondedata->access_token;
      $expires_in = $respondedata->expires_in;
      $token_type = $respondedata->token_type;
      $refresh_token = $respondedata->refresh_token;
        $scope = $respondedata->scope;
       $endoftime =time() + $expires_in;
    global $wpdb;
     $table_namew = $wpdb->prefix.'cfdbworkflow_details';
 
        $clientId = get_option( 'workflow_api' )['client_id'];
        $clientSecret = get_option( 'workflow_api' )['client_secret'];
        $redirectUri = get_option( 'workflow_api' )['redirect_uri'];
        $scope = 'workflowmax offline_access';



     $wpdb->query($wpdb->prepare("UPDATE ".$table_namew." SET `client_id`='$clientId',`client_secret`='$clientSecret',`id_token`='$id_token',`access_token`= '$access_token', `expires_in`='$endoftime',`token_type`='$token_type',`refersh_token`='$refresh_token',`scope`='$scope' WHERE id=1"));

     /************************ get the trent id and id *************************/
     $this->try_refresh_access_token();
       //  $curl = curl_init();
       //  curl_setopt_array($curl, array(
       //    CURLOPT_URL => 'https://api.xero.com/connections',
       //    CURLOPT_RETURNTRANSFER => true,
       //    CURLOPT_ENCODING => '',
       //    CURLOPT_MAXREDIRS => 10,
       //    CURLOPT_TIMEOUT => 0,
       //    CURLOPT_FOLLOWLOCATION => true,
       //    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //    CURLOPT_CUSTOMREQUEST => 'GET',
       //    CURLOPT_HTTPHEADER => array(
       //      'Authorization: Bearer '.$access_token,
       //     ),
       //  ));
       //  $responsen = curl_exec($curl);
       //  curl_close($curl);
       // // echo $responsen;

       //  $respondedatan  = json_decode($responsen);
       //      //  echo '<pre>';print_R($respondedatan ); echo '</pre>';
       //          $id = $respondedatan[0]->id;
       //        $authEventId =$respondedatan[0]->authEventId;
       //        $tenantId = $respondedatan[0]->tenantId;
       //        $tenantName = $respondedatan[0]->tenantName;
       //        $createdDateUtc = $respondedatan[0]->createdDateUtc;

       //  $wpdb->query($wpdb->prepare("UPDATE ".$table_namew." SET `authEventId`='$authEventId',`tenantId`= '$tenantId', `tenantName`='$tenantName',`createdDateUtc`='$createdDateUtc' WHERE id=1"));



        }
    public function is_token_expiring() {
          global $wpdb;
          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
            $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
              $expires_in = $user->expires_in;
          return  ( 1200 > ( $expires_in - time() ) );
    }
     public function try_refresh_access_token() {
        try{
              $refreshed_token = $this->refreshAccessToken();
             // $this->update_option_access_token( $refreshed_token );
        }catch(Exception $e)
        {
          return 'error';   
        }
         
    }
    public function refreshAccessToken()
    {
          global $wpdb;
          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
          $refersh_token = $user->refersh_token;
           $clientid= $this->clientId;
          $clientSecret = $this->clientSecret;
          $redirectUri = $this->redirectUri;
          
//echo "authorization: Basic ".base64_encode($clientid. ":" .$clientSecret);

           $headers = array('grant_type'=>'refresh_token',
            'Authorization' => 'Basic ' . base64_encode($clientid.':'.$clientSecret),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        );

            $curl = curl_init();
         curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://identity.xero.com/connect/token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'grant_type=refresh_token&refresh_token='.$refersh_token,
          CURLOPT_HTTPHEADER =>  
           array(
            'grant_type: refresh_token',
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.base64_encode($clientid.':'.$clientSecret) ,
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
         $respondedata  = json_decode($response);
           //  echo '<pre>';print_R($respondedata ); echo '</pre>';

                $id_token = $respondedata->id_token;
              $access_token =$respondedata->access_token;
              $expires_in = $respondedata->expires_in;
              $token_type = $respondedata->token_type;
              $refresh_token = $respondedata->refresh_token;
                $scope = $respondedata->scope;
               $endoftime =time() + $expires_in;
            global $wpdb;
             $table_namew = $wpdb->prefix.'cfdbworkflow_details';
           
             $wpdb->query($wpdb->prepare("UPDATE ".$table_namew." SET `id_token`='$id_token',`access_token`= '$access_token', `expires_in`='$endoftime',`token_type`='$token_type',`refersh_token`='$refresh_token',`scope`='$scope' WHERE id=1"));

/************************ get the trent id and id *************************/
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.xero.com/connections',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$access_token,
           ),
        ));
        $responsen = curl_exec($curl);
        curl_close($curl);
       // echo $responsen;

        $respondedatan  = json_decode($responsen);
            //  echo '<pre>';print_R($respondedatan ); echo '</pre>';
                $id = $respondedatan[0]->id;
              $authEventId =$respondedatan[0]->authEventId;
              $tenantId = $respondedatan[0]->tenantId;
              $tenantName = $respondedatan[0]->tenantName;
              $createdDateUtc = $respondedatan[0]->createdDateUtc;

        $wpdb->query($wpdb->prepare("UPDATE ".$table_namew." SET `authEventId`='$authEventId',`tenantId`= '$tenantId', `tenantName`='$tenantName',`createdDateUtc`='$createdDateUtc' WHERE id=1"));
               

    }
    public function get_invoicescoding()
    {
        global $wpdb;
          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
          $access_token = $user->access_token;
          $tenantId  = $user->tenantId;
          $clientid= $this->clientId;
          $clientSecret = $this->clientSecret;
          $redirectUri = $this->redirectUri;

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.xero.com/api.xro/2.0/Invoices',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
           'xero-tenant-id: '.$tenantId,
            'Authorization: Bearer '.$access_token,
            'Accept: application/json',
            'Content-Type: application/json'

          ),
        ));
         
        $response = curl_exec($curl);
         curl_close($curl);
          echo $response;
    }
    public function check_contact_Alreadyexists($emailval)
    {
         global $wpdb;
          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
          $access_token = $user->access_token;
          $tenantId  = $user->tenantId;
          $clientid= $this->clientId;
          $clientSecret = $this->clientSecret;
          $redirectUri = $this->redirectUri;
         $curl = curl_init();
         curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.xero.com/workflowmax/3.0/client.api/list",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$access_token,
             "content-type: application/json",
             "xero-tenant-id: ".$tenantId
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          $error =  "cURL Error #:" . $err;
        } else {
              $xmldata=simplexml_load_string($response) or die("Error: Cannot create object");
              $Clients = ( $xmldata->Clients);
              //echo '<pre>';print_R($Clients);echo '</pre>';
              $userifreturns = 'notexist';
              $myarrayvalues =array();
              $Client = $Clients->Client;

             //  echo '<pre>';print_R($Client);echo '</pre>'; 
              foreach($Client as $myclients)
              {
                    $useridval = $myclients->UUID;
                    $email = $myclients->Email;
                  $myarrayvalues[]  = $email;
                  // $strcmp = strcmp($emailval,$email);
                  //   if($strcmp == 0 )
                  //   {
                  //     $userifreturns = $useridval;
                  //   }
              }
 

               if (in_array($emailval, $myarrayvalues))
                {
                    $userifreturns = 'userexists';
                }
                else
                {
                   $userifreturns = 'notexist';
                }
               return $userifreturns;  
        }
   }
 public function get_staffdetails()
 {
    global $wpdb;
          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
          $access_token = $user->access_token;
          $tenantId  = $user->tenantId;
          $clientid= $this->clientId;
          $clientSecret = $this->clientSecret;
          $redirectUri = $this->redirectUri;

         $curl = curl_init();
         curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.xero.com/workflowmax/3.0/staff.api/list",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$access_token,
             "content-type: application/json",
             "xero-tenant-id: ".$tenantId
          ),
        ));

        $response = curl_exec($curl);
       // echo  $response;
          $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          $error =  "cURL Error #:" . $err;
        } else {
              $xmldata=simplexml_load_string($response) or die("Error: Cannot create object");
              $StaffList = ( $xmldata->StaffList);
             // print_R($StaffList );
              $staffarray = array();
              $i=0;
              foreach($StaffList as $myStaffList)
              {
                    $useridval = $myStaffList->Staff->UUID;
                    $email = $myStaffList->Staff->Email;
                    $name = $myStaffList->Staff->Name;
                  $staffarray[$i]['name']= $name ;
                  $staffarray[$i]['id']= $useridval ;
                  $i++;
              }
              //print_R( $staffarray);
        }
  return  $staffarray;
  
 }

 public  function post_client_to_workflowmax($dataarray,$dataid) {
         // configure our connection to the api
     

       global $wpdb,$option;
         $staffid = get_option( 'workflow_api' )['staffid'];

          $table_namew = $wpdb->prefix.'cfdbworkflow_details';
          $user =  $wpdb->get_row("SELECT * FROM ".$table_namew ." WHERE id=1");
          $access_token = $user->access_token;
          $tenantId  = $user->tenantId;
          $clientid= $this->clientId;
          $clientSecret = $this->clientSecret;
          $redirectUri = $this->redirectUri;
 
        $valuesdata = unserialize($dataarray);
         $email = $valuesdata['client-email']?$valuesdata['client-email']:$valuesdata['email'];
         $name = $valuesdata['client-name']?$valuesdata['client-name']:$valuesdata['name'];
         $siteaddress = $valuesdata['site-address']?$valuesdata['site-address']:$valuesdata['siteaddress'];
         $contact = $valuesdata['client-contact']?$valuesdata['client-contact']:$valuesdata['phone'];
          $topic = $valuesdata['topic']?$valuesdata['topic']:$valuesdata['topic'];
         $message = $valuesdata['message']?$valuesdata['message']:$valuesdata['client-message'];

          $url = 'https://api.xero.com/workflowmax/3.0/client.api/add';
 $xml = '<Client>
             <Name>'. $name.'</Name>
            <Email>'.$email.'</Email>
            <Address></Address>
            <City></City>
            <Region></Region>
            <PostCode></PostCode>
            <Country></Country>
            <PostalAddress></PostalAddress>
            <PostalCity></PostalCity>
            <PostalRegion></PostalRegion>
            <PostalPostCode></PostalPostCode>
            <PostalCountry></PostalCountry>
            <Phone>'.$contact.'</Phone>
            <Fax></Fax>
            <Website>'. $siteaddress.'</Website>
            <ReferralSource></ReferralSource>
            <ExportCode></ExportCode>
            <IsProspect>No</IsProspect>
            <IsArchived>No</IsArchived>
            <IsDeleted>No</IsDeleted>
            <Contacts>
                <Contact>
                     <Name>'. $name.'</Name>
                    <Mobile></Mobile>
                    <Email>'. $email.'</Email>
                    <Phone>'.$contact.'</Phone>
                    <Position>test</Position>
                    <Salutation></Salutation>
                    <Addressee></Addressee>
                    <IsPrimary>Yes</IsPrimary>
                </Contact>
            </Contacts>
        </Client>';

$headers = array(
    "Content-type: text/xml",
    "Content-length: " . strlen($xml),
    'xero-tenant-id: '.$tenantId,
    'Authorization: Bearer '.$access_token,
);

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch); 
 // echo $data;
if(curl_errno($ch))
    { $retrundata =  curl_error($ch); $dataadsadds=array('log_type' => 'Workflowmax data  error','log_details' => curl_error($ch));
          $this->insert_logdata($dataadsadds); }
else
   { $retrundata = $data;
    curl_close($ch); }


  $xmldata=simplexml_load_string($data) or die("Error: Cannot create object");
  $Client = ( $xmldata->Client);
  //echo '<pre>'; print_R($xmldata); echo '</pre>';
  //echo '<pre>'; print_R($Client); echo '<pre>';

    $useridval = $Client->UUID;
   $email = $Client->Email;
   $Nameval  = $Client->Name;

  $table_name = $wpdb->prefix.'cfdbworkflow_forms';
  $wpdb->query("UPDATE ".$table_name. " SET workflow_clientid='".$useridval ."' WHERE form_id=".$dataid);

    $dataadsad=array('log_type' => 'Workflowmax data','log_details' => 'Workflow Max new client created'  );
   // print_R($dataadsad);
    $this->insert_logdata($dataadsad);
    
  /************************************* Adding lead*******************/
          $urllead = 'https://api.xero.com/workflowmax/3.0/lead.api/add';
         $xmllead = '<Lead>
          <Name>'.$Nameval .' '.$siteaddress.'</Name>
          <Description>'. $topic .'</Description>
          <ClientUUID>'.$useridval.'</ClientUUID>
          <OwnerUUID>'.$staffid.'</OwnerUUID>  
           <EstimatedValue>0</EstimatedValue>
        </Lead>'; 
        // <CategoryUUID>1404fb3b-ed04-4513-9c60-16955d5de540</CategoryUUID>
        $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xmllead),
            'xero-tenant-id: '.$tenantId,
            'Authorization: Bearer '.$access_token,
        );

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$urllead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmllead);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch); 
     //   echo $data;
          $xmldata=simplexml_load_string($data) or die("Error: Cannot create object");
          $dataadsadds=array('log_type' => 'Workflowmax data Lead','log_details' => 'Lead created');
          $this->insert_logdata($dataadsadds);

        if(curl_errno($ch))
            {  curl_error($ch);
$dataadsadds=array('log_type' => 'Workflowmax data Lead error','log_details' => curl_error($ch));
          $this->insert_logdata($dataadsadds); }
        else
            curl_close($ch);
   
     
 }

function insert_logdata($insert_data){
    global $wpdb;
    $table_name = LOG_TBNAME;
      $log_details= $insert_data['log_details'];
      $data=array('log_type' => $insert_data['log_type'],'log_details' => $log_details,'created_on' => date('Y-m-d H:i:s'));

    $wpdb->insert( $table_name, $data);
  }

}