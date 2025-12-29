// Color Controller
app.controller('ColorController', function($scope,$http){

    // Color list
    $scope.data = [];

    // Form object
    $scope.form = {};

    // Load colors
    function load(){
        $http.get('/colors').then(res=>{
            $scope.data = res.data;
        });
    }

    // Initial load
    load();

    // Save new color
    $scope.saveAdd = function(){
        $http.post('/colors',$scope.form).then(res=>{
            $scope.data.push(res.data);
            $('#create-color').modal('hide');
            $scope.form = {};
        });
    };

    // Delete color
    $scope.remove = function(c,index){
        if(confirm('Delete?')){
            $http.delete('/colors/'+c.id).then(()=>{
                $scope.data.splice(index,1);
            });
        }
    };
});
