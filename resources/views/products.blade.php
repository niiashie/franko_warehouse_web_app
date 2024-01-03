@extends('layouts.dashboard')


@section('pageCss')
 <link rel="stylesheet" href={{ asset('css/product.css') }} />
 <link rel="stylesheet" href={{ asset('css/setup.css') }} />
@endsection


@section('pageJs')
<script>
         
        function loadWarehouse(){
           var warehouseURL = document.getElementById('warehouseURL').innerHTML;
           window.location.replace(warehouseURL);
        }

        function loadInventory(){
           var inventoryURL = document.getElementById('inventoryURL').innerHTML;
           window.location.replace(inventoryURL);
        }

        function loadHome(){
            var homeURL = document.getElementById('homeURL').innerHTML;
            window.location.replace(homeURL);
        }
        function showProducts(){
              document.getElementById("addProductPopUp").style.display = "block";
        }

        var categoriesId = [];
        var categoriesName = [];
        var productIds = [];
        var productNames = [];
        var productOrigins = [];
        var productPrices = [];
        var currentProductEditId;
        var editProductName,editProductOrigin,editProductPrice;
        var selectedId,selectedProductId;

        $(document).ready(function() {
            //console.log(localStorage.getItem("username"));
            document.getElementById("selectedMenu").style.backgroundColor = "#dddddd";
            //getProductCategories();
           
            //Search Products filter
            $("#addBtnSearchEntry").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#productTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
      
        function showCategories(){
            document.getElementById("productBody").style.display = "none";
            document.getElementById("productCategoryCon").style.display = "block";
        }

       
        function closeAddProduct(){
            var productName = $("#addProductName").val();
            var productOrigin = $("#addProductOrigin").val();
            var productPrice = $("#addProductPrice").val();
            //var productCategory = $("#editCategory").val();
        
            document.getElementById("addProductName").value = "";
            document.getElementById("addProductOrigin").value = "";
            document.getElementById("addProductPrice").value = "";
            $("#editCategory").val("vol");
            document.getElementById("addProductPopUp").style.display = "none";
        }
        function exitcategories(){
            document.getElementById("productBody").style.display = "block";
            document.getElementById("productCategoryCon").style.display = "none";
        }
        
        function closeAddCategory(){
            document.getElementById("addCategoryPopUp").style.display = "none";
        }
        
        function showAddProductCategories(){
            document.getElementById("addCategoryPopUp").style.display = "block";
        }
        
        function closeEditCategory(){
            document.getElementById("editCategoryPopUp").style.display = "none";
            document.getElementById("editProductPopUp").style.display = "none";
        }

        

        function deleteClicked2(element){
            var userId = "{{ session()->get('user_id') }}";
            var role = "{{ session()->get('role') }}"
            if(role != "Manager"){
                showError("UnAuthorized","You are not allowed to delete ware houses");
            }
            else{
                Swal.fire({
                        title: 'Enter ID to proceed',
                        input: 'text',
                        inputAttributes: {
                            autocapitalize: 'off'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Proceed',
                        showLoaderOnConfirm: true,
                        preConfirm: (login) => {},
                        allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                        
                        if(result.value){
                            var input = result.value;
                           
                            if(input == userId){
                               
                                    Swal.fire({
                                            title: 'Delete Product',
                                            text: "Do you really want to delete this product",
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#0093E9',
                                            cancelButtonColor: '#d33',
                                            confirmButtonText: 'Delete'
                                            }).then((result) => {  
                                                if (result.value) {
                                                document.getElementById("loading").style.display = "block";
                                                var categoryDeleteRequest  = $.ajax({
                                                        url:"/deleteProduct",
                                                        method: "GET",
                                                        data:{
                                                            id:element,
                                                            "_token": "{{ csrf_token() }}"
                                                        }
                                                });
                                                categoryDeleteRequest.done(function(response, textStatus, jqXHR){
                                                    if(response == "Success"){
                                                        document.getElementById("loading").style.display = "none";
                                                        Swal.fire({
                                                                        title: 'Successfully deleted Product',
                                                                        text: "",
                                                                        icon: 'success',
                                                                        showCancelButton: false,
                                                                        confirmButtonColor: '#3085d6',
                                                                        confirmButtonText: 'Okay'
                                                                    }).then((result) => {
                                                                        if (result.value) {
                                                                            location.reload();
                                                            
                                                                        }
                                                                });
                                                    }
                                                    else{
                                                        document.getElementById("loading").style.display = "none";
                                                      showError("Category Deletion Failed",response);
                                                    }
                                                });
                                                }
                                            });
                            }else{
                                document.getElementById("loading").style.display = "none";
                                showError("UnAuthorized","Invalid user ID");
                            }
                        }
            });
            }
             
        }
        function editClicked2(element,name,origin,price){
                
                currentProductEditId = element

                editProductName = name;
                editProductOrigin = origin;
                editProductPrice = price;
        
                document.getElementById("editProductPopUp").style.display = "block";
                document.getElementById("editProductNameValue").innerHTML = name;
                document.getElementById("editProductOriginValue").innerHTML = origin;
                document.getElementById("editProductPriceValue").innerHTML = price;
        }

       
        function editClicked(element,oldValue){
        
                hideProductCategoryNewValueBox(); 
                document.getElementById("editCategoryPopUp").style.display = "block";
                selectedId = element;
                document.getElementById("productCategoryEditOldValue").innerHTML = oldValue;
            
        }
        function editCategoryValue(){
                
            var userId = "{{ session()->get('user_id') }}";
            var role = "{{ session()->get('role') }}"
            if(role != "Manager"){
                showError("UnAuthorized","You are not allowed to delete ware houses");
            }else{
                var newValue = $("#productCategoryEditBoxNewValue").val();
                if(newValue.length == 0){
                showError("Invalid Entry","Please enter new category name to proceed");
                }
                else{
                  Swal.fire({
                            title: 'Edit Category',
                            text: "Do you really want to edit this category",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#0093E9',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Edit'
                        }).then((result) => {  
                            if(result.value){
                                var categoryEditRequest  = $.ajax({
                                                        url:"/updateCategory",
                                                        method: "GET",
                                                        data:{
                                                            id:selectedId,
                                                            name:newValue,
                                                            "_token": "{{ csrf_token() }}"
                                                        }
                                });
        
                                categoryEditRequest.done(function(response, textStatus, jqXHR){
                                                    if(response == "Success"){
                                                    Swal.fire({
                                                                    title: 'Successfully editted category',
                                                                    text: "",
                                                                    icon: 'success',
                                                                    showCancelButton: false,
                                                                    confirmButtonColor: '#3085d6',
                                                                    confirmButtonText: 'Okay'
                                                                }).then((result) => {
                                                                    if (result.value) {
                                                                        //location.reload();
                                                                        //$("#productCategoryTableBody").empty();
                                                                       // getProductCategories();
                                                                        closeEditCategory();
                                                                    }
                                                            });
                                                    }
                                                    else{
                                                    showError("Category Deletion Failed",response);
                                                    }
                                                });
        
                            }
                        
                  });
                }
            }
             
               
        }

        function deleteClicked(element){
            var userId = "{{ session()->get('user_id') }}";
            var role = "{{ session()->get('role') }}"
            if(role != "Manager"){
                showError("UnAuthorized","You are not allowed to delete ware houses");
            }else{
                    Swal.fire({
                            title: 'Enter ID to proceed',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Proceed',
                            showLoaderOnConfirm: true,
                            preConfirm: (login) => {},
                            allowOutsideClick: () => !Swal.isLoading()
                            }).then((result) => {
                            
                            if(result.value){
                                var input = result.value;
                                if(input == userId){
                                    
            
                                        Swal.fire({
                                                title: 'Delete Category',
                                                text: "Do you really want to delete this category",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#0093E9',
                                                cancelButtonColor: '#d33',
                                                confirmButtonText: 'Delete'
                                                }).then((result) => {  
                                                    if (result.value) {
                                                        document.getElementById("loading").style.display = "block";
                                                    var categoryDeleteRequest  = $.ajax({
                                                            url:"/deleteCategory",
                                                            method: "GET",
                                                            data:{
                                                                id:element,
                                                                "_token": "{{ csrf_token() }}"
                                                            }
                                                    });
                                                    categoryDeleteRequest.done(function(response, textStatus, jqXHR){
                                                       
                                                        if(response == "Success"){
                                                            document.getElementById("loading").style.display = "none";
                                                            Swal.fire({
                                                                            title: 'Successfully deleted category',
                                                                            text: "",
                                                                            icon: 'success',
                                                                            showCancelButton: false,
                                                                            confirmButtonColor: '#3085d6',
                                                                            confirmButtonText: 'Okay'
                                                                        }).then((result) => {
                                                                            if (result.value) {
                                                                                location.reload();
                                                                            //$("#productCategoryTableBody").empty();
                                                                            // getProductCategories();
                                                                            
                                                                            }
                                                                    });
                                                            }
                                                        else{
                                                            document.getElementById("loading").style.display = "none";
                                                           showError("Category Deletion Failed",response);
                                                        }
                                                    });
                                                    }
                                                });
                                }else{
                                    showError("UnAuthorized","Invalid user ID");
                                }
                            }
                });
            }
            
        }

        function showError(title,message){
            Swal.fire({
                                icon: 'error',
                                title: title,
                                text: message
                    });
        }
        
        function showProductCategoryNewValueBox(){
            document.getElementById("productEditBoxVisibility").style.display = "none";
            document.getElementById("productEditBoxValueVisibility").style.display = "block";
            document.getElementById("productCategoryEditBoxNewValueBtn").style.display = "block";
        }
        
        function hideProductCategoryNewValueBox(){
            document.getElementById("productEditBoxVisibility").style.display = "block";
            document.getElementById("productEditBoxValueVisibility").style.display = "none";
            document.getElementById("productCategoryEditBoxNewValueBtn").style.display = "none";
        }

        function addProduct(){
            var productName = $("#addProductName").val();
            var productOrigin = $("#addProductOrigin").val();
            var productPrice = $("#addProductPrice").val();
            var productCategory = $("#editCategory").val();
            var warehouseId = "{{ session()->get('warehouse') }}";
        
            if(productName.length == 0){
                showError("Invalid Entry","Product Name Required");
            }
            else if(productOrigin.length == 0){
                showError("Invalid Entry","Product Origin Required");
            }
            else if(productPrice.length == 0){
                showError("Invalid Entry","Product Price Required");
            }
            else if(productCategory == "vol"){
                showError("Invalid Entry","Please Select Product Category");
            }
            else{
                document.getElementById("loading").style.display = "block";
                var request = $.ajax({
                    url: "/addProducts",
                    method: "GET",
                    data:{
                        name:productName,
                        origin:productOrigin,
                        price:productPrice,
                        category_id:productCategory,
                        warehouse: warehouseId ,
                        "_token": "{{ csrf_token() }}"
                    }
                });
                    request.done(function (response, textStatus, jqXHR){
                        if(response == "Success"){
                            document.getElementById("loading").style.display = "none";
                            Swal.fire({
                                    title: 'Successfully added product',
                                    text: "",
                                    icon: 'success',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Okay'
                                }).then((result) => {
                                        if (result.value) {
                                        location.reload();
                                                            
                                }
                            });
                        }else{
                            document.getElementById("loading").style.display = "none";
                            showError("Error",response);
                        }
                    });
                    request.fail(function (){
                        document.getElementById("loading").style.display = "none";
                       // Show error
                       showError("Error","Please ensure server is turned on");
                    });
            }
        }
        function productNameEditSwitch(){
            $("#editProductOldNameValueContainer").hide();
            $("#editProductNewValueContainer").show();
        }

        function hideProductNameEditSwitch(){
            $("#editProductOldNameValueContainer").show();
            $("#editProductNewValueContainer").hide();
        }
        
        function productOriginEditSwitch(){
            $("#editProductOldOriginValueContainer").hide();
            $("#editProductNewOriginValueContainer").show();
        }
        
        function hideProductOriginEditSwitch(){
            $("#editProductOldOriginValueContainer").show();
            $("#editProductNewOriginValueContainer").hide();
        }
        
        function hideProductPriceEditSwitch(){
            $("#editProductOldPriceValueContainer").show();
            $("#editProductNewPriceValueContainer").hide();
        }
        
        function productPriceEditSwitch(){
            $("#editProductOldPriceValueContainer").hide();
            $("#editProductNewPriceValueContainer").show();
        }

        function showSuccess(title,message){
            Swal.fire({
                                icon: 'success',
                                title: title,
                                text: message,
            });
        }

        function productEditOnClick(){
            
            var userId = "{{ session()->get('user_id') }}";
            var role = "{{ session()->get('role') }}"
            if(role != "Manager"){
                showError("UnAuthorized","You are not allowed to delete ware houses");
            }
            else{
               
                var productNamePost,productPricePost,productOriginPost;
        
                var newProductName = $("#editProductNameNewValueTxt").val();
                var newProductOrigin = $("#editProductOriginNewValueTxt").val();
                var newProductPrice = $("#editProductPriceNewValueTxt").val();
            
                if(newProductName.length == 0){
                    productNamePost =  editProductName;
                }else{
                    productNamePost = newProductName;
                }
                
                if(newProductOrigin.length == 0){
                    productOriginPost = editProductOrigin
                }
                else{
                    productOriginPost = newProductOrigin
                }
            
                if(newProductPrice.length == 0){
                    productPricePost = editProductPrice;
                }
                else{
                    productPricePost = newProductPrice;
                }
                document.getElementById("loading").style.display = "block";
                var request = $.ajax({
                    url: "/updateProduct",
                    method: "GET",
                    data:{
                        name:productNamePost,
                        origin:productOriginPost,
                        price:productPricePost,
                        id:currentProductEditId,
                        "_token": "{{ csrf_token() }}"
                    }
                });
        
                request.done(function (response, textStatus, jqXHR){
                        console.log(response);
                        if(response == "Success"){
                            document.getElementById("loading").style.display = "none";
                            Swal.fire({
                                    title: 'Successfully updated product',
                                    text: "",
                                    icon: 'success',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Okay'
                                }).then((result) => {
                                        if (result.value) {
                                        location.reload();
                                                            
                                }
                            });
                        }else{
                            document.getElementById("loading").style.display = "none";
                            showError("Error",response);
                        }
                });
        
        
                request.fail(function (){
                    document.getElementById("loading").style.display = "none";
                    // Show error
                    showError("Error","Please ensure server is turned on");
                });
            }

            
              
        
        
        }
      
 </script>
@endsection


@section('pageMenu')
<div class="sideMenuIconContainer" onclick="loadHome()">
    <img class="sideMenuCenterIcon" src={{ asset('icons/home2.png') }}>
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Home</label>
    </div>
    <label style="display:none" id="homeURL">{{ route('homePage') }}</label>
</div>
@if(session()->get('role') =='Accountant')    
    <div class="sideMenuIconContainer" onclick="loadStock()">
        <img class="sideMenuCenterIcon" src={{ asset('icons/stocks.png') }}>
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Stock</label>
        </div>
        <label style="display:none" id="stockURL">{{ route('stock') }}</label>
    </div>
@else
    <div class="sideMenuIconContainer" onclick="loadWarehouse()">
        <img class="sideMenuCenterIcon" src={{ asset('icons/boxes.png') }}>
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Warehouse</label>
        </div>
        <label style="display:none" id="warehouseURL">{{ route('warehousePage') }}</label>
    </div>    
@endif     
 <div class="sideMenuIconContainer" id="selectedMenu">
    <img class="sideMenuCenterIcon" src={{ asset('icons/product.png') }} >
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Products</label>
    </div>
 </div>
 <div class="sideMenuIconContainer" onclick="loadInventory()">
    <img class="sideMenuCenterIcon" src={{ asset('icons/box2.png') }}>
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Inventory</label>
    </div>
    <label style="display:none" id="inventoryURL">{{ route('inventoryPage') }}</label>
</div>
<div class="sideMenuIconContainer" onclick="loadTransactions()">
    <img class="sideMenuCenterIcon" src={{ asset('icons/transaction.png') }}>
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Transactions</label>
    </div>
    <label style="display:none" id="transactionURL">{{ route('transactionPage') }}</label>
</div>

<div class="sideMenuIconContainer" onclick="loadProformer()">
    <img class="sideMenuCenterIcon" src={{ asset('icons/invoice.png') }} >
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Proforma</label>
    </div>
    <label style="display:none" id="proformerURL">{{ route('profermerPage') }}</label>
</div>
<div class="sideMenuIconContainer" onclick="showLogOut()">
    <img class="sideMenuCenterIcon" src={{ asset('icons/logout.png') }}>
    <div class="sideMenuLeftLabel">
        <label class="sideMenuLeftLabelText">Logout</label>
    </div>
</div>
 
@endsection


@section('pageContent')

 <div id="productBody">
    <div class="productHead">
       <label class="productTitle">Products</label>
       <div class="addBtnCon" onclick="showProducts()">
          <img class="addBtnConIcon" src="/icons/plus.png">
          <label class="addBtnConLable">Add Product</label>
       </div>
       <div id="searchBtnCon">
          <img class="addBtnConIcon" src="/icons/ashSearch.png">
          <input id="addBtnSearchEntry" type="text" placeholder="Search Product..">
       </div>
    </div>
    <div class="productContent">
       <div class="tableContainer">
          <table id="productTable">
             <thead>
               <tr>
                 <th>Name</th>
                 <th>Origin</th>
                 <th>Category</th>
                 <th>Price</th>
                 <th>Edit</th>
                 <!--<th>Delete</th>-->
               </tr> 
            </thead> 
            <tbody id="tbody2">
               @foreach ($products as $row2)
                   <tr>
                       <td>{{ $row2->name }}</td>
                       <td>{{ $row2->origin }}</td>
                       <td>{{ $row2->category->name }}</td>
                       <td>&#8373 {{ $row2->price }}</td>
                       <td> <img src="../icons/pencil.png" style="width:20;height:20;" onclick="editClicked2('{{ $row2->id }}','{{ $row2->name }}','{{ $row2->origin }}','{{ $row2->price }}')"></td>
                       <!--<td> <img src="../icons/delete.png" style="width:20;height:20;" onclick="deleteClicked2('{{ $row2->id }}')"></td>-->
                   </tr>
               @endforeach
            </tbody>
           </table>
       </div>
        <div class="productCategoryContainer">
          <div class="productCategoryTopDivider"> </div>
            <div class="productCategory" onclick="showCategories()">
               <label class="productCategoryLable">Categories</label>
            </div>
        </div>
    </div>
 </div>

 <div id="productCategoryCon">
    <div class="productHead">
       <label class="productTitle">Products Categories</label>
       <div class="addBtnCon" onclick="showAddProductCategories()">
          <img class="addBtnConIcon" src="/icons/plus.png">
          <label class="addBtnConLable">Add Category</label>
       </div>
    </div>
    <div class="productContent">
       <div class="tableContainer">
          <table id="productCategoryTable">
             <thead>
               <tr>
                 <th>Category</th>
                 <!--<th>Edit</th>-->
                 <th>Delete</th>
               </tr> 
            </thead> 
            <tbody id="productCategoryTableBody">
              @foreach ($categories as $row)
                 <tr>
                   <td> {{ $row->name }} </td>
                   <!--<td> <img src="../icons/pencil.png" style="width:20;height:20;" onclick="editClicked('{{ $row->id }}','{{ $row->name }}')"></td>-->
                   <td> <img src="../icons/delete.png" style="width:20;height:20;" onclick="deleteClicked('{{ $row->id }}')"></td>
                </tr> 
              @endforeach
           </tbody>
          </table>
      
           
           
       </div>
       

        <div class="productCategoryContainer">
           <div class="productCategoryTopDivider"> </div>
          <div class="productCategory" onclick="exitcategories()">
             <label class="productCategoryLable">Products</label>
          </div>
      </div>

    </div>
 </div>
@endsection

@section('pageModals')
        @if($errors->any())
            <script>
                showError("Error","{{$errors->first()}}");
            </script>
        @endif
        @if(session()->has('categories_addition'))
            <script>
                showSuccess('Success','Product category successfully added');
            </script>
        @endif
        <div id="addProductPopUp">
        <div id="addProductPopUpCenter">
            <img onclick="closeAddProduct()" class="cancel" src= {{ asset('icons/cancel.png') }} >
            <div class="addProductTitleCon">
                <label class="popUpTitle">Add Product</label>
            </div>
            <input class="inputDesign" id="addProductName" type="text" placeholder="Product Name">
            <input class="inputDesign" id="addProductOrigin" type="text" placeholder="Product Origin">
            <input class="inputDesign" id="addProductPrice" type="number" step="0.01" placeholder="Product Price">
            <select class="selectDesign" id="editCategory">
                <option value="vol">Product Category </option>
                @foreach ( $categories as $row )
                  <option value="{{ $row->id }}">{{ $row->name }}</option>  
                @endforeach
            </select>
            <button class="buttonDesign" id="addProductBtn" onclick="addProduct()">Register Product</button>
        </div>
        </div>

        <div id="addCategoryPopUp">
        <div class="addCategoryPopUpCenter">
            <img onclick="closeAddCategory()" class="cancel" src= {{ asset('icons/cancel.png') }} >
            <div class="addProductTitleCon">
                <label class="popUpTitle">Add Category</label>
            </div>
            <form action="{{ route('addProductCategories') }}" method="POST">
                @csrf
                <input class="inputDesign" id="addCategoryName" name="categoryName" type="text" placeholder="Category Name">
                @error('categoryName')
                    <script>
                        showError('Error',"{{ $message }}");
                    </script>
                @enderror
                <button type="submit" class="buttonDesign" id="addCategoryBtn">Add Category</button>
            </form>
        </div>
        </div>

        <div id="editCategoryPopUp">
        <div class="addCategoryPopUpCenter">
            <img onclick="closeEditCategory()" class="cancel" src= {{ asset('icons/cancel.png') }}>
            <div class="addProductTitleCon">
                <label class="popUpTitle">Edit Category</label>
            </div>
            <div class="productCategoryEditBox" id="productEditBoxVisibility">
                <div class="bottomDivider"></div>
                <label class="editOldValue" id="productCategoryEditOldValue">Old Value</label>
                <img class="editChangeValueIcon" src="icons/pencil.png" onclick="showProductCategoryNewValueBox()">
            </div>
            <div class="productCategoryEditBox" id="productEditBoxValueVisibility">
                <input class="inputDesign" id="productCategoryEditBoxNewValue" placeholder="Enter new value">
                <img src="icons/close.png" class="editChangeValueIcon" onclick="hideProductCategoryNewValueBox()">
            </div>
            <button class="buttonDesign" id="productCategoryEditBoxNewValueBtn" onclick="editCategoryValue()">Update Category</button>
        </div>
        </div>

        <div id="editProductPopUp">
            <div class="addProductPopUpCenter">
                <img onclick="closeEditCategory()" class="cancel" src= {{ asset('icons/cancel.png') }}>
                <div class="addProductTitleCon">
                    <label class="popUpTitle">Edit Product</label>
                </div>
                <div id="editProductBox">
                    <div class="editProductBoxItem">
                        <div class="editProductBoxLabelLayout" id="editProductOldNameValueContainer">
                            <div class="bottomDivider"></div>
                            <label class="editOldValue" id="editProductNameValue">Old Value</label>
                            <img class="editChangeValueIcon" src="/icons/pencil.png" onclick="productNameEditSwitch()">
                        </div>
                        <div class="editProductBoxLabelHiddenLayout" id="editProductNewValueContainer">
                            <input class="inputDesign editProductEntry" id="editProductNameNewValueTxt" placeholder="Enter new product name">
                            <img src="/icons/close.png" class="editChangeValueIcon" onclick="hideProductNameEditSwitch()">
                        </div>
                    </div>
                    <div class="verticalSpacing"></div>
                    <div class="editProductBoxItem">
                        <div class="editProductBoxLabelLayout" id="editProductOldOriginValueContainer">
                            <div class="bottomDivider"></div>
                            <label class="editOldValue" id="editProductOriginValue">Old Value</label>
                            <img class="editChangeValueIcon" src="/icons/pencil.png" onclick="productOriginEditSwitch()">
                        </div>
                        <div class="editProductBoxLabelHiddenLayout" id="editProductNewOriginValueContainer">
                            <input class="inputDesign editProductEntry" id="editProductOriginNewValueTxt" placeholder="Enter new product origin">
                            <img src="/icons/close.png" class="editChangeValueIcon" onclick="hideProductOriginEditSwitch()">
                        </div>
                        
                    </div>
                    <div class="verticalSpacing"></div>

                    <div class="editProductBoxItem">
                    <div class="editProductBoxLabelLayout" id="editProductOldPriceValueContainer">
                        <div class="bottomDivider"></div>
                        <label class="editOldValue" id="editProductPriceValue">Old Value</label>
                        <img class="editChangeValueIcon" src="/icons/pencil.png" onclick="productPriceEditSwitch()">
                    </div>
                    <div class="editProductBoxLabelHiddenLayout" id="editProductNewPriceValueContainer">
                        <input class="inputDesign editProductEntry" type="number" id="editProductPriceNewValueTxt" placeholder="Enter new product price">
                        <img src="/icons/close.png" class="editChangeValueIcon" onclick="hideProductPriceEditSwitch()">
                    </div>              
                    </div>

                    <div id="editProductBtnBox">
                        <button class="buttonDesign" id="updateProductBtn" onclick="productEditOnClick()">Update Product</button>
                    </div>

                </div>
                </div>
            </div>
        </div>
        <div class="showLoading"id="loading">
            <div class="loader">Loading...</div>
        </div>
@endsection

