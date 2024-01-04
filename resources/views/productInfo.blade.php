<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href={{ asset('css/productDetail.css') }} />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script type="text/javascript" src="../js/jquery.js"></script> 
    <script type="text/javascript" src="../js/setup.js"></script> 
    <title>Product Transactions</title>
</head>
<script>
   $(document).ready(function() {
      var productId = "{{ $id }}";
      console.log("Current product Id is: "+productId);
      getProductInfo(productId);
   });

   function getProductInfo(productId){
         var productsRequest  = $.ajax({
                                                    url:"/productDetail",
                                                    method: "GET",
                                                    data: {
                                                       pid:productId
                                                    }
         });
         productsRequest .done(function (response, textStatus, jqXHR){ 
            document.getElementById('productName').innerHTML = response[0]['product'];
            console.log(response);
            var transactions = response[0]['transaction'];
            console.log(transactions.length);
                  for(var i=0;i<transactions.length;i++){
                     var category = transactions[i]['category'];
                 
                  var detailCon = document.createElement('div');
                  detailCon.classList.add("detailContainer");

                  //Date Text
                  var productDate = transactions[i]['date'];
                  console.log(formatdate(productDate));
                  var dateLabel = document.createElement('label');
                  dateLabel.classList.add("dateLabel");
                  dateLabel.innerHTML = formatdate(productDate);
                  
                  //Quantity Text
                  var quantityDiv = document.createElement('div');
                  quantityDiv.classList.add('quantityDiv');

                  var quantityLabel = document.createElement('label');
                  quantityLabel.classList.add('quantityLabel');
                  quantityLabel.innerHTML = "Quantity   :";
                  quantityDiv.appendChild(quantityLabel);

                  var quantityValue = document.createElement('label');
                  quantityValue.classList.add('quantityValue');
                  quantityValue.innerHTML = transactions[i]['quantity'];
                  quantityDiv.appendChild(quantityValue);

                  //Value Text
                  var valueDiv = document.createElement('div');
                  valueDiv.classList.add('valueDiv');
                  
                  var valueLabel = document.createElement('label');
                  valueLabel.classList.add('quantityLabel');
                  if(category == "change"){
                     valueLabel.innerHTML = "Previous:";
                  }
                  else{
                     valueLabel.innerHTML = "Value   :";
                  }
                 
                  
                  valueDiv.appendChild(valueLabel);

                  var valueQuantity = document.createElement('label');
                  valueQuantity.classList.add('quantityValue');
                  valueQuantity.innerHTML = transactions[i]['value'];
                  valueDiv.appendChild(valueQuantity);

                  
                  if(category == "transaction"){
                     var image = document.createElement('img');
                     image.setAttribute("src","../icons/red_arrow.png");
                     image.classList.add("redImage");

                     detailCon.appendChild(image);
                  }else if(category == "received"){
                     var image2 = document.createElement('img');
                     image2.setAttribute("src","../icons/green_arrow.png");
                     image2.classList.add("redImage");
                     detailCon.appendChild(image2);
                  }else{
                     var image2 = document.createElement('img');
                     image2.setAttribute("src","../icons/shuffle.png");
                     image2.classList.add("redImage");
                     detailCon.appendChild(image2);
                  }

                  detailCon.appendChild(dateLabel);
                  detailCon.appendChild(quantityDiv);
                  detailCon.appendChild(valueDiv);

                  document.getElementById("centerCon").appendChild(detailCon); 
               }
            
           
           
      
         });
   }

   function formatdate(productDate){
      var year = productDate.substring(0, 4);
      var month = productDate.substring(5,7);
      var day = productDate.substring(8,10);

      return ""+day+"/"+getCurrentDate(month)+"/"+year;
   }
</script>
<body>
    <div id="bottomMenu"></div>
    <div id="topContainer">
       <label id="topContainerTxt">Product Transaction History</label>
    </div>
    <div id="centerPage">
       <div id="upperCon">
          <label id="productName">Aminovit</label>
       </div>
       <div id="mainCenterCon">
         <div id="centerCon">
            <div id="arrowIndicatorCon">
               <div id="leftArrowIndicatorCon">
                  <img src={{ asset('icons/red_arrow.png') }} class="redArrow">
                  <label class="goodsOut">Products Out</label>
               </div>
               <div id="rightArrowIndicatorCon">
                  <img src={{ asset('icons/green_arrow.png') }} class="redArrow">
                  <label class="goodsOut">Products In</label>
                </div>
               <div id="shuffleIndicatorCon">
                  <img src={{ asset('icons/shuffle.png') }} class="redArrow">
                  <label class="goodsOut">Stock Change</label>
               </div>
              
            </div>
         </div>
       </div>
    </div>
</body>
</html>