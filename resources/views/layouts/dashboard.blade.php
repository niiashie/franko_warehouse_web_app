<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href={{ asset('css/setup.css') }} />
    <link rel="stylesheet" href={{ asset('css/sweetalert2.css') }} />
    <script src={{ URL("js/sweetalert2.all.js") }}></script>
    <script type="text/javascript" src="../js/jquery.js"></script> 
    <script type="text/javascript" src="../js/setup.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    @yield('pageCss')
    <title>Document</title>
</head>
<script>
   var dashProductNames = [];
   var dashproductIds = [];
   var selectedProductId,selectedProductName;
   var currentRole = "";
   $(document).ready(function() {
        //console.log(localStorage.getItem("username"));
        document.getElementById("selectedMenu").style.backgroundColor = "#dddddd";
        var today = new Date();
        var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
        var month = today.getMonth()+1;
        document.getElementById('currentDate').innerHTML = "{{ session()->get('warehouse_name') }} / "+today.getDate()+" "+getCurrentDate(month)+" "+today.getFullYear();
        currentRole = "{{ session()->get('role') }}"
        if(currentRole == "Accountant"){
          console.log("Present");
          //document.getElementsByClassName('hiddenTab').style.display = 'gone';
        }
        //console.log("Current role: "+currentRole);
        getProductList();
   });
  
   function cancelLogOut(){
      document.getElementById("logOutContainer").style.display = "none";
   }
      
   function checkLogIn(){
      document.getElementById("sessionExpiredContainer").style.display = "block";
   }

   function setUpProductSearch(){
      var searchField  = document.getElementById('searchEntry');
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
                            for (i = 0; i < dashProductNames.length; i++) {
                            /*check if the item starts with the same letters as the text field value:*/
                            if (dashProductNames[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                                /*create a DIV element for each matching element:*/
                            
                                b = document.createElement("DIV");
                                /*make the matching letters bold:*/
                                b.innerHTML = "<strong>" + dashProductNames[i].substr(0, val.length) + "</strong>";
                                b.innerHTML += dashProductNames[i].substr(val.length);
                                /*insert a input field that will hold the current array item's value:*/
                                b.innerHTML += "<input type='hidden' value='" + dashProductNames[i] + "'>";
                                /*execute a function when someone clicks on the item value (DIV element):*/
                                b.addEventListener("click", function(e) {
                                    var a = dashProductNames.indexOf(this.getElementsByTagName("input")[0].value);
                                    var currentId = element.target.id;
                                    var myArray = currentId.split('_');
                                    var currentIdIndex = myArray[1];
                                    console.log("Current index: "+dashproductIds[a]);
                                    selectedProductId = dashproductIds[a];
                                    selectedProductName = a;
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

   function getProductList(){
         var productsRequest  = $.ajax({
                                                    url:"/productList",
                                                    method: "GET",
         });
         productsRequest .done(function (response, textStatus, jqXHR){ 
            console.log(response.length);
            for(var i=0;i<response.length;i++){
               dashProductNames.push(response[i]['name']);
               dashproductIds.push(response[i]['id']);
            }
            setUpProductSearch();
         });

   }

   function showError(title,message){
                Swal.fire({
                            icon: 'error',
                            title: title,
                            text: message
                });
   }

  
   function searchProductsOnClick(){
      if(selectedProductId == null){
         showError("Invalid Input","Please select a product to proceed");
      }else{
         location.href = "/products/"+selectedProductId;
      }
     // alert("hi");
   }


  
</script>
@yield('pageJs')
<body>
    <div id="mainBody">
       @yield('pageContent')
    </div>
    <div id="warehouse0PopUp">
      <div id="warehousePopUpCenter">
          <img id="warehouseImgCenter" src="{{ asset('images/oops.png') }}">
          <div id="warehousePopUpTitleCon">
               <label class="popUpTitle">UnAuthorized Access</label>
          </div>
          <div id="warehousePopUpSub">
               <label id="warehousePopUpSubTxt"> Staff is currently unassigned to any warehouse</label>
          </div>
      </div>
    </div>
    <!--Menu Body-->
    <div id="bottomMenu">

    </div>
    <div id="sideMenu">
       <div id="iconSetCon">
         @yield('pageMenu')
         
       </div>
    </div>
    <div id="topMenu">
       <label id="heading">FRANKO WAREHOUSE</label>
      
       <div id="rightSearchContainer">
          <div id="searchIconCon">
            <img id="centerSearch" src= {{ asset('icons/search.png') }} onclick="searchProductsOnClick()">
          </div>
          <input id="searchEntry" type="text" placeholder="Enter Products...">
          <div id="userNameCon">
             <label id="currentDate"></label>
          </div>
       </div>
    
    </div>
    

    <!-- Modal shown when session is expired -->
    <div id="sessionExpiredContainer">
       <div id="sessionExpiredContainerCenter">
          <div class="oopsContainer">
             <img class="oops" src= {{ asset('images/oops.png') }}>
          </div> 
          <div class="oopsTextCon">
             <label class="oopsText">Session expired,please re-login</label>
          </div>
          <a href={{ route('loginPage') }}>
            <button class="buttonDesign" id="sessionBtn">Login</button>
          </a>
       </div>
    </div>

    <!-- Log Out Modal -->
    <div id="logOutContainer">
      <div id="logOutContainerCenter">
         <div class="oopsContainer">
            <img class="oops" src= {{ asset('images/exit.png') }}>
         </div> 
         <div class="oopsTextCon">
            <label class="dialogTitle">LOGOUT</label>
         </div>
         <div class="oopsTextCon2">
            <label class="oopsText">Do you really want to log out ?</label>
         </div>
         <div id="btnConfirmationCon">
            <a href={{ route('loginPage') }}>
               <button class="buttonDesign2" id="okayBtn">Yes</button>
            </a>
            <button class="buttonDesign" id="cancelBtn" onclick="cancelLogOut()">No</button> 
         </div>
      </div>
    </div>
  

   @yield('pageModals')

    @if(!Session::has('name'))
      <script>
         checkLogIn();
      </script>
    @endif

   
</body>
</html>