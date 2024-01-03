function loadWarehouse(){
    var warehouseURL = document.getElementById('warehouseURL').innerHTML;
    window.location.replace(warehouseURL);
}

function loadProducts(){
    var productsURL = document.getElementById('productsURL').innerHTML;
    window.location.replace(productsURL);
}

function loadInventory(){
    var inventoryURL = document.getElementById('inventoryURL').innerHTML;
    window.location.replace(inventoryURL);
}

function loadStock(){
   //stockURL
   var inventoryURL = document.getElementById('stockURL').innerHTML;
   window.location.replace(inventoryURL);
}

function loadTransactions(){
    var transactionURL = document.getElementById('transactionURL').innerHTML;
    window.location.replace(transactionURL);
}

function loadHome(){
   //homeURL
   var transactionURL = document.getElementById('homeURL').innerHTML;
   window.location.replace(transactionURL);
}

function loadProformer(){
   var proformerURL = document.getElementById('proformerURL').innerHTML;
   window.location.replace(proformerURL);
}

function showLogOut(){
    document.getElementById('logOutContainer').style.display = "block";
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

 function formatDate(day){
    var year = day.substring(0, 4);
    var month = day.substring(5,7);
    var monthDay = day.substring(8,10);
    
    return ""+monthDay+" "+getCurrentDate(month)+" "+year;
 }

 function getTodaysDate(){
   var today = new Date();
   var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
   var month = today.getMonth()+1;

   return ""+today.getDate()+" "+getCurrentDate(month)+" "+today.getFullYear();
 }