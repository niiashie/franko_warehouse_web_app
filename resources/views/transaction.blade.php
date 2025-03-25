@extends('layouts.dashboard')

@section('pageCss')
  <link rel="stylesheet" href={{ asset('css/product.css') }} />
  <link rel="stylesheet" href={{ asset('css/transaction.css') }} />
  <link rel="stylesheet" href={{ asset('css/setup.css') }} />
@endsection

@section('pageJs')
  <script>
      var idCounter = 0;
      var productIdCounters = [];
      var productNames = [];
      var productCost = [];
      var productIds = [];
      var productQuantities = [];
      var inventoryHistoryPayLoad = {}
      var total = 0;
      var labelCounter = 0;
      var transactionInvoiceNumber="",transactionDate="",transactionCustomerName="",transactionWarehouseName="";
      var transactionType = "";

     $(document).ready(function() { 
        addNewBatch();
        $("#addBtnSearchEntry").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#productTable tbody tr").filter(function() {
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

     function showAddTransaction(){
        document.getElementById("productBody").style.display = "none";
        document.getElementById("transactionHistory").style.display = "none";
        document.getElementById("transactionBody").style.display = "block";
     }

     function showTodaysTransaction(){
        document.getElementById("productBody").style.display = "block";
        document.getElementById("transactionBody").style.display = "none";
        document.getElementById("transactionHistory").style.display = "none";
     }

     function showTransactionHistory(){
        document.getElementById("productBody").style.display = "none";
        document.getElementById("transactionBody").style.display = "none";
        document.getElementById("transactionHistory").style.display = "block";
     }

     


     function val() {
        d = document.getElementById("transactionTypeSelectStyle").value;
      
        var parentCon = document.getElementById('transactionAdditionalInputCon');
        while(parentCon.firstChild) {
            parentCon.removeChild(parentCon.firstChild);
        }

        //Declaring optional fields
        //Invoice Number
        var invoiceNoInput = document.createElement('input');
        invoiceNoInput.classList.add('inputDesign');
        invoiceNoInput.id = "transactionInvoiceNo";
        invoiceNoInput.placeholder = "Invoice Number";
        invoiceNoInput.type = 'number';
        invoiceNoInput.addEventListener(
            'change',function(){
                transactionInvoiceNumber = document.getElementById('transactionInvoiceNo').value;
            }
        );

         //Date Select
        var dateNoInput = document.createElement('input');
        dateNoInput.classList.add('inputDesign');
        dateNoInput.id = "transactionDateSelect";
        dateNoInput.placeholder = "Date";
        dateNoInput.type = 'date';
        dateNoInput.addEventListener(
            'change',function(){
                transactionDate = document.getElementById("transactionDateSelect").value;
            }
        )

        //Warehouse Name 
        var warehouseInput = document.createElement('input');
        warehouseInput.classList.add('inputDesign');
        warehouseInput.id = "transactionWarehouseName";
        warehouseInput.placeholder = "Warehouse Name";
        warehouseInput.type = 'text';
        warehouseInput.addEventListener(
            'change',function(){
                transactionWarehouseName = document.getElementById("transactionWarehouseName").value;
            }
        );

        //Customer Name
        var customerNameInput = document.createElement('input');
        customerNameInput.classList.add('inputDesign');
        customerNameInput.id = "transactionCustomerName";
        customerNameInput.placeholder = "Customer Name";
        customerNameInput.type = 'text';
        customerNameInput.addEventListener(
            'change',function(){
                transactionCustomerName = document.getElementById("transactionCustomerName").value;
            }
        );

        //Make Transaction Btn
        var makeTransactionBtn = document.createElement('button');
        makeTransactionBtn.classList.add('buttonDesign');
        makeTransactionBtn.id = "transactionButton";
        makeTransactionBtn.innerHTML = "Make Transaction";
        makeTransactionBtn.onclick = function() {

             addToStock(); 
        };

        
        if(d == "retail_requisition"){
           //parentCon.appendChild(invoiceNoInput);
           parentCon.appendChild(dateNoInput);
           parentCon.appendChild(makeTransactionBtn);
           transactionType = "Retail Requisition";       
          
        }
        else if(d == "warehouse_requisition"){
           parentCon.appendChild(warehouseInput); 
           //parentCon.appendChild(invoiceNoInput);
           parentCon.appendChild(dateNoInput);
           parentCon.appendChild(makeTransactionBtn);
           transactionType = "Kumasi Requisition";
        }
        else if(d == "customer_cash_sales" || d == "customer_credit_sales"){
           parentCon.appendChild(customerNameInput); 
           //parentCon.appendChild(invoiceNoInput);
           parentCon.appendChild(dateNoInput);
           parentCon.appendChild(makeTransactionBtn);

           if(d == "customer_cash_sales"){
             transactionType = "Customer Cash Sales";
           }else{
             transactionType = "Customer Credit Sales";
           }
        }
        
       
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
        let temp = document.getElementById('receiveTransItemBox');
        document.getElementById("productRowNumber"+idCounter).innerHTML = ""+temp.childElementCount+"."
        
     }

    function addNewBatch(){

        idCounter = idCounter+1;
        processBatch();  
        productIdCounters.push(idCounter);
    }

    function showError(title,message){
                Swal.fire({
                        icon: 'error',
                        title: title,
                        text: message
            });
    }

    function selectTransactionType(){
        document.getElementById('transactionTypeCon').style.display = "block";
    }

    function closeTransactionSelect(){
        document.getElementById('transactionTypeCon').style.display = "none";
    }

    function addToStock(){
        console.log("called");
        if(transactionType == ""){
           showError("Error","Please select transaction type to proceed");
        }
        // else if(transactionInvoiceNumber == ""){
        //     showError("Error","Please enter transaction invoice number to proceed");
        // }
        else if(transactionDate == ""){
            showError("Error","Please select transaction date to proceed"); 
        }
        else{
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
                "user_id":"{{ session()->get('id') }}",
                "transaction_type":transactionType,
                "transaction_invoice":transactionInvoiceNumber,
                "transaction_date": transactionDate,
                "transaction_customer": transactionCustomerName,
                "transaction_warehouse": transactionWarehouseName
            }
            if(errorCounter>0){
                showError("Error","Some rows are empty");
                document.getElementById('transactionTypeCon').style.display = 'none';
            }else{
                
                
                var json = JSON.stringify(resultArray);
                console.log(json);

                Swal.fire({
                    title: 'Make Transaction',
                    text: "Do you really want to make this transaction",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0093E9',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Make Transaction'
                }).then((result) => {  
                    if (result.value) {
                        document.getElementById("loading").style.display = "block";
                        var inventoryRequest  = $.ajax({
                                                    url:"/transact",
                                                    method: "GET",
                                                    data: {
                                                        res:json
                                                    }
                        });
                        inventoryRequest.done(function (response, textStatus, jqXHR){
                            console.log("Response is :"+ response);
                            if(response == 'Success'){
                                document.getElementById("loading").style.display = "none";
                                Swal.fire({
                                        title: 'Transaction Successful',
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
                                console.log("Running transaction Error");
                                showError("Error",response);
                            }
                        });

                        inventoryRequest.fail(function (){
                            document.getElementById("loading").style.display = "none";
                            // Show error
                            showError("Failure","Please ensure server is active");
                        });


                    }

                });
                
                
            }
        }

        
    
        //alert(json);
    }

    function reverseTransaction(invoiceNo){
        Swal.fire({
                    title: 'Reverse Transaction',
                    text: "Do you really want to reverse this transaction",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0093E9',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Reverse Transaction'
                }).then((result) => {  
                    if (result.value) {
                        document.getElementById("loading").style.display = "block";
                        var inventoryRequest  = $.ajax({
                                                    url:"/reverse",
                                                    method: "GET",
                                                    data: {
                                                        invoiceNumber:invoiceNo
                                                    }
                        });
                        inventoryRequest.done(function (response, textStatus, jqXHR){
                            console.log("Response is :"+ response);
                            if(response == 'Success'){
                                document.getElementById("loading").style.display = "none";
                                Swal.fire({
                                        title: 'Transaction Reversal Successful',
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
                                console.log("Running transaction Error");
                                showError("Error",response);
                            }
                        });

                        inventoryRequest.fail(function (){
                            document.getElementById("loading").style.display = "none";
                            // Show error
                            showError("Failure","Please ensure server is active");
                        });


                    }

                });
                
    
    }

    function printTransaction(wName,wLocation){
        var date = document.getElementById('transactionHistoryTodayDate').innerHTML;
        var printWindow = window.open('', '', 'height=800,width=1200');
        printWindow.document.write('<html><head><title>Transactions</title></head>');  
        printWindow.document.write(
           '<style>'+
           ' table, th, td {'+
           'border: 1px solid black; text-align:center;font-size:11px;}'+
           '#historyTable{'+
            'width:100%;height:auto;position:relative;'+
           '}'+
           '</style>'+ 
           '<body>'
        );
        printWindow.document.write('<div style="width:100%;height:auto;padding-top:5px;padding-bottom:5px;text-align:center;">'+
          '<h2>Transaction History<h2>'+  
          '</div>'+
          '<div style="width:100%;height:auto;top:45px;text-align:center;position:absolute">'+
          '<h3>'+wName+'('+wLocation+')</h3>'+
          '</div>'+
          '<div style="width:100%;height:auto;top:80px;text-align:center;position:absolute">'
          +'<h3>'+""+'</h3>'+
          '</div>'
        );

        //MainBody
        printWindow.document.write(
            '<div style="width:90%;left:5%;top:140px;padding-top:5px;padding-bottom:5px;height:auto;position:absolute">'   
        );

        printWindow.document.write(
            '<div style="width:100%;height:auto;position:relative">'
        );
        var overallQuantity = 0;
        var overallValue = 0;
        for (var key in inventoryHistoryPayLoad) {
            if (inventoryHistoryPayLoad.hasOwnProperty(key)) {
                //console.log(key + " -> " + inventoryHistoryPayLoad[key]);
                printWindow.document.write(
                   '<div style="width:100%;height:auto;margin-top:10px;position:relative;">'+
                   '<label style="font-weight:bold;font-size:11px;">#'+key+'</label>'
                   
                );
                printWindow.document.write(
                    '<div style="width:100%;height:auto;padding-top:10px;padding-bottom:5px;position:relative;">'
                );
                printWindow.document.write(
                    '<table id="historyTable">'+
                    '<thead>'+
                    '<tr>'+
                    '<th>Product Name</th>'+
                    '<th>Quantity</th>'+
                    '<th>Value</th>'+
                    '<th>Type</th>'+
                    '<th>Customer</th>'+
                    '</tr>'+
                    '</thead>'+
                    '<tbody>'

                );
                var subTotalQuantity = 0;
                var subTotalValue = 0;
                for(var i=0;i<inventoryHistoryPayLoad[key].length;i++){
                    printWindow.document.write(
                        '<tr>'+
                            '<td>'+inventoryHistoryPayLoad[key][i]['productName']+'</td>'+
                            '<td>'+inventoryHistoryPayLoad[key][i]['productQuantity']+'</td>'+
                            '<td> &#8373 '+inventoryHistoryPayLoad[key][i]['productValue']+'</td>'+
                            '<td>'+inventoryHistoryPayLoad[key][i]['transactionType']+'</td>'+
                            '<td>'+inventoryHistoryPayLoad[key][i]['customerName']+'</td>'+
                        '</tr>'
                    );

                    subTotalQuantity = subTotalQuantity + parseInt(inventoryHistoryPayLoad[key][i]['productQuantity']);
                    subTotalValue = subTotalValue + parseFloat(inventoryHistoryPayLoad[key][i]['productValue']);

                    overallQuantity = overallQuantity + parseInt(inventoryHistoryPayLoad[key][i]['productQuantity']);
                    overallValue = overallValue + parseFloat(inventoryHistoryPayLoad[key][i]['productValue']);
                }
                printWindow.document.write(
                    '<tr>'+
                            '<td>Total</td>'+
                            '<td>'+subTotalQuantity+'</td>'+
                            '<td> &#8373 '+subTotalValue+'</td>'+
                            '<td></td>'+
                            '<td></td>'+
                    '</tr>'
                );
                
                printWindow.document.write(
                    '</tbody>'+
                    '</table>'+
                    '</div>'+
                    '</div>'
                    
                );

            }
        }

        printWindow.document.write(
            '<div style="width:100%;height:auto;margin-top:20px;position:relative;">'+
            '<label style="right:5px;color:black;font-weight: bold;font-size:15px;position:absolute">Total Quantity : '+overallQuantity+'</label>'+
            '</div>'
        );

        printWindow.document.write(
            '<div style="width:100%;height:auto;margin-top:40px;position:relative;">'+
            '<label style="right:5px;color:black;font-weight: bold;font-size:15px;position:absolute">Total Value : &#8373 '+overallValue+'</label>'+
            '</div>'
        );
  
        printWindow.document.write(
           '</div>'+
           '</div>'+
           '</body>'
        );

        printWindow.document.close();  
        //printWindow.print(); 
        
    }

    function getCurrentDate(month){
        var currentMonth = parseInt(month);
        if(currentMonth == 1){
            return "January";
        }
        else if(currentMonth == 2){
            return "Febuary";
        }
        else if(currentMonth == 3){
            return "March";
        }
        else if(currentMonth == 4){
            return "April";
        }
        else if(currentMonth == 5){
            return "May";
        }
        else if(currentMonth == 6){
            return "June";
        }
        else if(currentMonth == 7){
            return "July";
        }
        else if(currentMonth == 8){
            return "August";
        }
        else if(currentMonth == 9){
            return "September";
        }
        else if(currentMonth == 10){
            return "October";
        }
        else if(currentMonth == 11){
            return "November";
        }
        else if(currentMonth == 12){
            return "December";
        }
   
    }

    function restructureDate(date){
        var year = date.substring(0,4);
        var month = date.substring(5,7);
        var day = date.substring(8,10);

        

        return day+" "+getCurrentDate(month)+" "+year;
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

  
    <div class="sideMenuIconContainer" onclick="loadInventory()">
        <img class="sideMenuCenterIcon" src={{ asset('icons/box2.png') }}>
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Inventory</label>
        </div>
        <label style="display:none" id="inventoryURL">{{ route('inventoryPage') }}</label>
    </div>
    

    <div class="sideMenuIconContainer" id="selectedMenu">
        <img class="sideMenuCenterIcon" src={{ asset('icons/transaction.png') }}>
        <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Transaction</label>
        </div>
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
   
   
  
    @foreach ($today as $row)
    <script>
       total = total + parseFloat('{{ $row->value }}')
    </script>
    @endforeach    

    <div id="productBody">
        <div class="productHead">
            <label class="productTitle">Transactions</label>
            @if(session()->get('role') !='Accountant')
                <div class="addTrans" onclick="showAddTransaction()">
                <img class="addBtnConIcon" src="/icons/plus.png">
                <label class="addBtnConLable">Add Transaction</label>
                </div>
            @endif    
            <div class="transHistory" onclick="showTransactionHistory()">
               <img class="addBtnConIcon" src="/icons/save.png">
               <label class="addBtnConLable">Transaction History</label>
            </div>

            <div id="searchBtnCon2">
               <img class="addBtnConIcon" src="/icons/ashSearch.png">
               <input id="addBtnSearchEntry" type="text" placeholder="Search Transaction">
            </div>

        </div>
        <div class="productContent">
            <div id="todayTransCon">
                <label id="todayText">Today's Transaction</label>
            </div>
            <div class="tableContainer2">
                <table id="productTable">
                    <thead>
                        <tr>
                          <th>Product Name</th>
                          <th>Quantity</th>
                          <th>Unit Cost</th>
                          <th>Amout</th>
                          <th>Time</th>
                        </tr> 
                    </thead> 
                    <tbody>
                        @foreach ($today as $row)
                            <tr>
                                <td>{{ $row->products->name }}</td>
                                <td>{{ $row->quantity }}</td>
                                <td>&#8373 {{ $row->products->price }}</td>
                                <td>&#8373 {{ $row->value }}</td>
                                <td>{{ $row->created_at->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="inventoryTotalContainer">
                <div class="productCategoryTopDivider"> </div>
                <label id="inventoryTotalLable">Total Value:</label>  
                <label id="inventoryTotalLableTxt">&#8373 30000</label>
            </div>
            <script>
                document.getElementById('inventoryTotalLableTxt').innerHTML = "&#8373 "+total;
            </script>
        </div>
    </div>
    
    @foreach ( $data as $result )
            <script>
                productNames.push('{{ $result->name }}');
                productCost.push('{{ $result->price }}');
                productIds.push('{{ $result->id }}');
                productQuantities.push('{{ $result->pivot->quantity }}');
              

                
            </script>
    @endforeach

    <div id="transactionBody">
        <div class="productHead">
            <label class="productTitle">Add Transaction</label>
            <div class="addTrans" onclick="showTodaysTransaction()">
                <img class="addBtnConIcon" src="/icons/list.png">
                <label class="addBtnConLable">Transactions</label>
             </div>
             <div class="transHistory" onclick="showTransactionHistory()">
                <img class="addBtnConIcon" src="/icons/save.png">
                <label class="addBtnConLable">Transaction History</label>
             </div>
        </div>
        <div class="productContent">
            <div id="receiveTransBox">
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
                    <button id="addToStockBtn" onclick="selectTransactionType()">Make Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <div id="transactionHistory">
        <div class="productHead">
            <label class="productTitle">Transaction History</label>
            <div class="addTrans" onclick="printTransaction(wName,wLocation)">
                <img class="addBtnConIcon" src="/icons/white_print.png">
                <label class="addBtnConLable">Print Transaction</label>
             </div>
            <div class="transHistory" onclick="showTodaysTransaction()">
                <img class="addBtnConIcon" src="/icons/list.png">
                <label class="addBtnConLable">Transactions</label>
            </div>
        </div>
        <div class="productContent">
            <div id="transactionHistoryDateSelect">
                <form action="{{ route('transactionHistory') }}" method="POST">
                    @csrf
                    <input class="inputDesign" name="transactionHistoryDate" id="transactionHistoryDateSelectTxt" type="text" placeholder="Select Transaction History Date" onfocus="(this.type='date')">
                        @error('transactionHistoryDate')
                            <script>
                                showError('Error',"{{ $message }}");
                            </script>
                        @enderror
                    <button type="submit" id="transactionHistoryDateBtn" class="buttonDesign" >Search</button>
                </form>
            </div>
            <div id="transactionHistoryBodyCon">
                <label id="transactionHistoryTodayDate"></label>
                <div id="transactionHistoryBodyData">
                    @foreach ($history as $key => $data)
                        <div class="inventoryHistoryDiv">
                           <div class="inventoryHistoryHeadingCon">
                               <label class="inventoryHistoryDateHeading">
                                   #{{ $key }}
                               </label>
                               @if(session()->get('role') =='Director')
                                    <button onclick="reverseTransaction('{{ $key }}')" class="reverseTransactionBTN">Reverse</button>
                               @endif
                               
                           </div>
                           <table class="inventoryTableList">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Value</th>
                                        <th>Type</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody >
                                     <script>
                                var wName,wLocation;
                                var inventoryItems = [];
                            </script>    
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $row->products->name }}</td> 
                                            <td>{{ $row->quantity }}</td>
                                            <td>&#8373 {{ $row->value }}</td>
                                            <td>{{ $row->transaction_type }}</td>
                                            <td>{{ $row->customer_name }}</td>
                                            <td>{{ $row->transaction_date }}</td>
                                        </tr>
                                        <script>
                                            var obj = {
                                                "productName": "{{ $row->products->name }}",
                                                "productQuantity":"{{ $row->quantity }}",
                                                "productValue": "{{ $row->value}}",
                                                "transactionType":"{{ $row->transaction_type }}",
                                                "customerName":"{{ $row->customer_name }}",
                                                "transactionDate":"{{ $row->transaction_date }}"
                                            };
                                            var selectedDate = "{{ $row->created_at }}";
                                            
                                            document.getElementById("transactionHistoryTodayDate").innerHTML = restructureDate(selectedDate);
                                            document.getElementById("transactionHistoryTodayDate").style.display = "none";
                                            inventoryItems.push(obj);
                                            wName = "{{ $row->warehouse->wname }}";
                                            wLocation = "{{ $row->warehouse->wlocation }}"
                                        </script>
                                    @endforeach
                                </tbody>
                           </table>
                          
                        </div>
                        <script>
                             inventoryHistoryPayLoad['{{ $key }}'] = inventoryItems;
                        </script>
                       
                   @endforeach
                </div>
            </div>
        </div>

    </div>
    
    @if($display == "history")
        <script>
            showTransactionHistory();
        </script>
    @else
        <script>
            showTodaysTransaction();
        </script>
    @endif

@endsection

@section('pageModals')
    @empty($data)
    <script>
        console.log("UnAuthorized");
        document.getElementById('warehouse0PopUp').style.display = 'block';
    </script>
    @endempty
    <div id="transactionTypeCon">
       <div id="transactionTypeCenter">
           <div id="cancelCon">
              <img onclick="closeTransactionSelect()" class="cancel" src={{ asset('icons/cancel.png') }}>
           </div>
           <div id="transactionTypeSelectCon">
                <div id="transactionTypeSelectDiv">
                    <select class="selectDesign"id="transactionTypeSelectStyle" onchange="val()">
                        <option value="default">Select Transaction Type</option>
                        <option value="retail_requisition">Retail Requisition</option>
                        <option value="warehouse_requisition">Kumasi Requisition</option>
                        <option value="customer_credit_sales">Credit Sales Customers</option>
                        <option value="customer_cash_sales">Non Credit Sales Customers </option>
                    </select>
                </div>
                <div id="transactionAdditionalInputCon">

                </div>
           </div>
       </div>
    </div>
    <div class="showLoading"id="loading">
        <div class="loader">Loading...</div>
    </div>
@endsection


