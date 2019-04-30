<?php

    namespace App\Controller;
    
    use App\Service\PalaceMinisterService;
    use App\Service\AttachFileService;

    class PalaceMinisterController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getPalaceMinister($request, $response, $args){
            try{
                error_reporting(E_ERROR);
                error_reporting(E_ALL);
                ini_set('display_errors','On');
                $_PalaceMinister = PalaceMinisterService::getPalaceMinister();
                $_PalaceMinisterList = [];
                foreach($_PalaceMinister as $k => $v){
                    $_AttachFiles = AttachFileService::getAttachFiles($v['id'], 'palace-minister');
                    $v['AttachFiles'] = $_AttachFiles;
                    array_push($_PalaceMinisterList, $v);
                }

                $this->data_result['DATA']['PalaceMinister'] = $_PalaceMinisterList;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getPalaceMinisterCurrent($request, $response, $args){
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $_PalaceMinister = PalaceMinisterService::getPalaceMinisterCurrent();
                $_AttachFiles = AttachFileService::getAttachFiles($_PalaceMinister['id'], 'palace');
                $_PalaceMinister['AttachFiles'] = $_AttachFiles;

                $this->data_result['DATA']['PalaceMinister'] = $_PalaceMinister;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updatePalaceMinister($request, $response, $args){
            error_reporting(E_ERROR);
            error_reporting(E_ALL);
            ini_set('display_errors','On');
            $_WEB_IMAGE_PATH = 'cio-files/img';
            $_WEB_FILE_PATH = 'cio-files/files';
            
            try{
                $params = $request->getParsedBody();

                $_PalaceMinister = $params['obj']['PalaceMinister']; 
                
                // Update Attach files
                $files = $request->getUploadedFiles();
                $f = $files['obj']['AttachFile'];
                // print_r($f);
                // exit;
                if($f != null){
                    if($f->getClientFilename() != ''){
                        // Unset old image if exist
                        $delete_file_path = '../../' . $_WEB_IMAGE_PATH . $_PalaceMinister['picture_path'];
                        unset($delete_file_path);

                        $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                        $FileName = date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                        $FilePath = $_WEB_IMAGE_PATH . '/palace-minister/'.$FileName;
                        
                        $_PalaceMinister['picture_name'] = $f->getClientFilename();
                        $_PalaceMinister['picture_path'] = $FilePath;
                        
                        $f->moveTo('../../' . $FilePath);
                    }        
                }

                // Save PalaceMinister 
                $id = PalaceMinisterService::updatePalaceMinister($_PalaceMinister);

                // Update Attach files
                $files = $request->getUploadedFiles();
                if($files != null){
                    foreach($files as $key => $val){
                        foreach($val['AttachFileList'] as $k => $v){
                            // print_r($v['attachFile']);
                            $f = $v['attachFile'];
                            // print_r($f);
                            if($f != null){
                                if($f->getClientFilename() != ''){
                                    $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                                    $FileName = $id . '_' . date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                                    $FilePath = $_WEB_FILE_PATH . '/palace-minister/'.$FileName;

                                    $AttachFile = ['parent_id'=>$id
                                                    ,'page_type'=>'palace-minister'
                                                    ,'file_name'=>$f->getClientFilename()
                                                    ,'file_path'=>$FilePath
                                                    ,'content_type'=>$f->getClientMediaType()
                                                    ,'file_size'=>number_format($f->getSize()/1024, 2)

                                                ];
                                    // print_r($AttachFile);exit;
                                    AttachFileService::addAttachFiles($AttachFile);
                                    $f->moveTo('../../' . $FilePath);
                                    
                                }
                            }
                        }
                    }
                }

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function removePicture($request, $response, $args){
            try{
                
                $id = $request->getAttribute('id');
                $_PalaceMinister = PalaceMinisterService::getPalaceMinister($id);
                $delete_file_path = '../../' . $_WEB_IMAGE_PATH . '/' . $_PalaceMinister['picture_path'];
                unset($delete_file_path);

                PalaceMinisterService::removePicture($id);
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }