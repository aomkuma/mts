angular.module('e-homework').controller('NewsController', function($scope, $compile, $cookies, $filter, $state, $routeParams, HTTPService, IndexOverlayFactory) {
    $scope.$parent.menu_selected = 'news';
    $scope.$parent.menu_selected_th = 'ข่าว';
    $scope.NEWS_TYPE = $routeParams.NEWS_TYPE;
    console.log($routeParams.NEWS_TYPE);
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

    $scope.loadNewsCategoryList = function(){
        HTTPService.clientRequest('news/category/list', null).then(function(result){
            console.log(result);
            $scope.NewsCategoryList = result.data.DATA.DataList;
            IndexOverlayFactory.overlayHide();

            
            $scope.NEWS_TYPE = $scope.NewsCategoryList[0].category_name;
            
        });
    }

    $scope.load = function(action, news_type, actives){
        var currentDate = makeSQLDate(new Date());
        var params = {'news_type' : news_type, 'actives': actives, 'currentDate' : currentDate};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.NewsList = result.data.DATA.News;
            IndexOverlayFactory.overlayHide();
        });
    }
    
    $scope.changeNewsType = function(news_type){
        $scope.NEWS_TYPE = news_type;
        $scope.load('news', $scope.NEWS_TYPE, 'Y');
    }

    $scope.getThaiDate = function(date){
        console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    
    $scope.loadMenu('menu/list');
    $scope.load('news', $scope.NEWS_TYPE, 'Y');
    $scope.loadNewsCategoryList();
});