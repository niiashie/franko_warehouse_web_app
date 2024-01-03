@extends('layouts.dashboard')

@section('pageCss')
 <link rel="stylesheet" href={{ asset('css/product.css') }} />
 <link rel="stylesheet" href={{ asset('css/picomplete.css') }} />
 <link rel="stylesheet" href={{ asset('css/inventory.css') }} />
 <link rel="stylesheet" href={{ asset('css/setup.css') }} />
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-database.js"></script>
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-auth.js"></script>
@endsection

@section('pageJs')

        <script>

        var idCounter = 0;
        var totalAmount = 0;
        var productNames = [];
        var productCost = [];
        var productIds = [];
        var productValue = [];
        var productQuantities = [];
        var productCategories = [];
        var productIdCounters = [];
        var wName,wLocation;
        var inventoryHistoryPayLoad = {},resultJSON;
        var goodsPermissionGrant = false;
        var goodsPermissionSent = false;
        var goodsReceptionStatus,currentRequisitionId,notificationCount;

        $(document).ready(function() {
            document.getElementById("selectedMenu").style.backgroundColor = "#dddddd";

            //Initialize Firebase
            var firebaseConfig = {
                apiKey: "AIzaSyClvlKP8rq3fUpiUjN3MnlwFC0ZcjQcIjE",
                authDomain: "qudi-799c9.firebaseapp.com",
                databaseURL: "https://qudi-799c9.firebaseio.com",
                projectId: "qudi-799c9",
                storageBucket: "qudi-799c9.appspot.com",
                messagingSenderId: "946416892069",
                appId: "1:946416892069:web:bc3e23d77885c46da3754c"
                };
                // Initialize Firebase
                firebase.initializeApp(firebaseConfig);
                var currentWarehouse = "{{ session()->get('warehouse') }}";
                firebase.database().ref("franko_ware_house/goods_reception/"+currentWarehouse).on('value', function(snapshot) {
                    var result = snapshot.val(); 
                    goodsReceptionStatus = result.status;
                    currentRequisitionId = result.requisition;
                });
                firebase.database().ref("franko_ware_house/mobile_notification_count").on('value',function(snapshot){
                    var result = snapshot.val(); 
                    notificationCount = result.number;
                    //console.log("Notification count: "+result.number);
                });
            
            addNewBatch();
            document.getElementById("inventoryHeadingTxt").innerHTML =  localStorage.getItem('ware_house_name');
            document.getElementById("inventoryHeadingTxt2").innerHTML = localStorage.getItem('ware_house_location');

            $("#addBtnSearchEntry").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#inventoryTableStructure tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
           
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

            function receiveGoods(){
                document.getElementById("productBody").style.display = "none";
                document.getElementById("addGoodsBody").style.display = "block";
                document.getElementById("goodsReceptionBlockPopUp").style.display = "block";
                if(goodsReceptionStatus == "pending"){
                    document.getElementById("goodsReceptionBlockPopUp").style.display = "none";
                    document.getElementById('goodsReceptionSentPopUp').style.display = "block";
                }else if(goodsReceptionStatus == "granted"){
                    document.getElementById("goodsReceptionBlockPopUp").style.display = "none";
                    console.log("Requisition granted");
                    document.getElementById("goodsReceptionBlockPopUp").style.display = "none";
                }
            
            
            }

            function closeRequisitionBlock(){
                document.getElementById("productBody").style.display = "block";
                document.getElementById("addGoodsBody").style.display = "none";
                document.getElementById("goodsReceptionBlockPopUp").style.display = "none";
                document.getElementById("goodsReceptionSentPopUp").style.display = "none";
            }

            function iventory(){
            document.getElementById("productBody").style.display = "block";
            document.getElementById("addGoodsBody").style.display = "none";
            document.getElementById("inventoryHistoryBody").style.display = "none";
            }

           


            function showError(title,message){
                Swal.fire({
                            icon: 'error',
                            title: title,
                            text: message
                });
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
                        var currentQuantity = element.target.value;

                        var result = currentUnitCost*currentQuantity;
                        document.getElementById("productValueResult"+currentIdIndex).innerHTML = result;
                        computeTotal();
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
                                    document.getElementById("receiveGoodsItemBox").removeChild(currentListItem);
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
                

                    document.getElementById("receiveGoodsItemBox").appendChild(receiveGoodsListItem); 
                    let temp = document.getElementById('receiveGoodsItemBox');
                    document.getElementById("productRowNumber"+idCounter).innerHTML = ""+temp.childElementCount+"."
        }

        function addNewBatch(){

            idCounter = idCounter+1;
            processBatch();  
            productIdCounters.push(idCounter);
        }

        function addToStock(){
            var resultArray = [];
            var errorCounter = 0;
            var productsArray = [];
            console.log("Length: "+productIdCounters.length);
            for(var i=0;i<productIdCounters.length;i++){
                //console.log("Id is: "+productIdCounters[i]);
                var productName = document.getElementById("productSelect_"+productIdCounters[i]);
                var productQuantity = document.getElementById("productQuantity_"+productIdCounters[i]);
                var productValue = document.getElementById("productValueResult"+productIdCounters[i]);
                var productNameIndex = productNames.indexOf(productName.value);
                var productId = productIds[productNameIndex];
                
                
                var quantity = productQuantity.value;
                var value = productValue.innerHTML;
                var id = productId;
                var name = productName.value; 
                

                if(productName.value == "" || productQuantity.value == ""){
                    errorCounter = errorCounter + 1;
                }

                var obj = {
                    "product_name":name,
                    "product_id":id,
                    "product_value":value,
                    "product_quantity":quantity,
                };

                productsArray.push(obj);

            }
            resultArray = {
                "products":productsArray,
                "warehouse_id":"{{ session()->get('warehouse') }}",
                "requisition_id":currentRequisitionId
            }
            if(errorCounter>0){
                showError("Error","Some rows are empty");
            }else{
                    /* var obj = {"name": "Peter", "age": 22, "country": "United States"}; */
                var json = JSON.stringify(resultArray);
                console.log(json);

                Swal.fire({
                    title: 'Receive Goods',
                    text: "Do you really want to receive this stock to ware house",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0093E9',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Receive Goods'
                }).then((result) => {  
                    if (result.value) {
                        document.getElementById('loading').style.display = "block";
                        var inventoryRequest  = $.ajax({
                                                    url:"/receiveGoods",
                                                    method: "GET",
                                                    data: {
                                                        res:json
                                                    }
                        });
                        inventoryRequest.done(function (response, textStatus, jqXHR){
                            console.log("Response is :"+ response);
                            var currentWarehouse = "{{ session()->get('warehouse') }}";
                            if(response == 'Success'){

                                firebase.database().ref("franko_ware_house/goods_reception").child(currentWarehouse).set({
                                                        reason: "",
                                                        status : 'closed',
                                                        requisition : ""

                                        },(error) => {
                                                if (error) {
                                                            showError("Please check network",error);
                                                }
                                                else{
                                            
                                                    Swal.fire({
                                                                    title: 'Successfully received goods',
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
                                        }
                                            
                                );
                            }
                            else{
                                document.getElementById('loading').style.display = "none";
                                showError("Error",response);
                            }
                           
                        });

                        inventoryRequest.fail(function (){
                            
                            document.getElementById('loading').style.display = "none";
                            
                            // Show error
                            showError("Failure","Please ensure server is active");
                        });


                    }

                });
                
            }
        
            //alert(json);
        }
            
            
        function inventoryHistory(){
            document.getElementById("productBody").style.display = "none";
            document.getElementById("inventoryHistoryBody").style.display = "block";
        }

        function checkGoodsReceptionPermission(){
        var warehouseReception = localStorage.getItem('ware_house_received_goods');
        console.log("warehouse: "+warehouseReception);
            dbRef = firebase.database().ref();

            dbRef.child("franko_ware_house/goods_reception").child(warehouseReception).on('value', (snapshot) => {
                console.log(snapshot.val());
                
                if(snapshot.val().permission == 0){
                    goodsPermissionSent = false
                }else{
                    goodsPermissionSent = true;
                }

                if(snapshot.val().access == 0){
                    goodsPermissionGrant = false;
                }else{
                    goodsPermissionGrant = true;
                }
                console.log("Permission grant: "+goodsPermissionGrant);
            });
        }

        function printDiv(date,wName,wLocation) {
            //var divContents = document.getElementById("inventoryTableStructure").innerHTML;  
            var printWindow = window.open('', '', 'height=800,width=1200');  
            printWindow.document.write('<html><head><title>Print DIV Content</title>');  
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
            +'<h2>Inventory History<h2>'+
            '</div>'+
            '<div style="width:100%;height:auto;top:45px;text-align:center;position:absolute">'+
            '<h3>'+wName+'('+wLocation+')</h3>'+
            '<div style="width:100%;height:auto;top:40px;text-align:center;position:absolute">'
            +'<h3>'+date+'</h3>'+
            '</div>'+
            '<div style="top:100px;width:100%;height:auto;position:absolute;">'+
                '<table id="historyTable">'+
                '<thead>'+
                '<tr>'+
                '<th>Product Name</th>'+
                '<th>Quantity</th>'+
                '<th>Value</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody id="historyTableBody">');
                var totalQuantity = 0,totalValue = 0;
                for(var i=0;i<inventoryHistoryPayLoad[date].length;i++){
                    printWindow.document.write(
                    '<tr>'+
                        '<td>'+inventoryHistoryPayLoad[date][i]['productName']+'</td>'+
                        '<td>'+inventoryHistoryPayLoad[date][i]['productQuantity']+'</td>'+
                        '<td> &#8373 '+inventoryHistoryPayLoad[date][i]['productValue']+'</td>'+
                    '</tr>'
                    );
                    totalQuantity = totalQuantity + parseInt(inventoryHistoryPayLoad[date][i]['productQuantity']);
                    totalValue = totalValue + parseFloat(inventoryHistoryPayLoad[date][i]['productValue']);
                }
                printWindow.document.write(
                    '<tr>'+
                        '<td>Total</td>'+
                        '<td>'+totalQuantity+'</td>'+
                        '<td> &#8373 '+totalValue+'</td>'+
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


        function printInventory(){
            var today = new Date();
            var month = today.getMonth()+1;
            var format = today.getDate()+" "+getCurrentDate(month)+" "+today.getFullYear();
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
            +'<h2>Received Goods History<h2>'+
            '</div>'+
            '<div style="width:100%;height:auto;top:45px;text-align:center;position:absolute">'+
            '<h3>'+wName+'('+wLocation+')</h3>'+
            '<div style="width:100%;height:auto;top:40px;text-align:center;position:absolute">'
            +'<h3>'+format+'</h3>'+
            '</div>'+
            '<div style="top:100px;width:100%;height:auto;position:absolute;">'+
                '<table id="historyTable">'+
                '<thead>'+
                '<tr>'+
                '<th>Product Name</th>'+
                '<th>Category</th>'+
                '<th>Quantity</th>'+
                '<th>Value</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody id="historyTableBody">');
                var totalQuantity = 0,totalValue = 0;
                for(var i=0;i<productNames.length;i++){
                    printWindow.document.write(
                    '<tr>'+
                        '<td>'+productNames[i]+'</td>'+
                        '<td>'+productCategories[i]+'</td>'+
                        '<td>'+productQuantities[i]+'</td>'+
                        '<td> &#8373 '+productValue[i]+'</td>'+
                    '</tr>'
                    );
                    totalQuantity = totalQuantity + parseInt(productQuantities[i]);
                    totalValue = totalValue + parseFloat(productValue[i]);
                }
                printWindow.document.write(
                    '<tr>'+
                        '<td></td>'+
                        '<td>Total</td>'+
                        '<td>'+totalQuantity+'</td>'+
                        '<td> &#8373 '+totalValue+'</td>'+
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

            

        function addRow(name,price,category,quantity,value){
                if (!document.getElementsByTagName) return;

                tabBody=document.getElementById('inventoryTableStructure').getElementsByTagName("tbody").item(0);
                row=document.createElement("tr");
                cell1 = document.createElement("td");
                cell2 = document.createElement("td");
                cell3 = document.createElement("td");
                cell4 = document.createElement("td");
                cell5 = document.createElement("td");
                
                
                textnode1=document.createTextNode(name);
                textnode2=document.createTextNode(category);
                textnode3=document.createTextNode(price);
                textnode4=document.createTextNode(quantity);
                textnode5=document.createTextNode(value);

                
                cell1.appendChild(textnode1);
                cell2.appendChild(textnode2);
                cell3.appendChild(textnode3);
                cell4.appendChild(textnode4);
                cell5.appendChild(textnode5);    

                row.appendChild(cell1);
                row.appendChild(cell2);
                row.appendChild(cell3);
                row.appendChild(cell4);
                row.appendChild(cell5);
                
                
                tabBody.appendChild(row);   

        }

      


        function requestGoodsReception(){
            var currentWarehouse = "{{ session()->get('warehouse') }}";
            var requisitionReason = document.getElementById('goodsReceptionRequestReason').value;
            
            
            if(requisitionReason.length<10){
                showError("Failure","Please throw more light on the reason for requisition");
            }else{
                console.log("Current warehouse id: "+currentWarehouse);
                document.getElementById("loading").style.display = "block";
                var requestGoodsRequest  = $.ajax({
                                                    url:"/requestRequisition",
                                                    method: "GET",
                                                    data:{
                                                        wid:currentWarehouse,
                                                        reason: requisitionReason,
                                                        "_token": "{{ csrf_token() }}"
                                                        }
                });
                requestGoodsRequest.done(function (response, textStatus, jqXHR){
                            console.log("Response is :"+ response);
                            if(response.includes("Success")){
                                var myArr = response.split(" ");
                                var requisitionId = myArr[1];

                                firebase.database().ref("franko_ware_house/goods_reception").child(currentWarehouse).set({
                                                        reason: requisitionReason,
                                                        status : 'pending',
                                                        requisition : requisitionId

                                        },(error) => {
                                                if (error) {
                                                    document.getElementById("loading").style.display = "none";
                                                            showError("Please check network",error);
                                                }
                                                else{
                                                    document.getElementById("loading").style.display = "none";
                                                    notification = notificationCount + 1;
                                                    firebase.database().ref("franko_ware_house/mobile_notification_count").set({
                                                        number: notification
                                                    });
                                                    Swal.fire({
                                                                    title: 'Successfully sent requisition request',
                                                                    text: "",
                                                                    icon: 'success',
                                                                    showCancelButton: false,
                                                                    confirmButtonColor: '#3085d6',
                                                                    confirmButtonText: 'Okay'
                                                                    }).then((result) => {
                                                                        if (result.value) {
                                                                            document.getElementById("goodsReceptionBlockPopUp").style.display = "none";
                                                                            document.getElementById("productBody").style.display = "block";
                                                                            document.getElementById("addGoodsBody").style.display = "none";                   
                                                                    }
                                                            });
                                                }
                                        }
                                            
                                );
                            
                            }else{
                                document.getElementById("loading").style.display = "none";
                                showError("Failure","Please check Internet")
                            }
                });

                requestGoodsRequest.fail(function (){
                            // Show error
                            showError("Failure","Please check Internet")
                });
            }
            
                

    

        }


        function loadWarehouse(){
           var warehouseURL = document.getElementById('warehouseURL').innerHTML;
           window.location.replace(warehouseURL);
        }

        function loadProducts(){
            var productsURL = document.getElementById('productsURL').innerHTML;
            window.location.replace(productsURL);
        }

        function loadHome(){
            var homeURL = document.getElementById('homeURL').innerHTML;
            window.location.replace(homeURL);
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

    <div class="sideMenuIconContainer"  id="selectedMenu">
        <img class="sideMenuCenterIcon" src={{ asset('icons/box2.png') }}>
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Inventory</label>
        </div>
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
   
   

    <div id="productBody">
        <div class="productHead">
            <label class="productTitle">Inventory</label>
            <div class="inventoryHistoryCon" onclick="inventoryHistory()">
            <img class="addBtnConIcon" src= {{ asset('icons/save.png') }} >
            <label class="addBtnConLable">Goods History</label>
            </div>
            @if(session()->get('role') !='Accountant')
                <div class="addWareHouseCon" onclick="receiveGoods()" >
                    <img class="addBtnConIcon" src=  {{ asset('icons/plus.png') }} >
                    <label class="addBtnConLable">Receive Goods</label>
                </div> 
            @endif
            <div id="searchWareHouseCon">
                <img class="addBtnConIcon" src= {{ asset('icons/ashSearch.png') }} >
                <input id="addBtnSearchEntry" type="text" placeholder="Search Inventory..">
            </div>
        </div>
        <div class="productContent">
            <div id="inventoryHeading">
            <label id="inventoryHeadingTxt">Ware House Name</label>
            </div>
            <div id="inventoryHeading2">
            <label id="inventoryHeadingTxt2">Ware House Location</label>
            </div>
            <div class="inventoryTable">
                <table id="inventoryTableStructure">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Unit Cost</th>
                        <th>Quantity</th>
                        <th>Value</th>
                    </tr> 
                </thead> 
                <tbody id="inventoryTableBodyStructure">
                    @foreach ($data as $result)    
                        <tr>
                            <td>{{ $result->name }}</td>
                            <td>{{ $result->pivot->category }}</td>
                            <td>&#8373 {{ $result->price }}</td>
                            <td>{{ $result->pivot->quantity }}</td>
                            <td>&#8373 {{ $result->pivot->value }}</td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            <div class="inventoryTotalContainer">
                <div class="productCategoryTopDivider"> </div>
                <div id="inventoryPrintButton" onclick="printInventory()">
                    <img class="whiteIcon" src= {{ asset('icons/white_print.png') }}>
                    <label class="whiteText">Print</label>
                </div>
                <label id="inventoryTotalLable">Total Value:</label>  
                <label id="inventoryTotalLableTxt">&#8373 30000</label>
            </div>
            @foreach ( $data as $result )
            <script>
                productNames.push('{{ $result->name }}');
                productCost.push('{{ $result->price }}');
                productIds.push('{{ $result->id }}');
                productCategories.push('{{ $result->pivot->category }}')
                productValue.push('{{ $result->pivot->value }}');
                productQuantities.push('{{ $result->pivot->quantity }}');
                var amount = parseFloat('{{ $result->pivot->value }}');

                totalAmount = totalAmount + amount;
                console.log("total: "+totalAmount );
            </script>
            @endforeach
            <script>
                document.getElementById('inventoryTotalLableTxt').innerHTML = '&#8373 '+totalAmount;
            </script>
        </div>
    </div>

    <div id="addGoodsBody"> 
        <div class="productHead">
        <label class="productTitle">Receive Goods</label>
        <div class="inventoryHistoryCon" onclick="iventory()" >
            <img class="addBtnConIcon" src= {{ asset('icons/plus.png') }} >
            <label class="addBtnConLable">Inventory</label>
        </div>
        
    </div>

        <div class="productContent">
            <div id="receiveGoodsListBox">
                <div id="receiveGoodsItemBox">

                </div>
                <div class="addReceiveBtnGoodsBox">
                    <label class="totalTag">Total:</label>
                    <label id="totalTagTxt">&#8373 0.00</label>
                </div>
                <div class="addReceiveBtnGoodsBox">
                    <button id="addReceiveGoodsBtn" onclick="addNewBatch()">Add New Batch</button>
                </div> 
                <div id="addToStockBtnBox">
                    <button id="addToStockBtn" onclick="addToStock()">Add to Stock</button>
                </div>
            </div>
        </div>
    </div>

    <div id="inventoryHistoryBody">
        <div class="productHead">
            <label class="productTitle">Inventory History</label>
            <div class="inventoryHistoryCon" onclick="iventory()" >
                <img class="addBtnConIcon" src= {{ asset('icons/plus.png') }} >
                <label class="addBtnConLable">Inventory</label>
            </div>
        </div>
        <div class="productContent">
            <div id="inventoryHistoryBodyCon">
                <div id="inventoryHistoryBodyData">
                @foreach ($history as $key => $data)
                    <div class="inventoryHistoryDiv">
                        <script>
                           
                            var inventoryItems = [];
                        </script>    
                        <div class="inventoryHistoryHeadingCon">
                            <label class="inventoryHistoryDateHeading">{{ $key }}</label>
                            <img class="printInventory" src="icons/print.png" onclick="printDiv('{{ $key }}',wName,wLocation)">
                        </div>
                        <table class="inventoryTableList">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody >
                                @foreach ($data as $row)
                                        <script>
                                            var obj = {
                                                "productName": "{{ $row->products->name }}",
                                                "productQuantity":"{{ $row->quantity }}",
                                                "productValue": "{{ $row->value}}"
                                            };
                                            inventoryItems.push(obj);
                                            wName = "{{ $row->warehouse->wname }}";
                                            wLocation = "{{ $row->warehouse->wlocation }}"
                                        </script>
                                    <tr>
                                    <td>{{ $row->products->name }}</td> 
                                    <td>{{ $row->quantity }}</td>
                                    <td>&#8373 {{ $row->value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="inventoryHistoryDivider"></div>
                    </div>
                    <script>
                        inventoryHistoryPayLoad['{{ $key }}'] = inventoryItems;
                    </script>
                @endforeach
                </div>
            </div>
        </div>
    </div>

    
@endsection

@section('pageModals')
    <div id="goodsReceptionBlockPopUp">
        <div id="goodsReceptionBlockPopUpCenter">
            <img onclick="closeRequisitionBlock()" class="cancel" src= {{ asset('icons/cancel.png') }} >
            <img class="requestGoodsImg" src={{ asset('images/goods.png') }}>
            <div id="goodsReceptiionHead">
                <label class="iventoryPopUpHead">Request Goods Reception</label>
            </div>
            <textarea id="goodsReceptionRequestReason" placeholder="Reason" name="w3review" rows="5" cols="50"></textarea>
            <button class="buttonDesign" id="goodsReceptionRequestBtn"onclick="requestGoodsReception()">Submit</button>
           
        </div>
    </div>

    <div id="goodsReceptionSentPopUp">
        <div id="goodsReceptionSentPopUpCenter">
        <img onclick="closeRequisitionBlock()" class="cancel" src= {{ asset('icons/cancel.png') }}>
        <div class="addProductTitleCon">
            <label class="popUpTitle">Request Requisition</label>
        </div>
        <div id="goodsReceptionSentCon">
            Goods reception request already placed, please contact director for clearance.
        </div>
        </div>
    </div>

     
    @empty($data)
        <script>
            console.log("UnAuthorized");
            document.getElementById('warehouse0PopUp').style.display = 'block';
        </script>
    @endempty
    <div class="showLoading"id="loading">
        <div class="loader">Loading...</div>
    </div>
@endsection

