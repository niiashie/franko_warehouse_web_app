@extends('layouts.dashboard')

@section('pageCss')
    <link rel="stylesheet" href={{ asset('css/stock.css') }} />
    <link rel="stylesheet" href={{ asset('css/product.css') }} />
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
            <div class="sideMenuIconContainer" id="selectedMenu">
                <img class="sideMenuCenterIcon" src={{ asset('icons/stocks.png') }}>
                <div class="sideMenuLeftLabel">
                    <label class="sideMenuLeftLabelText">Stocks</label>
                </div>
                <!--<label style="display:none" id="stockURL">{{ route('stock') }}</label>-->
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

@section('pageJs')
 <script>
     var totalOldValue = 0,totalNewValue = 0,differenceValue=0,differenceQuantity=0;
     var productNames = [],previousQuantity = [],currentQuantity=[],quantityDifference=[];
     var previousValue = [],currentValue = [], valueDifference = [];
     var stockDate = "",selectedProductId="",selectedProductQuantity="",selectedProductPrice="";
     var dashProductNames2 = [],dashProductIds2 = [], dashProductPrice = [],dashProductQuantity = [];
     function processStock(){
       alert("Hello");
     }

     $(document).ready(function() {
        getProductList2();
        modifyStock();
     });


     function getProductList2(){
         console.log("getting product list");
         var productsRequest  = $.ajax({
                                                    url:"/productStockList",
                                                    method: "GET",
         });
         productsRequest .done(function (response, textStatus, jqXHR){ 
            console.log(response);
            for(var i=0;i<response.length;i++){
               dashProductNames2.push(response[i]['name']);
               dashProductIds2.push(response[i]['id']);
               dashProductPrice.push(response[i]['price']);

               var warehouses = response[i]['warehouses'];
               var warehouse = warehouses[0]['pivot'];
               dashProductQuantity.push(warehouse['quantity']);
            }
            setUpStockProductSearch();
         });

    }

    function setUpStockProductSearch(){
      var searchField  = document.getElementById('stockSearchEntry');
      
      searchField.addEventListener(
                        "input", function(element) {
                        var a, b, i, val = this.value;
                            /*close any already open lists of autocompleted values*/
                            closeAllLists();
                            if (!val) { return false;}
                            currentFocus = -1;
                            /*create a DIV element that will contain the items (values):*/
                            a = document.createElement("DIV");
                            a.setAttribute("id", this.id + "autocomplete-list");
                            a.setAttribute("class", "autocomplete-items");
                            /*append the DIV element as a child of the autocomplete container:*/
                            this.parentNode.appendChild(a);
                            /*for each item in the array...*/
                            for (i = 0; i < dashProductNames2.length; i++) {
                            /*check if the item starts with the same letters as the text field value:*/
                            if (dashProductNames2[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                                /*create a DIV element for each matching element:*/
                            
                                b = document.createElement("DIV");
                                /*make the matching letters bold:*/
                                b.innerHTML = "<strong>" + dashProductNames2[i].substr(0, val.length) + "</strong>";
                                b.innerHTML += dashProductNames2[i].substr(val.length);
                                /*insert a input field that will hold the current array item's value:*/
                                b.innerHTML += "<input type='hidden' value='" + dashProductNames2[i] + "'>";
                                /*execute a function when someone clicks on the item value (DIV element):*/
                                b.addEventListener("click", function(e) {
                                    var a = dashProductNames2.indexOf(this.getElementsByTagName("input")[0].value);
                                    var currentId = element.target.id;
                                    var myArray = currentId.split('_');
                                    var currentIdIndex = myArray[1];
                                    console.log("Current index: "+dashProductIds2[a]);
                                    selectedProductId = dashProductIds2[a];
                                    selectedProductPrice = dashProductPrice[a];
                                    selectedProductName = a;
                                    
                                    selectedProductQuantity = dashProductQuantity[a];
                                    document.getElementById('oldStockQuantityValue').innerHTML = dashProductQuantity[a];
                                    console.log("Product price : "+selectedProductPrice);
                                    /*insert the value for the autocomplete text field:*/
                                    element.target.value = this.getElementsByTagName("input")[0].value;
                                    //document.getElementById("productSelect1").value = this.getElementsByTagName("input")[0].value;
                                    //e.innerHTML = this.getElementsByTagName("input")[0].value;
                                    /*close the list of autocompleted values,
                                    (or any other open lists of autocompleted values:*/
                                    closeAllLists();
                                });
                                a.appendChild(b);
                            }
                            }


                            function closeAllLists(elmnt) {
                                /*close all autocomplete lists in the document,
                                except the one passed as an argument:*/
                                var x = document.getElementsByClassName("autocomplete-items");
                                for (var i = 0; i < x.length; i++) {
                                    if (elmnt != x[i] && elmnt != element) {
                                    x[i].parentNode.removeChild(x[i]);
                                    }
                                }
                            }

                        }
                    );
                    searchField.addEventListener(
                        "keydown",function(e){
                        var x = document.getElementById(this.id + "autocomplete-list");
                            if (x) x = x.getElementsByTagName("div");
                            if (e.keyCode == 40) {
                            /*If the arrow DOWN key is pressed,
                            increase the currentFocus variable:*/
                            currentFocus++;
                            /*and and make the current item more visible:*/
                            addActive(x);
                            } else if (e.keyCode == 38) { //up
                            /*If the arrow UP key is pressed,
                            decrease the currentFocus variable:*/
                            currentFocus--;
                            /*and and make the current item more visible:*/
                            addActive(x);
                            } else if (e.keyCode == 13) {
                            /*If the ENTER key is pressed, prevent the form from being submitted,*/
                            e.preventDefault();
                            if (currentFocus > -1) {
                                /*and simulate a click on the "active" item:*/
                                if (x) x[currentFocus].click();
                            }
                        }

                        function addActive(x) {
                            /*a function to classify an item as "active":*/
                            if (!x) return false;
                            /*start by removing the "active" class on all items:*/
                            removeActive(x);
                            if (currentFocus >= x.length) currentFocus = 0;
                            if (currentFocus < 0) currentFocus = (x.length - 1);
                            /*add class "autocomplete-active":*/
                            x[currentFocus].classList.add("autocomplete-active");
                        }
                        function removeActive(x) {
                            /*a function to remove the "active" class from all autocomplete items:*/
                            for (var i = 0; i < x.length; i++) {
                                x[i].classList.remove("autocomplete-active");
                            }
                        }
                        }
                    );
    } 

     function modifyStock(){
       document.getElementById('stockTable').style.display = "none";
       document.getElementById('stockChangeCon').style.display = "block";
     }

     function stockDetails(){
       document.getElementById('stockTable').style.display = "block";
       document.getElementById('stockChangeCon').style.display = "none";
     }

     function getStockData(){
        var stockRequest  = $.ajax({
                                url:"/stockData",
                                method: "GET",
                                data:{
                                    "_token": "{{ csrf_token() }}"
                                }
        });
        stockRequest.done(function(response, textStatus, jqXHR){
            console.log("Response is: "+response);                                       
        });
        
     }

     function updateStock(){
        var newQuantity = document.getElementById('newStockQuantityValue').value;
        
        if(newQuantity.length == 0){
            showError("Invalid Input","Please enter new product quantity");
        }
        
        else{
               var stockChangeRequest  = $.ajax({
                        url:"/updateStock",
                        method: "POST",
                        data:{
                            id : selectedProductId,
                            price : selectedProductPrice,
                            new_quantity : newQuantity,
                           "_token": "{{ csrf_token() }}"
                        }
                });
                stockChangeRequest.done(function(response, textStatus, jqXHR){
                    console.log(response);
                    if(response == "Success"){
                    Swal.fire({
                                    title: 'Successfully confirmed stock details',
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
                      showError("Stock Update Failed",response);
                    }
                });
        }
     }

     function printStock(){
        var today = new Date();
            var month = today.getMonth()+1;
            var format = today.getDate()+" "+getCurrentDate(month)+" "+today.getFullYear();
            var wName = "{{ $warehouse->wname }}";
            var wLocation = "{{ $warehouse->wlocation }}"
            var printWindow = window.open('', '', 'height=800,width=1200');  
            printWindow.document.write('<html><head><title>Inventory</title>');  
            printWindow.document.write('</head>'+
            '<style>'+
            ' table, th, td {'+
            'border: 1px solid black; text-align:center}'+
            '#historyTable{'+
            'left:5%;width:90%;height:auto;position:absolute;'+
            '}'+
            '</style>'+
            '<body>');  
            printWindow.document.write('<div style="width:100%;height:auto;padding-top:5px;padding-bottom:5px;text-align:center;">'
            +'<h2>Stock Report<h2>'+
            '</div>'+
            '<div style="width:100%;height:auto;top:45px;text-align:center;position:absolute">'+
            '<h3>'+wName+'('+wLocation+')</h3>'+
            '<div style="width:100%;height:auto;top:40px;text-align:center;position:absolute">'
            +'<h3>'+stockDate+'</h3>'+
            '</div>'+
            '<div style="top:100px;width:100%;height:auto;position:absolute;">'+
                '<table id="historyTable">'+
                '<thead>'+
                '<tr>'+
                '<th>Product Name</th>'+
                '<th>Previous Quantity</th>'+
                '<th>Current Quantity</th>'+
                '<th>Quantity Difference</th>'+
                '<th>Previous Value</th>'+
                '<th>Current Value</th>'+
                '<th>Value Difference</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody id="historyTableBody">');
                var totalQuantity = 0,totalValue = 0;
                for(var i=0;i<productNames.length;i++){
                    printWindow.document.write(
                    '<tr>'+
                        '<td>'+productNames[i]+'</td>'+
                        '<td>'+previousQuantity[i]+'</td>'+
                        '<td>'+currentQuantity[i]+'</td>'+
                        '<td>'+quantityDifference[i]+'</td>'+
                        '<td> &#8373 '+previousValue[i]+'</td>'+
                        '<td> &#8373 '+currentValue[i]+'</td>'+
                        '<td> &#8373 '+valueDifference[i]+'</td>'+
                    '</tr>'
                    );
                    console.log(previousValue[i]);
                    //totalQuantity = totalQuantity + parseInt(productQuantities[i]);
                    //totalValue = totalValue + parseFloat(productValue[i]);
                }
                printWindow.document.write(
                    '<tr>'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td>Total</td>'+
                        '<td style="color:black;font-weight: 900;">'+differenceQuantity+'</td>'+
                        '<td style="color:black;font-weight: 900;"> &#8373 '+totalOldValue+'</td>'+
                        '<td style="color:black;font-weight: 900;"> &#8373 '+totalNewValue+'</td>'+
                        '<td style="color:black;font-weight: 900;"> &#8373 '+differenceValue+'</td>'+
                    '</tr>'
                );

                printWindow.document.write(
                    '</tbody>'+
                    '</table>'+
                    '</div>'
                );
            
            printWindow.document.write('</body></html>');  
            printWindow.document.close();  
            printWindow.print(); 
     }

     function processStock(){
         var stockId = "{{ $stockID }}";
        Swal.fire({
            title: 'Confirm Stock',
            text: "Are you certain with all the stock figures displayed below?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0093E9',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Proceed'
            }).then((result) => {  
                if (result.value) {
                var categoryDeleteRequest  = $.ajax({
                        url:"/submitStock",
                        method: "GET",
                        data:{
                            stockId:stockId,
                            "_token": "{{ csrf_token() }}"
                        }
                });
                categoryDeleteRequest.done(function(response, textStatus, jqXHR){
                    
                    if(response == "Success"){
                    Swal.fire({
                                    title: 'Successfully confirmed stock details',
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
                    showError("Category Deletion Failed",response);
                    }
                });
                }
        });
     }
 </script>
@endsection

@section('pageContent')
   @isset($date)
      <script>
          var unProcessedStockDate = "{{ $date }}";
          var dateExtract = unProcessedStockDate.substring(0,10);
          //alert(dateExtract);
          var month = dateExtract.substring(5,7);
          var day = dateExtract.substring(8,10);
          var year = dateExtract.substring(0,4);

          stockDate = ""+day+"/"+getCurrentDate(month)+"/"+year;
          
      </script>
   @endisset
   <div id="productBody">
       <div class="productHead">
          <label class="productTitle">Stock Manager</label>
          <div class="addBtnCon" onclick="processStock()">
            <img class="addBtnConIcon" src="/icons/process.png">
            <label class="addBtnConLable">Confirm Stock</label>
          </div>
          <div id="printStockCon" onclick="printStock()">
            <img class="addBtnConIcon" src="/icons/white_print.png">
            <label class="addBtnConLable">Print Stock</label>
          </div>
          <div id="modifyStock" onclick="modifyStock()">
            <img class="addBtnConIcon" src="/icons/list.png">
            <label class="addBtnConLable">Modify Stock</label>
          </div>
          <div id='stockStatusCon'>
             <label id="stockStatus">Hi</label>

          </div>
          <script>
            var status = "{{ $status }}";
            if(status == "pending"){
               document.getElementById('stockStatus').innerHTML = "Pending Confirmation"
            }
            else{
                document.getElementById('stockStatus').innerHTML = "Awaiting Approval"
            }
         </script>
       </div>
       <div id="stockTable" class="productContent">
            <div class="tableContainer">
                <table id="stocksTable">
                    <thead>
                        <tr>
                        <th>Product</th>
                        <th>Previous Quantity</th>
                        <th>Current Quantity</th>
                        <th>Quantity Difference</th>
                        <th>Previous Value</th>
                        <th>Current Value</th>
                        <th>Value Difference</th>
                        </tr> 
                    </thead> 
                    <tbody id="tbody2">
                        @foreach ($stockData as $row2)
                            <tr>
                                <td>{{ $row2->products->name }}</td>
                                <td>{{ $row2->old_quantity }}</td>
                                <td>{{ $row2->new_quantity }}</td>
                                <td>{{ $row2->difference_quantity }}</td>
                                <td>&#8373 {{ $row2->old_value }}</td>
                                <td>&#8373 {{ $row2->new_value }}</td>
                                <td>&#8373 {{ $row2->difference_value }}</td>
                            </tr>
                            <script>
                                var oValue = "{{ $row2->old_value }}";
                                var nValue = "{{ $row2->new_value }}";
                                var dValue = "{{ $row2->difference_value }}";
                                var qValue = "{{ $row2->difference_quantity }}";
                                
                                productNames.push("{{ $row2->products->name }}");
                                previousQuantity.push("{{ $row2->old_quantity }}");
                                currentQuantity.push("{{ $row2->new_quantity }}");
                                quantityDifference.push("{{ $row2->difference_quantity }}");
                                previousValue.push("{{ $row2->old_value }}");
                                currentValue.push("{{ $row2->new_value }}");
                                valueDifference.push("{{ $row2->difference_value }}");


         
                                totalOldValue = totalOldValue + parseInt(oValue);
                                totalNewValue = totalNewValue + parseInt(nValue);
                                differenceValue = differenceValue + parseInt(dValue);
                                differenceQuantity = differenceQuantity + parseInt(qValue);

                            </script>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="totalbox">
                <div id="box1">
                   <label class="boxheading">Total Old Value</label>
                   <label class="boxValue" id="totalOldValue"></label>
                   <div class="rightDivider"></div>
                </div>
                <div id="box2">
                   <label class="boxheading">Total New Value</label>
                   <label class="boxValue" id="totalNewValue"></label>
                   <div class="rightDivider"></div>
                </div>
                <div id="box3">
                    <label class="boxheading">Difference In Quantity</label>
                    <label class="boxValue" id="differenceQuantity"></label>
                   <div class="rightDivider"></div>
                </div>
                <div id="box4">
                    <label class="boxheading">Difference In Value</label>
                    <label class="boxValue" id="differenceValue"></label>
                </div>
                <div id="topDivider"></div>
                <script>

                    document.getElementById('totalOldValue').innerHTML = "&#8373 "+totalOldValue;
                    document.getElementById('totalNewValue').innerHTML = "&#8373 "+totalNewValue;
                    document.getElementById('differenceValue').innerHTML = "&#8373 "+differenceValue;
                    document.getElementById('differenceQuantity').innerHTML = ""+ differenceQuantity;
                    console.log("Total Old Value: "+totalOldValue);
                    
                </script>
            </div>
       </div>
       <div id="stockChangeCon" class="productContent">
         <div id="stockSearchContainer">
            <input id="stockSearchEntry" type="text" placeholder="Enter Product name">            
         </div>
         <div id="oldQuantityCon">
           <label class="oldQuantityLabel">Old Quantity : </label>
           <div id="oldQuantityLabelCon">
             <label id="oldStockQuantityValue"></label>
           </div>
         </div>
         <div id="newQuantityCon">
           <label class="oldQuantityLabel">New Quantity : </label>
           <input id = "newStockQuantityValue" type="number" placeholder="Enter new value" />  
         </div>
         <button onclick="updateStock()" id="updateStockValue">Update Stock</button>
        
         <div id="bottomLine">
            <div id="topLine"></div>
            <div id="stockDetails" onclick="stockDetails()">
                <img class="addBtnConIcon" src="/icons/list.png">
                <label class="addBtnConLable">Stock Table</label>
            </div>
         </div>
       </div>
   </div>

@endsection
