@extends('layouts.dashboard')

@section('pageJs')
<script>
      function loadInventory(){
         var inventoryURL = document.getElementById('inventoryURL').innerHTML;
         window.location.replace(inventoryURL);
      }

      function loadProducts(){
         var productsURL = document.getElementById('productsURL').innerHTML;
         window.location.replace(productsURL);
      }

      function loadHome(){
         var homeURL = document.getElementById('homeURL').innerHTML;
         window.location.replace(homeURL);
      }
    var wareHouseIds = [];
    var selectedId;
    var unmanagedWareHousIds = [];
    var managedIds = [];
    var unmanagedWareHouseName = [];
    var currentStaffId;
    var currentWareHouseId,cWID;
    var currentManageId;
    var userCode;
    var goodsReceptionStatus;

    $(document).ready(function() {
       //document.getElementById("selectedMenu").style.backgroundColor = "#dddddd";
       //getWareHouses();
       //getPendingRegistration();

        firebaseConfig = {
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
       
        //var ref = firebase.database().ref("franko_ware_house/goods_reception/11");
        

        $("#addBtnSearchEntry").on("keyup", function() {
                    var value = $(this).val().toLowerCase();
                    $("#wareHouseTable tbody tr").filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
         });
    });

    function rolesOnClick(){
      document.getElementById("productBody").style.display = "none";
      document.getElementById("rolesBody").style.display = "block";
    }
    
    function wareHouseOnClick(){
      document.getElementById("productBody").style.display = "block";
      document.getElementById("rolesBody").style.display = "none";
    }

    function addWareHousePopUp(){
      document.getElementById("addWareHousePopup").style.display = "block"; 
    }

    function closeAddWareHouse(){
     document.getElementById("addWareHousePopup").style.display = "none"; 
    }

    function closeAssignStaff(){
       document.getElementById("assignStaffPopUp").style.display = "none";
    }

    function closeAssignWareHouse(){
       document.getElementById("assignWarehousePopUp").style.display = "none";
    }

  

 

  


   
   function assignWareHouse(element){
     console.log($(element).closest('tr').index());
     var selectedWareHouseIndex = $(element).closest('tr').index();
     //alert(unmanagedWareHousIds[selectedWareHouseIndex]);

     currentWareHouseId = unmanagedWareHousIds[selectedWareHouseIndex];
     var wareHouseName = unmanagedWareHouseName[selectedWareHouseIndex];
     document.getElementById("assignWarehousePopUp").style.display = "block";
     document.getElementById("wareHouseNameTxt").innerHTML = wareHouseName;
     /*var rowJavascript = element.parentNode.parentNode;
     var rowjQuery = $(element).closest("tr");
     alert("Assigning warehouse id: "+rowjQuery);*/
   }

   function deleteClicked(element){


      var userId = "{{ session()->get('user_id') }}";
      var role = "{{ session()->get('role') }}"
      if(role != "Manager"){
         showError("UnAuthorized","You are not allowed to delete ware houses");
      }else{
         Swal.fire(
        {
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
        }
       ).then((result) => {
              if(result.value){
                 var input = result.value;
                

                 if(input == userId){

                       Swal.fire({
                                   title: 'Delete WareHouse',
                                   text: "Do you really want to delete this ware house",
                                   icon: 'warning',
                                   showCancelButton: true,
                                   confirmButtonColor: '#0093E9',
                                   cancelButtonColor: '#d33',
                                   confirmButtonText: 'Delete'
                                }).then((result) => {  
                                   if (result.value) {
                                      document.getElementById('loading').style.display = "block";
                                      var deleteRequest  = $.ajax({
                                               url:"{{ route('deleteWareHouse') }}",
                                               type: "GET",
                                               data:{id:element,"_token": "{{ csrf_token() }}"}
                                      });

                                      deleteRequest.done(function(response, textStatus, jqXHR){
                                       document.getElementById('loading').style.display = "none";
                                         if(response == "Delete Successful"){
                                            Swal.fire({
                                                           title: 'Successfully deleted ware house',
                                                           text: "",
                                                           icon: 'success',
                                                           showCancelButton: false,
                                                           confirmButtonColor: '#3085d6',
                                                           confirmButtonText: 'Okay'
                                                       }).then((result) => {
                                                           if (result.value) {
                                                              //location.reload();
                                                              $("#wareHouseTableBody").empty();
                                                              getWareHouses();
                                                             
                                                           }
                                                   });
                                         }
                                      });
                                }

                             });
                 }
                 else{
                    document.getElementById('loading').style.display = "none";
                    showError("UnAuthorized","Invalid user ID");
                 }
              }
       });
      }
   }

   
   
   
   function addWareHouse(){
     var wareHouseName = $("#addWareHouseName").val();
     var wareHouseLocation = $("#addWareHouseLocation").val();
     var wareHouseBranch = $("#addWareHouseBranch").val();

      if(wareHouseName.length == 0){
         showError("Empty Entry","Ware House Name Required");
      }
      else if(wareHouseLocation.length == 0){
        showError("Empty Entry","Ware House Location Required");
      }
      else if(wareHouseBranch.length == 0){
        showError("Empty Entry","Ware House Branch Required");
      }else{

        // alert("Ready to rock");
        document.getElementById('loading').style.display = "block";
        var request = $.ajax({
           url: "http://35.166.189.194/FrankoWare/php/addWareHouse.php",
           method: "POST",
           data:{
              name:wareHouseName,
              location:wareHouseLocation,
              branch:wareHouseBranch,
           }
        });

        request.done(function (response, textStatus, jqXHR){
            document.getElementById('loading').style.display = "none";
              console.log(response);
              if(response == "Success"){
                 var lowerCase = wareHouseName.toLowerCase();
                 var lowerCase2 = lowerCase.replaceAll(" ","_");
                 var lowerCase3 = lowerCase2+"_received_goods";
                 console.log("Firebase node: "+lowerCase3);
                 firebase.database().ref('franko_ware_house/goods_reception/' + lowerCase3).set({
                    warehouse: wareHouseName,
                    reason: "",
                    permission : 0,
                    access:0
                 }, (error) => {
                    if (error) {
                       showError("Please check network",error);
                    } else {
                      Swal.fire({
                             title: 'Successfully added warehouse',
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
                 });

                 
              }else{
               document.getElementById('loading').style.display = "none";
                 showError("Error",response);
              }
        });

        request.fail(function (){
           document.getElementById('loading').style.display = "none";
           // Show error
           showError("Error","Please ensure server is turned on");
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

   function  assignStaff(staffId){
     currentStaffId = staffId;
     document.getElementById("assignStaffPopUp").style.display = "block";
   }

  function acceptRegistration(id,name){
      var role = "{{ session()->get('role') }}"
         if(role != "Manager"){
            showError("UnAuthorized","You are not allowed to accept staff registrations");
         }
         else{
            console.log("Id: "+id+" name: "+name);
                     Swal.fire({
                        title: 'Accept Registration',
                        text: "Do you really want to accept registration from "+name,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0093E9',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Confirm Registration'
                     }).then((result) => { 
                     if (result.value) {
                        document.getElementById('loading').style.display = "block";
                        var request = $.ajax({
                           url: "/confirmRegistration",
                           method: "GET",
                           data:{staff_id:id,"_token": "{{ csrf_token() }}"}
                        });
                     request.done(function (response, textStatus, jqXHR){
                        document.getElementById('loading').style.display = "none";
                        console.log(response);
                        if(response == "Success"){
                           Swal.fire({
                                          title: 'Successfully confirmed registration',
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
                           document.getElementById('loading').style.display = "none";
                           showError("Failed",response)
                        }
                     });
                  }
               });
         }
     
  }

  function changeRoles(id,wId){
    //alert("Chanage roles");
    currentManageId = id;
    cWID = wId;
    //alert(managedIds[selectedWareHouseIndex]);
    document.getElementById("changeRolePopUp").style.display = "block";
   

  }



  function assignStaffToWarehouse(){
     $selectedWareHouse = $( "#warehouseSelect" ).val();
     $selectedWareHouseRole = $( "#roleSelect" ).val();
     
     console.log("staffId: "+currentStaffId+ " role: "+$selectedWareHouseRole+ " warehouse: "+$selectedWareHouse );
     

     if($selectedWareHouse == "vol"){
        showError("Invalid Entry","Please select warehouse to proceed");
     }
     else if($selectedWareHouseRole == "vol"){
        showError("Invalid Entry","Please select warehouse role to proceed");
     }
     else{
         var role = "{{ session()->get('role') }}"
         if(role != "Manager"){
            showError("UnAuthorized","You are not allowed to assign staffs");
         }
         else{
               document.getElementById('loading').style.display = "block";
               var request = $.ajax({
                     url: "/assignStaffToWarehouse",
                     method: "GET",
                     data:{
                        staff_id:currentStaffId,
                        role: $selectedWareHouseRole,
                        warehouse_id: $selectedWareHouse,
                        "_token": "{{ csrf_token() }}"
                     }
               });
               request.done(function (response, textStatus, jqXHR){
                     console.log(response);
                     if(response == "Success"){
                        document.getElementById('loading').style.display = "none";
                        Swal.fire({
                           title: 'Successfully assigned staff',
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
                        document.getElementById('loading').style.display = "none";
                        showError("Failed",response);
                     }
               });

               request.fail(function (){
                  document.getElementById('loading').style.display = "none";
                  // Show error
                  showError("Failure","Please ensure server is active");
               });
         }
      
     }
  }

  

 

  function closeChangeRoles(){
     document.getElementById("changeRolePopUp").style.display = "none";
  }

  function assignNewRole(){
     var changeRole = $("#changeRoleSelect" ).val();
     if(changeRole == "vol"){
        showError("Invalid Entry","Please select role");
     }else{
         var role = "{{ session()->get('role') }}"
         if(role != "Manager"){
            showError("UnAuthorized","You are not allowed to change staff roles");
         }
         else{
               document.getElementById('loading').style.display = "block";
               var request = $.ajax({
                  url: "/changeRoles",
                  method: "GET",
                  data:{
                     staff_id : currentManageId,
                     role : changeRole,
                     warehouse_id : cWID,
                     "_token": "{{ csrf_token() }}"
                  }
                  }
               );

               request.done(function (response, textStatus, jqXHR){
                     console.log(response);
                        if(response == "Success"){
                           document.getElementById('loading').style.display = "none";
                           Swal.fire({
                              title: 'Successfully updated staff role',
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
                           document.getElementById('loading').style.display = "none";
                           showError("Error",response);
                        }
               });

               request.fail(function (){
                      document.getElementById('loading').style.display = "none";
                     // Show error
                     showError("Error","Please ensure server is turned on");
               });
         }
        
     }
     //alert(currentManageId);
  }

  function showSuccess(title,message){
    Swal.fire({
                        icon: 'success',
                        title: title,
                        text: message,
    }); 
  }

  function uploadWarehouse(warehouseId,warehouseName){
   firebaseConfig = {
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
   firebase.database().ref('franko_ware_house/goods_reception/' + warehouseId).set({
                     reason: "",
                     status:"closed",
                     requisition:""
                  }, (error) => {
                     if (error) {
                        showError("Please check network",error);
                     } else {
                       Swal.fire({
                              title: 'Successfully added warehouse',
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
                  });
  }

</script>
@endsection

@section('pageCss')
 <link rel="stylesheet" href={{ asset('css/product.css') }} />
 <link rel="stylesheet" href={{ asset('css/warehouse.css') }} />
 <link rel="stylesheet" href={{ asset('css/setup.css') }} />
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-database.js"></script>
 <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-auth.js"></script>
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
      <div class="sideMenuIconContainer" id="selectedMenu">
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
   @if(session()->get('role') !='Accountant')
    
      <div class="sideMenuIconContainer" onclick="loadTransactions()">
         <img class="sideMenuCenterIcon" src={{ asset('icons/transaction.png') }}>
         <div class="sideMenuLeftLabel">
            <label class="sideMenuLeftLabelText">Transactions</label>
        </div>
         <label style="display:none" id="transactionURL">{{ route('transactionPage') }}</label>
      </div>
  @endif
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

        <!-- Get warehouses-->
        @if(session()->has('ware_house_addition'))
            <script>
               var result = "{{ session()->get('ware_house_addition') }}";
               var myArr = result.split("_");
               console.log("Result: "+myArr);
               var warehouseId = myArr[0];
               var warehouseName = myArr[1];
               uploadWarehouse(warehouseId,warehouseName);
            </script>
        @endif
       
      
        <div id="productBody">
        <div class="productHead">
            <label class="productTitle">Ware House</label>
            <div class="addWareHouseCon" onclick="addWareHousePopUp()" >
                <img class="addBtnConIcon" src= {{ asset('icons/plus.png') }} >
                <label class="addBtnConLable">Add Warehouse</label>
            </div>
            <div id="searchWareHouseCon">
                <img class="addBtnConIcon" src= {{ asset('icons/ashSearch.png') }} >
                <input id="addBtnSearchEntry" type="text" placeholder="Search Ware House..">
            </div>
            </div>

            <div class="productContent">
            <div class="tableContainer">
                <table id="wareHouseTable">
                    <thead>
                        <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Branch</th>
                        <th>Manager</th>
                        <th>Delete</th>
                        </tr> 
                    </thead> 
                    <tbody id="wareHouseTableBody">
                        @foreach ($warehouse_managers as $members)
                           <tr>
                             <td>{{ $members->warehouse->wname }}</td>
                             <td>{{ $members->warehouse->wlocation }}</td>
                             <td>{{ $members->warehouse->wbranch }}</td>
                             <td>{{ $members->admin->name }}</td>
                             <td><img src="../icons/delete.png" style="width:20;height:20;" onclick="deleteClicked('{{ $members->ware_house_id }}')"></td>
                           </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
                <div class="productCategoryContainer">
                <div class="productCategoryTopDivider"> </div>
                <div class="productCategory" onclick="rolesOnClick()">
                    <label class="wareHousePermissionLable">Roles</label>
                </div>
                </div>
            </div>
            
        </div>

        <div id="rolesBody">
        <div class="productHead">
            <label class="productTitle">Ware House Roles</label>
            
        </div>
        <div class="productContent">
            <div class="tableContainer">
                <div id="pendingWareHouseReg">
                    <label class="pendingRegTxt">Pending Registration</label>
                    @foreach ($inactive_members as $row2)
                        <div class="pendingWareHouseRegListItem">
                           <div class="bottomLine"></div>
                           <label class="pendingWareHouseTxt">{{ $row2->admin->name }}</label>
                           <button class="pendingUserBtn buttonDesign2" onclick="acceptRegistration('{{ $row2->admin->id }}','{{ $row2->admin->name }}')">Confirm</button>
                        </div>
                    @endforeach
                </div>
                <div id="pendingRegSpace" class="spaceY"></div>
                <div id="unassignedStaffCon">
                    <label class="pendingRegTxt">Unassigned Staff</label>
                    @foreach ($unassigned_staff as $row3)
                        <div class="pendingWareHouseRegListItem">
                           <div class="bottomLine"></div>
                           <label class="pendingWareHouseTxt">{{ $row3->admin->name }}</label>
                           <button class="pendingUserBtn buttonDesign2" onclick="assignStaff('{{ $row3->admin->id }}')">Assign</button>
                        </div>
                    @endforeach
                </div>
                <div id="pendingRegSpace" class="spaceY"></div>
                <div id="unManagedWareHouses">
                    <label class="pendingRegTxt">Unmanaged WareHouses</label>
                    <table id="unManagedWareHouseTable">
                    <thead>
                        <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Branch</th>
                        
                        </tr> 
                    </thead> 
                    <tbody id="unManagedWareHouseTableBody">
                        @foreach ($unmanaged_warehouse as $warehouse)
                           <tr>
                              <td>{{ $warehouse->wname }}</td>
                              <td>{{ $warehouse->wlocation }}</td>
                              <td>{{ $warehouse->wbranch }}</td>
                           </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
                <div class="spaceY"></div>
                <div id="wareHouseRolesCon">
                    <label class="pendingRegTxt">WareHouse Staff</label>
                    <table id="wareHouseRoles">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Ware House</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr> 
                    </thead>
                    <tbody id="wareHouseRolesTableBody">
                        @foreach ($active_members as $members)
                            <tr>
                               <td> {{ $members->admin->name }} </td>
                               <td> {{ $members->warehouse->wname }} </td>
                               <td> {{ $members->role }} </td>
                               <td> <button class="assignBtn" onclick="changeRoles('{{ $members->admin->id }}','{{ $members->ware_house_id }}')">Change Role</button> </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="productCategoryContainer">
                <div class="productCategoryTopDivider"> </div>
                <div class="productCategory" onclick="wareHouseOnClick()">
                    <label class="wareHousePermissionLable2">Ware House</label>
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

        <div id="addWareHousePopup">
            <div id="addWareHousePopupCenter">
              <img onclick="closeAddWareHouse()" class="cancel" src=  {{ asset('icons/cancel.png') }} >
               <div class="addProductTitleCon">
                  <label class="popUpTitle">Add Ware House</label>
               </div>
               <form action="{{ route('addWarehouse') }}" method="POST">
                  @csrf
                  <input class="inputDesign" id="addWareHouseName" name='ware_house_name' type="text" placeholder="Name">
                     @error('ware_house_name')
                        <script>
                           showError('Error',"{{ $message }}");
                        </script>
                     @enderror
                  <input class="inputDesign" id="addWareHouseLocation" name='ware_house_location' type="text" placeholder="Location">
                     @error('ware_house_location')
                        <script>
                           showError('Error',"{{ $message }}");
                        </script>
                     @enderror
                  <input class="inputDesign" id="addWareHouseBranch" name='ware_house_branch' type="text" placeholder="Branch">
                     @error('ware_house_branch')
                        <script>
                           showError('Error',"{{ $message }}");
                        </script>
                     @enderror
                  <button type="submit" class="buttonDesign" id="addWareHouseBtn" >Register Ware House</button>
               </form>
           
            </div>
        </div>

        <div id="assignStaffPopUp">
            <div id="assignStaffPopUpCenter">
            <img onclick="closeAssignStaff()" class="cancel" src={{ asset('icons/cancel.png') }}>
            <div class="addProductTitleCon">
                <label class="popUpTitle">Assign Staff</label>
            </div>
            <select class="selectDesign" id="warehouseSelect">
                <option value="vol">Select Warehouse </option>
            </select>
            @foreach ($warehouses as $row)
               <script>
                  var value = "{{ $row->wname }}";
                  console.log(value);
                  var newOption = document.createElement('option');
                  newOption.value = "{{ $row->id }}";
                  newOption.innerHTML = "{{ $row->wname }}";
                  document.getElementById("warehouseSelect").appendChild(newOption);
               </script>
            @endforeach
            <select class="selectDesign" id="roleSelect">
                <option value="vol">Select Role </option>
                <option value="Manager">Warehouse Manager </option>
                <option value="Staff">Warehouse Staff </option>
            </select>
            <button class="buttonDesign" id="assignStaffToWareHouseBtn" onclick="assignStaffToWarehouse()">Assign Staff</button>
            </div>
        </div>

        <div id="assignWarehousePopUp">
            <div id="assignWareHousePopUpCenter">
            <img onclick="closeAssignWareHouse()" class="cancel" src={{ asset('icons/cancel.png') }}>
            <div class="addProductTitleCon">
                <label class="popUpTitle">Assign WareHouse Manager</label>
            </div>
            <div id="wareHouseNameTxtCon">
                <label id="wareHouseNameTxt">Ware House Name</label>
            </div>
            <select class="selectDesign" id="wareHouseStaffAssign">
                <option value="vol">Select Staff </option>
            </select>
            <button class="buttonDesign" id="assignWareHouseManager" onclick="assignWarehouseManager()">Assign Manager</button>
            </div>
        </div>

        <div id="changeRolePopUp">
            <div id="changeRolePopUpCenter">
            <img onclick="closeChangeRoles()" class="cancel" src={{ asset('icons/cancel.png') }}>
            <div class="addProductTitleCon">
                <label class="popUpTitle">Change Roles</label>
            </div>
            <select class="selectDesign" id="changeRoleSelect">
                <option value="vol">Select Role </option>
                <option value="Manager">Warehouse Manager </option>
                <option value="Staff">Warehouse Staff </option>
            </select>
            <button class="buttonDesign" id="assignNewRoleBtn"onclick="assignNewRole()">Assign Role</button>
            </div>
        </div>

        <div class="showLoading"id="loading">
           <div class="loader">Loading...</div>
        </div>
@endsection