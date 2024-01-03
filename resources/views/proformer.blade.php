@extends('layouts.dashboard')

@section('pageCss')
 <link rel="stylesheet" href={{ asset('css/setup.css') }} />
 <link rel="stylesheet" href={{ asset('css/proformer.css') }} />
 <link rel="stylesheet" href={{ asset('css/transaction.css') }} />

@endsection

@section('pageJs')
  <script>
      var idCounter = 0;
      var wName,wLocation;
      var productIdCounters = [];
      var productNames = [];
      var productCost = [];
      var productIds = [];
      var productQuantities = [];

      $(document).ready(function() { 
        addNewBatch();
        wName = "{{ $warehouse->wname }}";
        wLocation = "{{ $warehouse->wlocation }}";
      
       });

      function computeTotal(){
        var totalTransactions = 0;
        var counter = parseInt(productIdCounters.length);
        for(var i=0;i<productIdCounters.length;i++){
            var value = parseFloat(document.getElementById("productValueResult"+productIdCounters[i]).innerHTML);
            totalTransactions = totalTransactions + value;
           // totalTransactions = totalTransactions + parseInt(document.getElementById("productValueResult"+i).innerHTML);
        }
        document.getElementById('totalTagTxt').innerHTML = "&#8373 "+totalTransactions;
       
      }  

      function addNewBatch(){

            idCounter = idCounter+1;
            processBatch();  
            productIdCounters.push(idCounter);
       }

      function processBatch(){
        var receiveGoodsListItem = document.createElement("div");
        receiveGoodsListItem.classList.add("receiveGoodsListItem");
        receiveGoodsListItem.id = "receiveGoodsListItem"+idCounter;

        //Row number
        var productRowNumber = document.createElement('label');
        productRowNumber.classList.add("productRowNumber");
        productRowNumber.id = "productRowNumber"+idCounter;


        //Product Select Div
        var productSelectDiv = document.createElement("div");
        //productSelectDiv.classList.add("picomplete");
        //productSelectDiv.classList.add("productSelectDiv");
        productSelectDiv.classList.add("autocomplete2");
        productSelectDiv.id = "productDiv"+idCounter;

    
        var productSelect = document.createElement("input");
        productSelect.classList.add("inventoryProductSelect");
        productSelect.classList.add("inputDesign");
        productSelect.autocomplete = "off";
        productSelect.id="productSelect_"+idCounter;
        productSelect.addEventListener(
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
                for (i = 0; i < productNames.length; i++) {
                /*check if the item starts with the same letters as the text field value:*/
                if (productNames[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    /*create a DIV element for each matching element:*/
                
                    b = document.createElement("DIV");
                    /*make the matching letters bold:*/
                    b.innerHTML = "<strong>" + productNames[i].substr(0, val.length) + "</strong>";
                    b.innerHTML += productNames[i].substr(val.length);
                    /*insert a input field that will hold the current array item's value:*/
                    b.innerHTML += "<input type='hidden' value='" + productNames[i] + "'>";
                    /*execute a function when someone clicks on the item value (DIV element):*/
                    b.addEventListener("click", function(e) {
                        var a = productNames.indexOf(this.getElementsByTagName("input")[0].value);
                        var currentId = element.target.id;
                        var myArray = currentId.split('_');
                        var currentIdIndex = myArray[1];
                        //console.log("Current index: "+currentIdIndex);
                        document.getElementById('productUnitCostValue'+currentIdIndex).innerHTML = productCost[a];
                        document.getElementById("productValueResult"+currentIdIndex).innerHTML = "";
                        document.getElementById("productQuantity_"+currentIdIndex).value = "";
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
        productSelect.addEventListener(
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
        
        //autocomplete(document.getElementById("productSelect0"), countries);
        productSelect.placeholder = "Select Product";

        //Add product select to product select div
        productSelectDiv.appendChild(productSelect);
        
        

        //Quantity lable
        var productQuantityLable = document.createElement("label");
        productQuantityLable.classList.add("productQuantityLable");
        productQuantityLable.innerHTML = "Quantity:";

        //Quantity input
        var productQuantityInput = document.createElement("input");
        productQuantityInput.classList.add("productQuantityInput");
        productQuantityInput.classList.add("inputDesign");
        productQuantityInput.id = "productQuantity_"+idCounter;
        productQuantityInput.type = "number";
        productQuantityInput.addEventListener(
            "input", function(element) {
            //Get current idCounter
            var currentId = element.target.id;
            var myArray = currentId.split('_');
            var currentIdIndex = myArray[1];
            
            var currentUnitCost = document.getElementById("productUnitCostValue"+currentIdIndex).innerHTML;
            var currentQuantity = parseInt(element.target.value);
            
            var selectedProduct = document.getElementById("productSelect_"+currentIdIndex).value;
            var selectedProductIndex = productNames.findIndex(productName => productName === selectedProduct)
            

            var productQuantity = parseInt(productQuantities[selectedProductIndex]);
            console.log("current product: "+ productQuantity+ " entered quantity: "+currentQuantity);
            


            if(currentQuantity>productQuantity){
                showError("Invalid Input","The quantity entered is more than the available quantity");
                element.target.value = 0;
                document.getElementById("productValueResult"+currentIdIndex).innerHTML = 0.00;
            }else{
                var result = currentUnitCost*currentQuantity;
                document.getElementById("productValueResult"+currentIdIndex).innerHTML = result;
                computeTotal();
            }

                

            

            }
        );

        //Unit Cost Label
        var productUnitCostLable = document.createElement("label");
        productUnitCostLable.classList.add("productUnitCostLable");
        productUnitCostLable.innerHTML = "Unit Cost:";

        //Unit Cost Value
        var productUnitCostValue = document.createElement("label");
        productUnitCostValue.classList.add("productUnitCostValue");
        productUnitCostValue.id = "productUnitCostValue"+idCounter;
        productUnitCostValue.innerHTML = "0.00";

        //Product Value
        var productValueLable = document.createElement("label");
        productValueLable.classList.add("productValueLable");
        productValueLable.innerHTML = "Value:";

        //Product Value result
        var productValueResult = document.createElement("label");
        productValueResult.classList.add("productValueResult");
        productValueResult.id = "productValueResult"+idCounter;
        productValueResult.innerHTML = "0.00";

        //Close icon
        if(idCounter>1){
            var productCloseBtn = document.createElement('IMG');
            productCloseBtn.setAttribute("src","icons/cancel.png");
            productCloseBtn.classList.add("productCloseBtn");
            productCloseBtn.id=idCounter;
            productCloseBtn.onclick = function () {
                        var objectId = "receiveGoodsListItem"+this.id;
                        var currentListItem = document.getElementById(objectId);
                        document.getElementById("receiveTransItemBox").removeChild(currentListItem);
                        var deletedId = this.id;
                        
                        var indexOfDeleted = productIdCounters.indexOf(parseInt(deletedId));
                        var indexBeforeDeleted = parseInt(indexOfDeleted) - 1;
                        var beforeCounter = productIdCounters[indexBeforeDeleted];

                        //Get number of element before
                        var numberingBefore = document.getElementById('productRowNumber'+beforeCounter).innerHTML;
                        var actualNumber = parseInt(numberingBefore.charAt(0));
                        var changeCounter = 1;
                        for(var i=0;i<productIdCounters.length;i++){
                            if(productIdCounters[i] > parseInt(deletedId) ){
                               //Get numbering
                               //var no = document.getElementById('productRowNumber'+productIdCounters[i]).innerHTML;
                               var integer = actualNumber + parseInt(changeCounter);
                             //  var increament = integer + parse 
                               document.getElementById('productRowNumber'+productIdCounters[i]).innerHTML = ""+integer+"."
                               changeCounter = changeCounter + parseInt(1);
                            }
                        }

                        productIdCounters = $.grep(productIdCounters, function(value) {
                        return value != deletedId;
                        
                        });
                        computeTotal();
                        //delete productIdCounters[this.id];
                    // var index =  productIdCounters.indexOf(this.id);
                        //console.log("Located at: "+index);
                        //productIdCounters.splice(index, this.id);
            };

            receiveGoodsListItem.appendChild(productCloseBtn);
        }
        
    
        receiveGoodsListItem.appendChild(productRowNumber);
        receiveGoodsListItem.appendChild(productSelectDiv);
        receiveGoodsListItem.appendChild(productQuantityLable);
        receiveGoodsListItem.appendChild(productQuantityInput);
        receiveGoodsListItem.appendChild(productUnitCostLable);
        receiveGoodsListItem.appendChild(productUnitCostValue);
        receiveGoodsListItem.appendChild(productValueLable);
        receiveGoodsListItem.appendChild(productValueResult);
    

        document.getElementById("receiveTransItemBox").appendChild(receiveGoodsListItem); 
        document.getElementById("receiveTransItemBox").appendChild(receiveGoodsListItem); 
        let temp = document.getElementById('receiveTransItemBox');
        document.getElementById("productRowNumber"+idCounter).innerHTML = ""+temp.childElementCount+"."
            
     }

     function checkCustomerName(){
       var customer = document.getElementById('proformerCustomerName').value;
       if(customer.length == 0){
           showError("Please enter customer name to proceed");
       }else{
        printProformer();
       }
     }

     function printProformer(){
        var today = new Date();
        var date = today.getDate()+" "+getCurrentDate((today.getMonth()+1))+" "+today.getFullYear();
        
        var printWindow = window.open('', '', 'height=800,width=1200');
        printWindow.document.write('<html><head><title>Proformer</title></head>');  
        printWindow.document.write(
           '<style>'+
           ' table, th, td {'+
           'border: 1px solid black; text-align:center;font-size:11px;}'+
           '#historyTable{'+
            'width:100%;height:auto;position:relative;'+
           '}'+
           'body{'+
           'margin : 0px;'+
           '}'+
           '</style>'+ 
           '<body>'
        );
        printWindow.document.write('<div style="width:100%;height:auto;padding-top:5px;padding-bottom:5px;text-align:center;">'+
          '<h2>Proformer Invoice<h2>'+  
          '</div>'+
          '<div style="width:100%;height:auto;top:40px;text-align:center;position:absolute">'+
          '<h4>'+wName+'('+wLocation+')</h4>'+
          '</div>'+
          '<div style="width:100%;height:auto;top:63px;text-align:center;position:absolute">'
          +'<h4>'+document.getElementById('proformerCustomerName').value+'</h4>'+
          '</div>'+
          '<div style="width:100%;height:auto;top:85px;text-align:center;position:absolute">'
          +'<h4>'+date+'</h4>'+
          '</div>'
        );

        //MainBody
        printWindow.document.write(
            '<div style="width:80%; height:0.5px; left:10%; background-color:grey; top:130px; position:absolute"></div>'
        );
        printWindow.document.write(
            '<div style="width:90%;left:5%;top:160px;padding-top:5px;padding-bottom:5px;height:auto;position:absolute">'   
        );
        printWindow.document.write(
                    '<table id="historyTable">'+
                    '<thead>'+
                    '<tr>'+
                    '<th>Product Name</th>'+
                    '<th>Quantity</th>'+
                    '<th>Value</th>'+
                    '</tr>'+
                    '</thead>'+
                    '<tbody>'

                );
        var totalQuantity = 0; 
        var totalValue = 0;

        for(var i=0;i<productIdCounters.length;i++){
            var productName = document.getElementById("productSelect_"+productIdCounters[i]).value;
            var productQuantity = document.getElementById("productQuantity_"+productIdCounters[i]).value;
            var productValue = document.getElementById("productValueResult"+productIdCounters[i]).innerHTML;

            totalQuantity = totalQuantity + parseInt(productQuantity);
            totalValue = totalValue + parseFloat(productValue);

            printWindow.document.write(
                '<tr>'+
                 '<td>'+productName+'</td>'+
                 '<td>'+productQuantity+'</td>'+
                 '<td> &#8373 '+productValue+'</td>'+ 
                '</tr>'    
            )
        }

        printWindow.document.write(
                '<tr>'+
                 '<td> Total </td>'+
                 '<td>'+totalQuantity+'</td>'+
                 '<td> &#8373 '+totalValue+'</td>'+ 
                '</tr>'    
            )

        printWindow.document.write(
            '</tbody>'+
            '</table>'+
            '</div>' +
            '</body>'
        )

        printWindow.document.close();  
        //printWindow.print(); 
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
        
    <div class="sideMenuIconContainer" onclick="loadProducts()">
        <img class="sideMenuCenterIcon" src={{ asset('icons/product.png') }} >
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Products</label>
        </div>
        <label style="display:none" id="productsURL">{{ route('productsPage') }}</label>
    </div>

    <div class="sideMenuIconContainer" onclick="loadInventory()" >
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

    <div class="sideMenuIconContainer" id="selectedMenu">
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

    
    @foreach ( $data as $result )
        <script>
            productNames.push('{{ $result->name }}');
            productCost.push('{{ $result->price }}');
            productIds.push('{{ $result->id }}');
            productQuantities.push('{{ $result->pivot->quantity }}');    
        </script>
    @endforeach
   <div class="proformerBody">
     <div class="productHead">
        <label class="productTitle">Proformer Invoice</label>
     </div>
     <div class="productContent">
        <div id="receiveTransBox">
            <div class="proformerUserInputDiv">
               <input id="proformerCustomerName" class="inputDesign proformerNameSelect" placeholder="Customer Name">
            </div>
            <div id="receiveTransItemBox">

            </div>
            <div class="addReceiveBtnGoodsBox">
                <label class="totalTag">Total:</label>
                <label id="totalTagTxt">&#8373 0.00</label>
            </div>
            <div class="addReceiveBtnGoodsBox">
                <button id="addReceiveGoodsBtn" onclick="addNewBatch()">Add New Batch</button>
            </div>
            <div id="addToStockBtnBox">
                <button id="addToStockBtn" onclick="checkCustomerName()">Print Proformer</button>
            </div>
        </div>
     </div>
   </div>
@endsection

