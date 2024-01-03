@extends('layouts.dashboard')

@section('pageJs')
   <script>
        var counter = 0;
        var warehouseObject = {};
    
        $(document).ready(function() {
      
            getMostTransacted();
            getLeastQuantities();

        });
       
        function getMostTransacted(){
            var xValues = [];
            var yValues = [];
            var barColors = ["red", "green","blue","orange","brown"];
                var request  = $.ajax({
                                url:"/homeValues",
                                type: "GET",
                                data:{"_token": "{{ csrf_token() }}"}
                });
                request.done(function(response, textStatus, jqXHR){
                    //var obj = JSON.parse(response);
                    //var objLength = obj.length; 
                    
                    var  newArray  = [];
                    if(response.length>5){
                        console.log("Greater than 5");
                        newArray = response.slice(0, 5)
                    }else{
                        newArray = response
                    }
                    
                    newArray.forEach(row => {
                           xValues.push(row['name']);
                           yValues.push(row['quantity']);
                        
                    });
                    new Chart("myChart", {
                        type: "bar",
                        data: {
                            labels: xValues,
                            datasets: [{
                            //backgroundColor: barColors,
                            data: yValues
                            }]
                        },
                        options: {
                            legend: {display: false},
                            title: {
                            display: true,
                            text: "Most Sold Products In Last 30 Days"
                            }
                        }
                    });
                  }
                );

                
        }
 
        function getLeastQuantities(){
            var aValues = [];
            var bValues = [];
            var request  = $.ajax({
                                url:"/leastQuantites",
                                type: "GET",
                                data:{"_token": "{{ csrf_token() }}"}
            });

            request.done(function(response, textStatus, jqXHR){

                var  newArray  = [];
                if(response.length>10){
                    console.log("Greater than 5");
                    newArray = response.slice(0, 10)
                }else{
                    newArray = response
                }

                newArray.forEach(row => {
                    aValues.push(row['name']);
                    bValues.push(row['quantity']);
                        
                });

                new Chart("myChart2", {
                        type: "bar",
                        data: {
                            labels: aValues,
                            datasets: [{
                            //backgroundColor: barColors,
                            data: bValues
                            }]
                        },
                        options: {
                            legend: {display: false},
                            title: {
                            display: true,
                            text: "Products With Least Quantities"
                            }
                        }
                });
                    
            });
        }


        function getCategoryDetail(e){
            var id2 = e.id;
            //var res = id2.charAt(id2.length-1);
            var categoryTxtId = id2+"name";
            var categoryTxt = document.getElementById(categoryTxtId).innerHTML;
           // alert(categoryTxt);
            location.href = "/productCategoty/"+categoryTxt;
        }
        
   </script>
@endsection

@section('pageCss')
    <link rel="stylesheet" href={{ asset('css/home.css') }} />
@endsection




@section('pageMenu')


    <div class="sideMenuIconContainer" id="selectedMenu">
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Home</label>
        </div>
        <img class="sideMenuCenterIcon" src={{ asset('icons/home2.png') }}>
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

    <div class="sideMenuIconContainer" onclick="loadProducts()">
        <img class="sideMenuCenterIcon" src={{ asset('icons/product.png') }} >
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Products</label>
        </div>
        <label style="display:none" id="productsURL">{{ route('productsPage') }}</label>
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
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Logout</label>
        </div>
        <img class="sideMenuCenterIcon" src={{ asset('icons/logout.png') }}>
    </div>


@endsection

@section('pageContent')
  
   

   <div id="mainContent">
       <div id="welcomeCon">
          <img id="rightLogo" src={{ asset('images/ware_house2.png') }}>
            @if(Session::has('name'))
              <label id="welcomeName">Welcome back {{ Session::get('name') }}</label> 
            @endif
            @if(Session::has('role'))
              <label id="welomeSubText">You are logged in as a {{ Session::get('role') }} of a registered warehouse.<br /> 
            Enter your data and transact your business we've got you covered.
             </label>
            @endif
            <label id="currentStockValue">Current Stock Value : </label>
            <label id="currentStockValueTxt">&#8373 {{ $totalStockValue }}</label>
       </div>
       <div id="productCategoriesContainer">
           <label id="productCategoriesTxt">Product Categories<label>
       </div>
       <div id="secondContainer">
            <div id="leftSideCashCon">
                <label id="cashRes"> Cash Results</label>
                <div id="cashTotalsCon">
                        <div class="cashTotalItem">
                            <label class="cashTitle">Total Transactions</label> 
                            <label class="cashValue">&#8373 {{ $totalTransaction }}</label>
                            <div class="divider"></div>
                        </div>
                        <div class="cashTotalItem">
                            <label class="cashTitle">Total Received Goods</label> 
                            <label class="cashValue">&#8373 {{ $totalReceivedGoods }}</label>
                            <div class="divider"></div>
                        </div>
                        <div class="cashTotalItem">
                            <label class="cashTitle">Transactions Today</label> 
                            <label class="cashValue">&#8373 {{ $todaysTransaction }}</label>
                            <div class="divider"></div>
                        </div>
                        <div class="cashTotalItem">
                            <label class="cashTitle">Received Goods Today</label> 
                            <label class="cashValue">&#8373 {{ $todaysReceivedGoods }}</label>
                            <div class="divider"></div>
                        </div>
                </div>
            </div>
            <div id= "rightSideMapCon">
                <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
            </div>
       </div>
       <div id="thirdContainer">
            <!--<canvas id="myChart2" style="width:100%;"></canvas>-->
            <div id="innerThirdContainer">
                <canvas id="myChart2" style="width:100%;height:300px;"></canvas>
            </div>
       </div>
       
   </div>
   @foreach ($productCategories['product_categories'] as $category => $obj)
       <script>
           var key = "{{ $category }}";
           var keyValue = "{{ $obj }}";
          

           var parentContainer = document.getElementById("productCategoriesContainer");
           var subItem =document.createElement("DIV");
           subItem.classList.add('productCategoryListItem');

           //Category Name
           var categoryName = document.createElement('label');
           categoryName.classList.add('productCategoryTxtItem');
           categoryName.id = "categoryDetail"+counter+"name";
           categoryName.innerHTML = key;

           //Category value
           var categoryValue = document.createElement('label');
           categoryValue.classList.add('productCategoryValueTxtItem');
           categoryValue.innerHTML = keyValue;

           //Detail Button
           var categoryDetail = document.createElement('button');
           categoryDetail.classList.add('productCategoryBtn');
           categoryDetail.innerHTML = "Details";
           categoryDetail.id = "categoryDetail"+counter;
           categoryDetail.setAttribute('onclick', 'getCategoryDetail(this)');

           subItem.appendChild(categoryName);
           subItem.appendChild(categoryValue);
           subItem.appendChild(categoryDetail);

           parentContainer.appendChild(subItem);
           counter = counter + 1;
       </script>
   @endforeach
@endsection

@section('pageModals')
   <div id="stockDeniedCon">
       <div id="stockDeniedConCenter">
            <div class="oopsContainer">
                <img class="oops" src= {{ asset('images/oops.png') }}>
            </div> 
            <div class="oopsTextCon">
                <label class="oopsText">Current warehouse have stock pending approval. Contact director for stock approval</label>
             </div>
       </div>
   </div>
   @if(session()->get('role') !='Accountant')
    <script>
        var stockAccess = "{{ $stockAccess }}";
        if(stockAccess == "denied"){
            document.getElementById("stockDeniedCon").style.display = "block";
        }
        else{
            document.getElementById("stockDeniedCon").style.display = "none";
        }

    </script> 
   @endif
@endsection

