<?php

    namespace App\Controller;
    
    use App\Service\LoginService;
    use App\Controller\Mailer;
    use App\Service\EmailService;
    use App\Service\UserRoleService;
    use App\Service\MenuService;
    

    class LoginController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function forgotPassword($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $username = $loginObj['obj']['Username'];
                
                $newPassword = LoginService::forgotPassword($username);
                // Send mail
                if($newPassword != 'invalid'){

                    // Get e-mail hosting for send
                    $email_settings = EmailService::getEmailForgotPassword();
                    // sent mail
                    $mailer = new Mailer;
                    $mailer->setMailHost('smtp.gmail.com');
                    $mailer->setMailPort('465');
                    $mailer->setMailUsername($email_settings->email);
                    $mailer->setMailPassword($email_settings->password);
                    $mailer->setSubject("แจ้งรหัสผ่านใหม่สำหรับการเข้าใช้งานระบบ ACFS");
                    $mailer->isHtml(true);
                    $mailer->setHTMLContent($this->generateForgotPasswordMailContent($username, $newPassword));
                    $mailer->setReceiver($username);
                    $res = $mailer->sendMail();
                    if($res){
                        $this->logger->info('Sent mail Room success');
                    }else{
                        // print_r($res);
                        // exit;
                        $this->logger->info('Sent mail Room failed' . $res);
                    }

                    $this->data_result['DATA'] = $result;
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ไม่พบผู้ใช้งาน';    
                }

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        private function generateForgotPasswordMailContent($userEmail, $newPassword){
            return "รหัสผ่านใหม่สำหรับการเข้าใช้งานระบบของผู้ใช้งานชื่อ " . $userEmail . " คือ <b>" . $newPassword ."</b>"
                    . "<br><br><b>**นี่คืออีเมลที่สร้างขึ้นจากระบบอัตโนมัติ กรุณาอย่าตอบกลับ e-mail ฉบับนี้**</b>";
        }
        
        public function authenticate($request, $response, $args){
    //         error_reporting(E_ERROR);
    // error_reporting(E_ALL);
    // ini_set('display_errors','On');
            try{
                $error_msg = '';

                $loginObj = $request->getParsedBody();
                $username = $loginObj['obj']['Username'];
                $password = $loginObj['obj']['Password'];
                
                $this->logger->info('Find by username : '. $username . " Password : " . $password);
                // System login
                $user = LoginService::authenticateAdmin($username , $password);    
                
                $this->logger->info($user);
                if(!empty($user[adminID])){
                    unset($user[password]);
                    
                    // Get menu in this user's group
                    //$menuList = LoginService::getMenuList($user['UserID']);                    
                    $user['userType'] = 'admin';
                    $this->data_result['DATA']['UserData'] = $user;
                    // $this->data_result['DATA']['MenuList'] = $menuList;
                }else{

                    // Check authen normal user
                    $user = LoginService::authenticate($username , $password);    
                    $user['userType'] = 'user';
                    if(!empty($user[userID])){
                        unset($user[password]);
                        $this->data_result['DATA']['UserData'] = $user;
                    }else{
                        $this->data_result['STATUS'] = 'ERROR';
                        $this->data_result['DATA'] = 'ไม่พบผู้ใช้งาน';    
                    }
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
            
        }

        public function authenticateAdmin($request, $response, $args){
    //         error_reporting(E_ERROR);
    // error_reporting(E_ALL);
    // ini_set('display_errors','On');
            try{
                $error_msg = '';

                $loginObj = $request->getParsedBody();
                $username = $loginObj['obj']['Username'];
                $password = $loginObj['obj']['Password'];
                
                $this->logger->info('Find Admin by username : '. $username . " Password : " . $password);
                // System login
                $user = LoginService::authenticateAdmin($username , $password);    
                
                $this->logger->info($user);
                if(!empty($user[AdminID])){
                    unset($user[Password]);
                    
                    // Get menu in this user's group
                    //$menuList = LoginService::getMenuList($user['UserID']);                    

                    $this->data_result['DATA']['UserData'] = $user;
                    $this->data_result['DATA']['MenuList'] = $menuList;
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ไม่พบผู้ใช้งาน';
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
            
        }

        public function register($request, $response, $args){
            try{
    //                     error_reporting(E_ERROR);
    // error_reporting(E_ALL);
    // ini_set('display_errors','On');
                $obj = $request->getParsedBody();
                $obj = $obj['obj'];
                // find duplicate id card
                $chkDuplicateIDCard = LoginService::checkDuplicateIDCard($obj['IDCard']);    
                $chkDuplicateMobile = LoginService::checkDuplicateMobile($obj['Mobile']);    
                if(empty($chkDuplicateIDCard[UserID]) && empty($chkDuplicateMobile[UserID])){
                    
                    // Add new member
                    $attendee = LoginService::registerMember($obj);   
                    if(!empty($attendee))
                    {   
                        // $smsContent = 'รหัสเข้าใช้งาน Wifi ของหมายเลขบัตรประชาชน ' . $attendee['IDCard'] . ' คือ ' . $attendee['Wifi'];
                        $smsContent = 'ขอบคุณที่ลงทะเบียนเข้าร่วมงานเทศกาลโคนมแห่งชาติ 2561';
                        $smsResult = $this->sendSMS($attendee['Mobile'], $smsContent);
                        // $smsResult = true;
                        $this->logger->info('SMS Result : ' . $smsResult); 
                        if($smsResult !== false){
                            $this->logger->info('SMS Result Success '); 
                            // $this->data_result['DATA'] = 'กรุณาตรวจสอบรหัสเข้าใช้งาน Wifi ที่ส่งไปยังเบอร์โทรศัพท์หมายเลข ' . $attendee['Mobile'];
                            $this->data_result['DATA'] = 'ขอบคุณที่ลงทะเบียนเข้าร่วมงานเทศกาลโคนมแห่งชาติ 2561';
                        }else{
                            $this->logger->info('SMS Result Failed '); 
                            $this->data_result['STATUS'] = 'ERROR';
                            // $this->data_result['DATA'] = 'ไม่สามารถรหัสเข้าใช้งาน Wifi ไปยังเบอร์โทรศัพท์ได้ ';//คลิ้กที่นี่ เพื่อรับรหัสผ่านอีกครั้ง';
                        }   
                    }
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    if(!empty($chkDuplicateIDCard[UserID]) && !empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขบัตรประชาชน (' . trim($obj['IDCard']) . ') และหมายเลขโทรศัพท์ (' . trim($obj['Mobile']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    else if(!empty($chkDuplicateIDCard[UserID]) && empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขบัตรประชาชน (' . trim($obj['IDCard']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    else if(empty($chkDuplicateIDCard[UserID]) && !empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขโทรศัพท์ (' . trim($obj['Mobile']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function registration($request, $response, $args){
            try{
                $obj = $request->getParsedBody();
                $obj = $obj['obj'];
                // find duplicate id card
                $chkDuplicateIDCard = LoginService::checkDuplicateIDCard($obj['IDCard']);    
                $chkDuplicateMobile = LoginService::checkDuplicateMobile($obj['Mobile']);    
                if(empty($chkDuplicateIDCard[UserID]) && empty($chkDuplicateMobile[UserID])){
                    
                    // Add new member
                    $obj['RegisterType'] = 'MANUAL';
                    $attendee = LoginService::registerMember($obj);   
                    if(!empty($attendee))
                    {   
                        // $smsContent = 'รหัสเข้าใช้งาน Wifi ของหมายเลขบัตรประชาชน ' . $attendee['IDCard'] . ' คือ ' . $attendee['Wifi'];
                        $smsContent = 'ขอบคุณที่ลงทะเบียนเข้าร่วมงานเทศกาลโคนมแห่งชาติ 2561';
                        $smsResult = $this->sendSMS($attendee['Mobile'], $smsContent);
                        // $smsResult = true;
                        $this->logger->info('SMS Result : ' . $smsResult); 
                        if($smsResult !== false){
                            $this->logger->info('SMS Result Success '); 
                            // $this->data_result['DATA'] = 'กรุณาตรวจสอบรหัสเข้าใช้งาน Wifi ที่ส่งไปยังเบอร์โทรศัพท์หมายเลข ' . $attendee['Mobile'];
                            $this->data_result['DATA'] = 'ขอบคุณที่ลงทะเบียนเข้าร่วมงานเทศกาลโคนมแห่งชาติ 2561';
                            $this->data_result['UserData'] = $attendee;
                        }else{
                            $this->logger->info('SMS Result Failed '); 
                            $this->data_result['STATUS'] = 'ERROR';
                            // $this->data_result['DATA'] = 'ไม่สามารถรหัสเข้าใช้งาน Wifi ไปยังเบอร์โทรศัพท์ได้ ';//คลิ้กที่นี่ เพื่อรับรหัสผ่านอีกครั้ง';
                        }   
                    }
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    if(!empty($chkDuplicateIDCard[UserID]) && !empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขบัตรประชาชน (' . trim($obj['IDCard']) . ') และหมายเลขโทรศัพท์ (' . trim($obj['Mobile']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    else if(!empty($chkDuplicateIDCard[UserID]) && empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขบัตรประชาชน (' . trim($obj['IDCard']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    else if(empty($chkDuplicateIDCard[UserID]) && !empty($chkDuplicateMobile[UserID]))
                    {
                        $this->data_result['DATA'] = 'หมายเลขโทรศัพท์ (' . trim($obj['Mobile']) . ') มีอยู่ในระบบแล้ว';    
                    }
                    
                }            
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function saveFrontRegister($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $obj = $loginObj['obj'];

                $chkDuplicateName = LoginService::checkDuplicateName($obj);    
                
                if(empty($chkDuplicateName)){
                    
                    $user = LoginService::saveFrontRegister($obj); 
                    
                    if(!empty($user['UserID'])){
                        unset($user['Password']);
                        unset($user['WifiUser']);
                        unset($user['WifiPassword']);
                        $this->data_result['DATA'] = $user;
                    }else{
                        $this->data_result['STATUS'] = 'ERROR';
                        $this->data_result['DATA'] = 'ไม่สามารถบันทึกได้ กรุณาลองใหม่ภายหลัง';
                    }
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ไม่สามารถบันทึกได้ เนื่องจากมีชื่อและนามสกุลนี้อยู่ในระบบแล้ว';
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);

            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }        

        public function findWithIDCard($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $idCard = $loginObj['obj']['IDCard'];
                
                $this->logger->info('Find by id card : '. $idCard );
                // System login
                $user = LoginService::findWithIDCard($idCard);    
                
                $this->logger->info($user);
                if(!empty($user[UserID])){
                    unset($user[Password]);
                    
                    // Get menu in this user's group
                    //$menuList = LoginService::getMenuList($user['UserID']);       
                    $user['CitizenID'] = $user['IDCard'];
                    $user['NameTH_FirstName'] = $user['Firstname'];
                    $user['NameTH_SurName'] = $user['Lastname'];
                    $user['Birthday'] = date('d/m/Y', strtotime($user['Birthdate']));
                    $user['BirthDate'] = date('d/m/Y', strtotime($user['Birthdate']));
                    $this->data_result['DATA']['UserData'] = $user;
                    $this->data_result['DATA']['UserDataEncode'] = base64_encode($user);
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ไม่พบผู้ใช้งาน กรุณาลงทะเบียน';
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }   

        public function saveAdmin($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $obj = $loginObj['obj'];
                
                // check duplicate Username
                $duplicate = LoginService::checkDuplicateAdmin($obj['Username']);    
                if(!$duplicate){
                    // add new admin
                    $result = LoginService::saveAdmin($obj);    
                    
                    if($result){
                        $this->data_result['DATA'] = 'Success';
                    }else{
                        $this->data_result['STATUS'] = 'ERROR';
                        $this->data_result['DATA'] = 'กรุณาลองใหม่ภายหลัง';
                    }
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ชื่อผู้ใช้งานนี้มีในระบบอยู่แล้ว';
                }

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        } 

        public function registerMailSubscribe($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $obj = $loginObj['obj'];
                
                // check duplicate Username
                $duplicate = LoginService::checkDuplicateMailSubscribe($obj['email']);    
                if(!$duplicate){
                    // add new admin
                    $data = [];
                    $data['email'] = $obj['email'];
                    $data['actives'] = 'Y';
                    $data['create_date'] = date('Y-m-d H:i:s');

                    $result = LoginService::addMailSubscribe($data);    
                    
                    if($result){
                        $this->data_result['DATA'] = 'Success';
                    }else{
                        $this->data_result['STATUS'] = 'ERROR';
                        $this->data_result['DATA'] = 'กรุณาลองใหม่ภายหลัง';
                    }
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA']['TH'] = 'email นี้มีในระบบอยู่แล้ว';
                    $this->data_result['DATA']['EN'] = 'This email is already exist.';
                }

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }   

        public function loginMailSubscribe($request, $response, $args){
            try{
                $loginObj = $request->getParsedBody();
                $obj = $loginObj['obj'];
                
                // check duplicate Username
                $DATA = LoginService::checkMailSubscribeLogin($obj['email']);    
                if(!empty($DATA)){
                    
                    // find most visit menu
                    $menu = MenuService::getMostVisit($obj['email']);
                    $this->data_result['DATA'] = $DATA;
                    $this->data_result['DATA']['Menu'] = $menu;
                    
                }else{
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA']['TH'] = 'ไม่พบ email นี้ในระบบ กรุณาลงทะเบียนก่อนเข้าใช้งาน';
                    $this->data_result['DATA']['EN'] = 'Email not found, please register.';
                }

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }   

        public function authenticateBackend($request, $response, $args){
    //         error_reporting(E_ERROR);
    // error_reporting(E_ALL);
    // ini_set('display_errors','On');
            try{
                $error_msg = '';

                $loginObj = $request->getParsedBody();
                $username = $loginObj['obj']['Username'];
                $password = $loginObj['obj']['Password'];
                
                $this->logger->info('Find by username : '. $username . " Password : " . $password);
                // System login
                $user = LoginService::authenticateBackend($username , $password);    
                
                $this->logger->info($user);
                if(!empty($user[id])){
                    unset($user[password]);
                    
                    // Get menu in this user's group
                    //$menuList = LoginService::getMenuList($user['UserID']);                    
                    $this->data_result['DATA']['UserData'] = $user;

                    // Load menu permission
                    $MenuPermission = UserRoleService::getDetails($user['role']);

                    $this->data_result['DATA']['MenuPermission'] = $MenuPermission;
                }else{
                
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'ไม่พบผู้ใช้งาน';    
                
                }
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
            
        }

        private function diff($date1, $date2) {
            $to_time = strtotime($date2);
            $from_time = strtotime($date1);
            return round(abs($to_time - $from_time) / 60,2);
        }
        
        private function sendSMS($receiver, $content){
            $sms = new SMSController($this->logger, $this->sms);
            $sms->setSmsReceiver($receiver);
            $sms->setSmsDesc($content);
            $sms->sendSMS();
        }
    }


?>