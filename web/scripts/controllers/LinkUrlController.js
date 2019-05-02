angular.module('e-homework').controller('LinkUrlController', function($scope, $compile, $cookies, $filter, $state, HTTPService, IndexOverlayFactory) {
	
	IndexOverlayFactory.overlayShow();
	
	$scope.$parent.menu_selected = 'relatelink';
	
	$scope.load = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            console.log(result);
            $scope.LinkUrl = result.data.DATA.LinkUrl;
            IndexOverlayFactory.overlayHide();
        });
    }

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

            
            
        });
    }

    $scope.loadMenu('menu/list');
    $scope.load('linkurlView');

});	