angular.module('e-homework').controller('PalaceMinisterController', function($scope, $compile, $cookies, $filter, $state, HTTPService, IndexOverlayFactory) {
    IndexOverlayFactory.overlayShow();
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    console.log('Hello ! PalaceMinister page');
	$scope.DEFAULT_LANGUAGE = 'TH';
    $scope.$parent.menu_selected = 'palace';

    $scope.addFiles = function(){
        $scope.FileList.push({'attachFile':null});
    }

    $scope.load = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            console.log(result);
            $scope.PalaceMinister = null;
            $scope.PalaceMinisterList = result.data.DATA.PalaceMinister;
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.edit = function(data){
        $scope.AttachFile = null;
        $scope.PalaceMinister = angular.copy(data);
        if($scope.PalaceMinister.position_start_date != null && $scope.PalaceMinister.position_start_date != undefined && $scope.PalaceMinister.position_start_date != ''){
            $scope.PalaceMinister.position_start_date = makeDate($scope.PalaceMinister.position_start_date);
        }
        if($scope.PalaceMinister.position_end_date != null && $scope.PalaceMinister.position_end_date != undefined && $scope.PalaceMinister.position_end_date != ''){
            $scope.PalaceMinister.position_end_date = makeDate($scope.PalaceMinister.position_end_date);
        }
        $scope.PAGE = 'UPDATE';
    }

    $scope.add = function(){
        $scope.AttachFile = null;
        $scope.PalaceMinister = null;
        $scope.PAGE = 'UPDATE';
    }

    $scope.cancelUpdate = function(){
        $scope.FileList = [];
        $scope.AttachFile = null;
        $scope.PalaceMinister = null;
        $scope.PAGE = 'MAIN';
    }

    $scope.save = function(action, PalaceMinister, AttachFile, AttachFileList){
        // console.log($scope.PalaceMinister);
        IndexOverlayFactory.overlayShow();
        if(PalaceMinister.position_start_date != null && PalaceMinister.position_start_date != undefined && PalaceMinister.position_start_date != ''){
            PalaceMinister.position_start_date = makeSQLDate(PalaceMinister.position_start_date);
        }
        if(PalaceMinister.position_end_date != null && PalaceMinister.position_end_date != undefined && PalaceMinister.position_end_date != ''){
            PalaceMinister.position_end_date = makeSQLDate(PalaceMinister.position_end_date);
        }
       
        var params = {'PalaceMinister':PalaceMinister, 'AttachFile':AttachFile, 'AttachFileList':AttachFileList};
        HTTPService.uploadRequest(action, params).then(function(result){
            console.log(result);
            $scope.PAGE = 'MAIN';
            if(result.data.STATUS == 'OK'){
                $scope.AttachFile = null;
                $scope.FileList = [];
                $scope.load('palace-minister');
                $scope.cancelUpdate();
                IndexOverlayFactory.overlayHide();
            }else{
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.removeAttach = function(action, id, index){
        HTTPService.deleteRequest(action, id).then(function(result){
            // $scope.load('palaces');
            $scope.PalaceMinister.AttachFiles.splice(index, 1);
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.FileList = [];
    $scope.AttachFile = null;
    $scope.PalaceMinister = {'position_th':'รัฐมนตรีว่าการกระทรวงการท่องเที่ยวและกีฬา','position_en':'รัฐมนตรีว่าการกระทรวงการท่องเที่ยวและกีฬา'};
    $scope.popup1 = {
        opened: false
    };

    $scope.popup2 = {
        opened: false
    };

    $scope.open1 = function() {
        $scope.popup1.opened = true;
    };

    $scope.open2 = function() {
        $scope.popup2.opened = true;
    };

    $scope.PAGE = 'MAIN';
    $scope.load('palace-minister');

});