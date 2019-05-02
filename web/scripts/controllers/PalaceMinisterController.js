angular.module('e-homework').controller('PalaceMinisterController', function($scope, $compile, $cookies, $filter, $state, HTTPService, IndexOverlayFactory) {
    IndexOverlayFactory.overlayShow();
    console.log('Hello ! Palace page');
	
    $scope.$parent.menu_selected = 'palace-minister';
    $scope.$parent.menu_selected_th = 'ทำเนียบรัฐมนตรี';

    $scope.loadMenu = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            //console.log(result);
            $scope.Menu = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
            $(document).ready(function(){
                // console.log('asd');
              $('a.test').on("click", function(e){
                // alert('aa');
                // $('ul.dropdown-menu').hide();
                $(this).next('ul').toggle();
                e.stopPropagation();
                e.preventDefault();
              });
            });

            // $scope.load('menu/page/get', $scope.ID);
            
        });
    }

    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.load = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            console.log(result);
            $scope.Palace = null;
            $scope.PalaceList = result.data.DATA.PalaceMinister;
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.edit = function(data){
        $scope.AttachFile = null;
        $scope.Palace = angular.copy(data);
        if($scope.Palace.position_start_date != null && $scope.Palace.position_start_date != undefined && $scope.Palace.position_start_date != ''){
            $scope.Palace.position_start_date = makeDate($scope.Palace.position_start_date);
        }
        if($scope.Palace.position_end_date != null && $scope.Palace.position_end_date != undefined && $scope.Palace.position_end_date != ''){
            $scope.Palace.position_end_date = makeDate($scope.Palace.position_end_date);
        }
    }

    $scope.save = function(action, Palace, AttachFile){
        // console.log($scope.Palace);
        IndexOverlayFactory.overlayShow();
        if(Palace.position_start_date != null && Palace.position_start_date != undefined && Palace.position_start_date != ''){
            Palace.position_start_date = makeSQLDate(Palace.position_start_date);
        }
        if(Palace.position_end_date != null && Palace.position_end_date != undefined && Palace.position_end_date != ''){
            Palace.position_end_date = makeSQLDate(Palace.position_end_date);
        }
       
        var params = {'Palace':Palace, 'AttachFile':AttachFile};
        HTTPService.uploadRequest(action, params).then(function(result){
            console.log(result);
            if(result.data.STATUS == 'OK'){
                $scope.AttachFile = null;
                $scope.load('palace-minister');
                IndexOverlayFactory.overlayHide();
            }else{
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.removeAttach = function(action, id){
        HTTPService.deleteRequest(action, id).then(function(result){
            $scope.load('palaces');
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.AttachFile = null;
    $scope.Palace = null;
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

    $scope.page_type = 'palace-minister';
    $scope.MenuName = getMenuName($scope.page_type);
    console.log($scope.MenuName);

    $scope.load('palace-minister');
    $scope.loadMenu('menu/list');
    $scope.getMenu('menu/get/type' ,$scope.page_type);

});