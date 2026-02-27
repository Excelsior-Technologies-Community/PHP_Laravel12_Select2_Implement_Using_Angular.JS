# PHP_Laravel12_Select2_Implement_Using_Angular.JS

---
<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/AngularJS-1.8-DD0031?style=for-the-badge&logo=angularjs&logoColor=white">
  <img src="https://img.shields.io/badge/Select2-Multiple%20Dropdown-2E7D32?style=for-the-badge">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-3.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white">
</p>


##  Overview

This project demonstrates how to build a **Many-to-Many CRUD application**
using **Laravel 12** as the backend and **AngularJS 1.8** as the frontend.

The application manages **Products** and **Colors**, where:
- One Product can have multiple Colors
- One Color can belong to multiple Products

Technologies used:
- Laravel 12
- AngularJS 1.8
- Select2 (Multiple Select Dropdown)
- Bootstrap 3
- MySQL

---

##  Features

- Product CRUD (Create, Read, Update, Delete)
- Color CRUD
- Many-to-Many relationship between Products & Colors
- Select2 multiple dropdown integration
- AngularJS Single Page Application (SPA)
- Bootstrap based UI
- Pagination support

---

##  Folder Structure

```
laravel-angular-select2/
├── app/
│   ├── Models/
│   │   ├── Product.php
│   │   └── Color.php
│   └── Http/Controllers/
│       ├── ProductController.php
│       └── ColorController.php
│
├── database/
│   └── migrations/
│       ├── create_products_table.php
│       ├── create_colors_table.php
│       └── create_color_product_table.php
│
├── public/
│   ├── app/
│   │   ├── routes.js
│   │   ├── packages/
│   │   │   └── dirPagination.js
│   │   └── controllers/
│   │       ├── ProductController.js
│   │       └── ColorController.js
│   └── templates/
│       ├── home.html
│       ├── products.html
│       ├── colors.html
│       └── dirPagination.html
│
├── resources/
│   └── views/
│       └── app.blade.php
│
├── routes/
│   └── web.php
│
└── README.md
```

---

## STEP 1: Laravel Installation

```bash
composer create-project laravel/laravel laravel-angular-select2
```

Open browser:

```
http://127.0.0.1:8000
```

---

## STEP 2: Database Setup

Create database:

```sql
CREATE DATABASE angular;
```

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=angular
DB_USERNAME=root
DB_PASSWORD=
```

---

## STEP 3: Migrations

### 1️⃣ Products Table

```bash
php artisan make:migration create_products_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### 2️⃣ Colors Table

```bash
php artisan make:migration create_colors_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
```

### 3️⃣ Pivot Table (IMPORTANT)

```bash
php artisan make:migration create_color_product_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('color_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('color_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('color_product');
    }
};
```

Run migrations:

```bash
php artisan migrate
```

---

## STEP 4: Models

### app/Models/Product.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['title','price'];

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }
}
```

### app/Models/Color.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
```

---

## STEP 5: Controllers (Laravel)

### ProductController

```bash
php artisan make:controller ProductController
```
app/Http/Controllers/ProductController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('colors')->paginate(5);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->only('title','price'));
        $product->colors()->sync($request->color_ids ?? []);
        return $product->load('colors');
    }

    public function edit($id)
    {
        return Product::with('colors')->find($id);
    }

    public function update(Request $request,$id)
    {
        $product = Product::find($id);
        $product->update($request->only('title','price'));
        $product->colors()->sync($request->color_ids ?? []);
        return $product->load('colors');
    }

    public function destroy($id)
    {
        Product::find($id)->delete();
        return response()->json(true);
    }
}
```

### ColorController

```bash
php artisan make:controller ColorController
```
app/Http/Controllers/ColorController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return Color::all();
    }

    public function store(Request $request)
    {
        return Color::create($request->only('name'));
    }

    public function destroy($id)
    {
        Color::find($id)->delete();
        return response()->json(true);
    }
}
```

---

## STEP 6: Routes

routes/web.php
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;

Route::get('/', function () {
    return view('app');
});

Route::resource('products', ProductController::class);
Route::resource('colors', ColorController::class);
```

---

## STEP 7: Main View (Angular Root)

resources/views/app.blade.php

```html
<!DOCTYPE html>
<html lang="en" ng-app="mainApp">
<head>
    <meta charset="UTF-8">
    <title>Laravel + AngularJS + Select2</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js"></script>

    <script src="{{ asset('app/packages/dirPagination.js') }}"></script>
    <script src="{{ asset('app/routes.js') }}"></script>
    <script src="{{ asset('app/controllers/ProductController.js') }}"></script>
    <script src="{{ asset('app/controllers/ColorController.js') }}"></script>
</head>

<body>
<nav class="navbar navbar-default">
    <div class="container">
        <ul class="nav navbar-nav">
            <li><a href="#/">Home</a></li>
            <li><a href="#/products">Products</a></li>
            <li><a href="#/colors">Colors</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <ng-view></ng-view>
</div>
</body>
</html>
```

---

## STEP 8: Angular Routes

public/app/routes.js

```js
var app = angular.module('mainApp', [
    'ngRoute',
    'angularUtils.directives.dirPagination'
]);

app.config(function ($routeProvider, $locationProvider) {

    $locationProvider.hashPrefix('');

    $routeProvider
        .when('/', { templateUrl: 'templates/home.html' })
        .when('/products', { templateUrl: 'templates/products.html' })
        .when('/colors', { templateUrl: 'templates/colors.html' })
        .otherwise({ redirectTo: '/' });
});
```

---

## STEP 9: Angular Controllers

### public/app/controllers/ProductController.js

```js
app.controller('ProductController', function ($scope, $http, $timeout) {

    $scope.data = [];
    $scope.colors = [];
    $scope.form = {};
    $scope.totalItems = 0;

    function loadProducts(page = 1) {
        $http.get('/products?page=' + page).then(function (res) {
            $scope.data = res.data.data;
            $scope.totalItems = res.data.total;
        });
    }
    loadProducts();

    function loadColors(callback) {
        $http.get('/colors').then(function (res) {
            $scope.colors = res.data;
            if (callback) callback();
        });
    }

    $scope.pageChanged = function (newPage) {
        loadProducts(newPage);
    };

    $('#create-product').on('shown.bs.modal', function () {
        loadColors(function () {
            $timeout(function () {
                $('#colorSelectCreate').select2({
                    dropdownParent: $('#create-product'),
                    width: '100%',
                    data: $scope.colors.map(c => ({id:c.id,text:c.name}))
                });
            },0);
        });
    });

    $scope.saveAdd = function () {
        $scope.form.color_ids = $('#colorSelectCreate').val();
        $http.post('/products', $scope.form).then(function () {
            loadProducts(1);
            $('#create-product').modal('hide');
            $scope.form = {};
        });
    };

    $scope.edit = function (id) {
        $http.get('/products/' + id + '/edit').then(function (res) {
            $scope.form = res.data;
            $('#edit-product').modal('show');

            loadColors(function () {
                $timeout(function () {
                    $('#colorSelectEdit').select2({
                        dropdownParent: $('#edit-product'),
                        width: '100%',
                        data: $scope.colors.map(c => ({id:c.id,text:c.name}))
                    });

                    $('#colorSelectEdit')
                        .val(res.data.colors.map(c => c.id))
                        .trigger('change');
                },0);
            });
        });
    };

    $scope.saveEdit = function () {
        $scope.form.color_ids = $('#colorSelectEdit').val();
        $http.put('/products/' + $scope.form.id, $scope.form).then(function () {
            loadProducts(1);
            $('#edit-product').modal('hide');
        });
    };

    $scope.remove = function (item,index) {
        if (confirm('Delete?')) {
            $http.delete('/products/' + item.id).then(function () {
                $scope.data.splice(index,1);
            });
        }
    };
});
```

### public/app/controllers/ColorController.js

```js
app.controller('ColorController', function ($scope, $http) {

    $scope.data = [];
    $scope.form = {};

    function load() {
        $http.get('/colors').then(res => {
            $scope.data = res.data;
        });
    }
    load();

    $scope.saveAdd = function () {
        $http.post('/colors', $scope.form).then(res => {
            $scope.data.push(res.data);
            $('#create-color').modal('hide');
            $scope.form = {};
        });
    };

    $scope.remove = function (c,index) {
        if (confirm('Delete?')) {
            $http.delete('/colors/' + c.id).then(() => {
                $scope.data.splice(index,1);
            });
        }
    };
});
```

---

## STEP 10: Templates

### public/templates/home.html

```html
<h2 class="text-center">Welcome Dashboard</h2>
```

### public/templates /products.html

```html
<!-- Product Controller Wrapper -->
<div ng-controller="ProductController">

<!-- Page Header -->
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <!-- Page Title -->
            <h1>Product Management</h1>
        </div>

        <div class="pull-right" style="padding-top:30px">
            <!-- Open Create Product Modal -->
            <button class="btn btn-success"
                    data-toggle="modal"
                    data-target="#create-product">
                Create New
            </button>
        </div>
    </div>
</div>

<!-- Product Table -->
<table class="table table-bordered pagin-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Product Title</th>
            <th>Price</th>
            <th>Colors</th>
            <th width="220px">Action</th>
        </tr>
    </thead>

    <tbody>
        <!-- Loop through products -->
        <tr ng-repeat="value in data">
            <td>{{$index + 1}}</td>
            <td>{{value.title}}</td>
            <td>{{value.price}}</td>

            <!-- Product Colors -->
            <td>
                <span class="label label-info"
                      ng-repeat="c in value.colors"
                      style="margin-right:5px">
                    {{c.name}}
                </span>
            </td>

            <!-- Action Buttons -->
            <td>
                <button class="btn btn-primary"
                        ng-click="edit(value.id)">
                    Edit
                </button>

                <button class="btn btn-danger"
                        ng-click="remove(value,$index)">
                    Delete
                </button>
            </td>
        </tr>
    </tbody>
</table>

<!-- Pagination Controls -->
<dir-pagination-controls
    class="pull-right"
    on-page-change="pageChanged(newPageNumber)"
    template-url="templates/dirPagination.html">
</dir-pagination-controls>


<!-- ================= CREATE PRODUCT MODAL ================= -->

<div class="modal fade" id="create-product">
    <div class="modal-dialog">
        <div class="modal-content">

            <form ng-submit="saveAdd()">
                <div class="modal-header">
                    <h4>Create Product</h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Product Title</label>
                        <input type="text"
                               class="form-control"
                               ng-model="form.title"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input type="number"
                               class="form-control"
                               ng-model="form.price"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Colors</label>
                        <select id="colorSelectCreate"
                                class="form-control"
                                multiple>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ================= EDIT PRODUCT MODAL ================= -->

<div class="modal fade" id="edit-product">
    <div class="modal-dialog">
        <div class="modal-content">

            <form ng-submit="saveEdit()">
                <input type="hidden" ng-model="form.id">

                <div class="modal-header">
                    <h4>Edit Product</h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Product Title</label>
                        <input type="text"
                               class="form-control"
                               ng-model="form.title"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input type="number"
                               class="form-control"
                               ng-model="form.price"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Colors</label>
                        <select id="colorSelectEdit"
                                class="form-control"
                                multiple>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>
            </form>

        </div>
    </div>
</div>

</div>

```

### public/templates /colors.html

```html
<!-- Color Controller Wrapper -->
<div ng-controller="ColorController">

<!-- Page Header -->
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <!-- Page Title -->
            <h1>Color Management</h1>
        </div>

        <div class="pull-right" style="padding-top:30px">
            <!-- Open Create Color Modal -->
            <button class="btn btn-success"
                    data-toggle="modal"
                    data-target="#create-color">
                Create New
            </button>
        </div>
    </div>
</div>

<!-- Color Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Color Name</th>
            <th width="180px">Action</th>
        </tr>
    </thead>

    <tbody>
        <!-- Loop through colors -->
        <tr ng-repeat="c in data">
            <td>{{$index + 1}}</td>
            <td>{{c.name}}</td>
            <td>
                <button class="btn btn-danger"
                        ng-click="remove(c,$index)">
                    Delete
                </button>
            </td>
        </tr>
    </tbody>
</table>


<!-- ================= CREATE COLOR MODAL ================= -->

<div class="modal fade" id="create-color">
    <div class="modal-dialog">
        <div class="modal-content">

            <form ng-submit="saveAdd()">
                <div class="modal-header">
                    <h4>Create Color</h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Color Name</label>
                        <input type="text"
                               class="form-control"
                               ng-model="form.name"
                               required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

</div>

```

### public/templates/dirPagination.html

```html
<ul class="pagination pull-right" ng-if="pagination.collectionLength > pagination.itemsPerPage">
    <li ng-class="{disabled: pagination.currentPage == 1}">
        <a href="" ng-click="pagination.currentPage = pagination.currentPage - 1">‹</a>
    </li>

    <li class="active">
        <a href="">{{pagination.currentPage}}</a>
    </li>

    <li ng-class="{disabled: pagination.currentPage * pagination.itemsPerPage >= pagination.collectionLength}">
        <a href="" ng-click="pagination.currentPage = pagination.currentPage + 1">›</a>
    </li>
</ul>

```

---

## STEP 11: Run Project

```bash
php artisan serve
```

Open:

```
http://127.0.0.1:8000
```

## OUTPUT:-

PRODUCT INDEX:-

<img width="1550" height="392" alt="Screenshot 2025-12-29 144454" src="https://github.com/user-attachments/assets/92a744fc-18f4-4a8e-bf47-05204e3de727" />

PRODUCT CREATE (Multiple Select2):-

<img width="747" height="483" alt="Screenshot 2025-12-29 143604" src="https://github.com/user-attachments/assets/1e09cf48-c3e9-4382-b2fe-5981457cb14e" />

PRODUCT EDIT (Multiple Select2):-

<img width="744" height="483" alt="Screenshot 2025-12-29 143628" src="https://github.com/user-attachments/assets/df6683de-db5d-4e65-96ec-40c6c9964653" />


COLOR INDEX:-

<img width="1529" height="601" alt="Screenshot 2025-12-29 143656" src="https://github.com/user-attachments/assets/5a42419d-a230-4d6e-a459-a33d83378500" />

COLOR CREATE:-

<img width="750" height="300" alt="Screenshot 2025-12-29 143646" src="https://github.com/user-attachments/assets/42e4766c-a873-474a-8db9-681d0dc4f08a" />
