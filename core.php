<?php
/**
 * @author      Heejra
 * @copyright   (C) 2020 - Heejra. All rights reserved.
 * @link        https://heejra.net
 */
ob_start();
header("X-Robots-Tag: noindex,nofollow");
use PHPMailer\PHPMailer\PHPMailer;
require __DIR__.'/vendor/autoload.php';
if (getenv('APP_ENV') == 'production') {
    error_reporting(0);
    @ini_set('display_errors', 0);
}else{
    error_reporting(1);
    @ini_set('display_errors', 1);
}

//load the environment variablea
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//Initialize the flat file
$storage = new Flatbase\Storage\Filesystem('storage');
$flatbase = new Flatbase\Flatbase($storage);

//Initialize csrf_token
session_start();
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['token'];

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $method = strtolower($_SERVER['REQUEST_METHOD']);
    //only post and delete method allowed
    if( $method === 'post' && isset($_REQUEST['method_field'])) {
        $tmp = strtolower((string)$_REQUEST['method_field']);
        if( in_array( $tmp, array( 'delete' ))) {
            $method = $tmp;
        }else{
            header('HTTP/1.0 501 Not Implemented');
            die();
        }
        unset($tmp);
    }

    //securing form from XSS
    // iterating over POST data
    foreach($_POST as $key => $value) { 
        //first we are doing non-destructive modifications
        //in case we will need to show the data back in the form on error
        $value = trim($value); 
        if (get_magic_quotes_gpc()) $value = stripslashes($value); 
        $value = htmlspecialchars($value,ENT_QUOTES); 
        $_POST[$key] = $value; 
        //here go "destructive" modifications, specific to the storage format
        $value = str_replace("\r","",$value);
        $value = str_replace("\n","<br>",$value);
        $value = str_replace("|","&brvbar;",$value);
        $msg[$key] = $value;
    }


    // now, just run the logic that's appropriate for the requested method
    switch( $method ) {
        case "post":
            // logic for POST here
        if (!empty($_POST['csrf_token'])) {
            if (hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
                // Proceed to process the form data

                // CHANGE THE ERROR MESSAGE FOR YOUT SUBSCRIBERS HERE IF THE VALIDAION FAILS
                $message_type = getenv('MESSAGE_TYPE_ERROR_FORM_FILLING');
                $message_title_for_user = getenv('MESSAGE_TITLE_ERROR_FORM_FILLING');
                $message_content_for_user = getenv('MESSAGE_CONTENT_ERROR_FORM_FILLING');
                //--END CHANGE THE ERROR MESSAGE FOR YOUT SUBSCRIBERS HERE IF THE VALIDAION FAILS

                    //validation
                    if (!$msg['name'] && $msg['email'] && $msg['phone'] && $msg['guest']) {
                        //name not filled, email,guest,phone filled
                        $_SESSION['flash_name'] = 'Please fill the name';
                        $_SESSION['flash_old_name'] = $msg['name'];
                        $_SESSION['flash_old_email'] = $msg['email'];
                        $_SESSION['flash_old_phone'] = $msg['phone'];
                        $_SESSION['flash_old_guest'] = $msg['guest'];
                    }
                    elseif (!$msg['email'] && $msg['name'] && $msg['phone'] && $msg['guest']) { 
                        //email not filled, name,guest,phone filled
                        $_SESSION['flash_email'] = 'Please fill the email';
                        $_SESSION['flash_old_name'] = $msg['name'];
                        $_SESSION['flash_old_email'] = $msg['email'];
                        $_SESSION['flash_old_phone'] = $msg['phone'];
                        $_SESSION['flash_old_guest'] = $msg['guest'];
                    }
                    elseif (!$msg['phone'] && $msg['name'] && $msg['email'] && $msg['guest']) { 
                        //phone not filled, name,guest,email filled
                        $_SESSION['flash_phone'] = 'Please fill the phone';
                        $_SESSION['flash_old_name'] = $msg['name'];
                        $_SESSION['flash_old_email'] = $msg['email'];
                        $_SESSION['flash_old_phone'] = $msg['phone'];
                        $_SESSION['flash_old_guest'] = $msg['guest'];
                    }
                    elseif ((!$msg['guest'] || $msg['guest'] <= 0) && $msg['name'] && $msg['email'] && $msg['guest']) { 
                        //guest not filled, name,phone,email filled
                        $_SESSION['flash_guest'] = 'Please fill the guest';
                        $_SESSION['flash_old_name'] = $msg['name'];
                        $_SESSION['flash_old_email'] = $msg['email'];
                        $_SESSION['flash_old_phone'] = $msg['phone'];
                        $_SESSION['flash_old_guest'] = $msg['guest'];
                    }
                    elseif (!$msg['name'] && !$msg['email'] && !$msg['phone'] && !$msg['guest']) {
                        //both name and email not filed
                        $_SESSION['flash_name'] = 'Please fill the name';
                        $_SESSION['flash_email'] = 'Please fill the email';
                        $_SESSION['flash_phone'] = 'Please fill the phone';
                        $_SESSION['flash_guest'] = 'Please fill the guest';
                        $_SESSION['flash_old_name'] = $msg['name'];
                        $_SESSION['flash_old_email'] = $msg['email'];
                        $_SESSION['flash_old_phone'] = $msg['phone'];
                        $_SESSION['flash_old_guest'] = $msg['guest'];
                    }else{
                        //both email and name filled
                        if (getenv('CAPTCHA') == 'recaptcha') {
                            //verify the reCaptcha
                            $recaptcha = new \ReCaptcha\ReCaptcha(getenv('RECAPTCHA_SECRET_KEY'));
                            $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                            ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                            if ($resp->isSuccess()) {
                                $verified = true;
                            } else {
                                // CHANGE THE DANGER FATAL ERROR CAPTCHA MESSAGE FOR YOUR SUBSCRIBERS HERE
                                $reCaptcha_errors ='<br><ul>'; 
                                for ($i=0; $i < sizeof($resp->getErrorCodes()) ; $i++) { 
                                    $reCaptcha_errors .= '<li>'.$resp->getErrorCodes()[$i].'</li>';
                                }
                                $reCaptcha_errors .= '</ul>';
                                $message_type = 'danger';
                                $message_title_for_user = "Whoops! Fatal error at verifying the reCaptcha!";
                                $message_content_for_user = "Please kindly contact the developer for these errors: ".$reCaptcha_errors;
                                //--END CHANGE DANGER FATAL ERROR CAPTCHA MESSAGE FOR YOUR SUBSCRIBERS HERE
                            }
                        }else{
                            //verify the manual captcha
                            if ($_SESSION['answer'] == $_POST['answer'] )
                            {
                                $verified = true;
                            }
                            else
                            {
                                $verified = false;
                                //WRONG CAPTCHA
                                $_SESSION['flash_old_name'] = $msg['name'];
                                $_SESSION['flash_old_email'] = $msg['email'];
                                $_SESSION['flash_old_guest'] = $msg['guest'];
                                $_SESSION['flash_old_phone'] = $msg['phone'];
                                $_SESSION['flash_answer'] = 'Please solve the question correctly';

                                // CHANGE THE WARNING WRONG CAPTCHA MESSAGE FOR YOUR SUBSCRIBERS HERE
                                $message_type = getenv('MESSAGE_TYPE_ERROR_MANUAL_CAPTCHA');
                                $message_title_for_user = getenv('MESSAGE_TITLE_ERROR_MANUAL_CAPTCHA');
                                $message_content_for_user = getenv('MESSAGE_CONTENT_ERROR_MANUAL_CAPTCHA');
                                //--END CHANGE WARNING WRONG CAPTCHA MESSAGE FOR YOUR SUBSCRIBERS HERE
                            }
                        }
                    }
                    if ($verified) {
                        //CORRECT CAPTCHA
                        //check if the inserted email is already exist
                        $email_already_exist = $flatbase->read()->in(getenv('STORAGE_FILE_NAME'))->where('email', '=', $msg['email'])->first();
                        if ($email_already_exist) {
                            $_SESSION['flash_old_name'] = $msg['name'];
                                $_SESSION['flash_old_email'] = $msg['email'];
                                $_SESSION['flash_old_guest'] = $msg['guest'];
                                $_SESSION['flash_old_phone'] = $msg['phone'];

                            // CHANGE THE WARNING DUPLICATE EMAIL MESSAGE FOR YOUR SUBSCRIBERS HERE
                            $message_type = getenv('MESSAGE_TYPE_DUPLICATE_EMAIL');
                            $message_title_for_user = getenv('MESSAGE_TITLE_DUPLICATE_EMAIL');
                            $message_content_for_user = getenv('MESSAGE_CONTENT_DUPLICATE_EMAIL');
                            //--END CHANGE THE WARNING DUPLICATE EMAIL MESSAGE FOR YOUR SUBSCRIBERS HERE
                        }else{
                            if (getenv('MAIL_FUNCTION') == "TRUE") {
                            //send the email first
                            //Create a new PHPMailer instance
                                $mail = new PHPMailer;
                            //Set who the message is to be sent from
                                $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
                            //Set who the message is to be sent to
                                $mail->addAddress($msg['email'], $msg['name']);
                            //Set the subject line
                                $mail->Subject = getenv('MAIL_SUBJECT');
                            //Read an HTML message body from an external file, convert referenced images to embedded,
                            //convert HTML into a basic plain-text alternative body
                                $mail->msgHTML(file_get_contents(getenv('MAIL_FILE_NAME')), __DIR__);
                            //Send the message, check for errors
                                if (!$mail->send()) {
                                // CHANGE THE DANGER OF FAILURE AT SENDING THE EMAIL MESSAGE FOR YOUR SUBSCRIBERS HERE
                                    $message_type = 'danger';
                                    $message_title_for_user = "Whoops! Fatal error at sending the email!";
                                    $message_content_for_user = "Please kindly contact the developer for these errors: ".$mail->ErrorInfo;
                                //--END CHANGE DANGER OF FAILURE AT SENDING THE EMAIL MESSAGE FOR YOUR SUBSCRIBERS HERE
                                }
                            }
                        // if no errors - insert the subscriber to the file
                            $flatbase->insert()->in(getenv('STORAGE_FILE_NAME'))
                            ->set([
                                'id' => uniqid().uniqid(),
                                'name' => $msg['name'], 
                                'email' => $msg['email'],
                                'guest' => $msg['guest'], 
                                'phone' => $msg['phone']
                            ])
                            ->execute();
                        // CHANGE THE SUCCESS MESSAGE FOR YOUR SUBSCRIBERS HERE
                            $message_type = getenv('MESSAGE_TYPE_SUCCESS_SENDING');
                            $message_title_for_user = getenv('MESSAGE_TITLE_SUCCESS_SENDING');
                            $message_content_for_user = getenv('MESSAGE_CONTENT_SUCCESS_SENDING');
                        //--END CHANGE THE SUCCESS MESSAGE FOR YOUR SUBSCRIBERS HERE
                        }
                    }
                    //and then redirect
                    $_SESSION['flash_popup'] = [
                        'type' => $message_type,
                        'message_title' => $message_title_for_user,
                        'message_content' => $message_content_for_user
                    ]; 
                    exit(header("Location: index.php"));
                } else {
                    // Log this as a warning and keep an eye on these attempts
                    header('HTTP/1.0 501 Not Implemented');
                    die();
                }
            } 
            break;

            case "delete":
            // logic for DELETE here
            if (!empty($_POST['csrf_token'])) {
                if (hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
                    // Proceed to process the form data
                    $err = '';
                    if (!$msg['id']) $err.="Whoops! Something wrong with the ID of the subscriber. Contact the developer!<br>";
                    // if no errors - delete the subscriber from the file
                    $flatbase->delete()->in(getenv('STORAGE_FILE_NAME'))->where('id', '==', $msg['id'])->execute();
                    //and then redirect
                    Header("Location: ".getenv('STORAGE_VIEW_FILE_NAME')); 
                    exit();
                } else {
                     // Log this as a warning and keep an eye on these attempts
                    header('HTTP/1.0 501 Not Implemented');
                    die();
                }
            } 
            break;

            default:
            header('HTTP/1.0 501 Not Implemented');
            die();
        }
    }
    ob_flush();