<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!--<link rel="stylesheet" href="node_modules/sweetalert2/dist/sweetalert2.css">-->
    <script src={{ URL("js/sweetalert2.all.js") }}></script>
    <script type="text/javascript" src="../js/setup.js"></script> 
    <script src={{ URL("js/jquery.js") }}></script>
    <link rel="stylesheet" href={{ asset('css/index.css') }} />
    <link rel="stylesheet" href={{ asset('css/sweetalert2.css') }} />
    <link rel="stylesheet" href={{ asset('css/setup.css') }} />
    <title>Sign</title>
</head>
<script>
    function disableBack() { window.history.forward(); }
    setTimeout("disableBack()", 0);
    window.onunload = function () { null };
    var counter = 0,totalWarehouses=0;
    var warehouseId = "",warehouseName = "",accountId = "",accountName="",accountRole="",accountUserId="";
    function registerOnClick(){
        document.getElementById("loginContainer").style.display = "none";
        document.getElementById("registrationCon").style.display = "block";
        console.log("Register");
        //$('#name').val = ""; 
        document.getElementById('name').value = '';
        document.getElementById('id').value = '';
        document.getElementById('regPassword').value = '';
        document.getElementById('confirmRegPassword').value = '';
        document.getElementById('adminId').value = '';
    }

  function loginOnClick(){
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("registrationCon").style.display = "none";
  }

  function currentWarehouse(e){
   
    
    //alert(counter);
    for(var i=0;i<counter;i++){
        var id = "warehouseSelectItem"+i;
        document.getElementById(id).style.background = "white";
        var child = document.getElementById(id).childNodes;
        child[0].style.color = "black";
    }
    var id2 = e.id;
    var children = document.getElementById(id2).childNodes;
    children[0].style.color = "white";
    document.getElementById(id2).style.background = "#009933";

    //Get warehouse Id = 
    warehouseId = children[1].id;

    //Get warehouse Name = 
    warehouseName = children[0].innerHTML;
 
    
  }

  function showError(title,message){
    Swal.fire({
                        icon: 'error',
                        title: title,
                        text: message,
                        
    }); 
  }

  function showSuccess(title,message){
    Swal.fire({
            icon: 'success',
            title: title,
            text: message,
    }); 
  }

  function goHome(){
    if(warehouseId!=""){
        var accountsRequest  = $.ajax({
                                        url:"/accountant",
                                        method: "GET",
                                        data:{
                                            wid:warehouseId,
                                            wname:warehouseName,
                                            accountId:accountId,
                                            accountName:accountName,
                                            accountUserId:accountUserId,
                                            "_token": "{{ csrf_token() }}"
                                        }
        });
        accountsRequest.done(function (response, textStatus, jqXHR){
          // console.log("Response: "+response);
          if(response == "Success"){
            window.location.replace('/home');
          }
        });
        accountsRequest.fail(function (){
          showError("Failure","Please check Internet")
        });
    }
    else{
        showError("Error","Please select ware house to proceed");
    }  
  }

  function login(){
     

     $id = $("#userId").val();
     $password = $("#userPassword").val();

     if($id.length == 0){
       showError("Invalid Enrty","User Id required ");
     }
     else if($password.length<6){
       showError("Invalid Enrty","User Password should be at least 6 characters ");  
     }
     else{
        
       alert("Ready to rambo");
      
     }
  
   }

</script>    
<body>

    
    <div id="mainContainer">
        <div id="centerContainer">
          <div id="leftImage">
             <img id="leftImg" src={{ asset('images/ware_house.jpg') }}>
          </div>
          <div id="rightCon">
             <div class=centerVertically id="loginContainer">
                 <div class=lCon>
                     <label class="title">Franko Ware House</label>
                     <label id="subTitle">Ware house management system</label>
                     <form action="{{ route('loginData') }}" method="POST">
                        @csrf

                        <div id="con1">
                            <input class="inputDesign authTxtInput" name="login_user_id" id="userId" type="text" placeholder="User ID">
                        </div>
                        @error('login_user_id')
                            <script>
                                showError('Error',"{{ $message }}");
                            </script>
                        @enderror
                        <div class="con2">
                            <input class="inputDesign authTxtInput" name="login_password" id="userPassword" type="password" placeholder="Password">
                        </div>
                        @error('login_password')
                            <script>
                                showError('Error',"{{ $message }}");
                            </script>
                        @enderror
                        <div class="con2">
                            <button type="submit" class="buttonDesign authTxtInput" id="loginBtn">Log In</button>
                        </div>
                     </form>
                      <div class="con2">
                          <label id="register" onclick="registerOnClick()">Register</label>
                      </div>
                 </div>
             </div>
 
             <div class=centerVertically id=registrationCon>
                 <div class=lCon>
                     <label class="title">Franko Ware House</label>
                     <label id="subTitle">Ware house management system</label>
                     <form action="{{ route('saveData') }}" method="POST">
                        @csrf
                        
                        @if($errors->any())
                            <script>
                                showError("Error","{{$errors->first()}}");
                            </script>
                        
                        @endif

                        @if(Session::has('success'))

                            <script>
                                showSuccess('Registration Success','Please contact administrator for account verification');
                            </script>

                        @endif

                        <div id="con1">
                            <input class="inputDesign authTxtInput" name="name" id="name" type="text" placeholder="Name">
                        </div>
                            @error('name')
                                <script>
                                    showError('Error',"{{ $message }}");
                                </script>
                            @enderror
                        <div class="con2">
                            <input class="inputDesign authTxtInput" id="id" name="user_id" type="text" placeholder="User ID">
                        </div>
                            @error('user_id')
                                <script>
                                    showError('Error',"{{ $message }}");
                                </script>
                            @enderror
                        <div class="con2">
                            <input class="inputDesign authTxtInput" id="regPassword" name="password" type="password" placeholder="Password">
                        </div>
                            @error('password')
                                <script>
                                    showError('Error',"{{ $message }}");
                                </script>
                            @enderror
                        <div class="con2">
                            <input class="inputDesign authTxtInput" id="confirmRegPassword" name="confirm_password" type="password" placeholder="Confirm Password">
                        </div>
                            @error('confirm_password')
                                <script>
                                    showError('Error',"{{ $message }}");
                                </script>
                            @enderror
                        <div class="con2">
                            <input class="inputDesign authTxtInput" id="adminId" type="text" name="adminId" placeholder="Admin ID">
                        </div>
                            @error('adminId')
                            <script>
                                showError('Error',"{{ $message }}");
                            </script>
                            @enderror
                        <div class="con2">
                            <button type="submit" class="buttonDesign authTxtInput"  id="registerBtn">Register</button>
                        </div>
                    </form>
                     <div class="con2">
                         <label id="login" onclick="loginOnClick()">Login</label>
                     </div>
                 </div>
             </div>
          </div>
        </div>
     </div>
     <div id="warehouseSelectModalCon">
        <div id="centerWarehouseModal">
           <div class="welcomeCon">
              <label id="headingTxt">Welcome 
                  @isset($accountant)
                      {{ $accountant->name }}
                      <script>
                          accountId = "{{ $accountant->id }}";
                          accountName = "{{ $accountant->name }}";
                          accountUserId = "{{ $accountant->user_id }}";
                      </script>
                  @endisset
              </label>
           </div>
           <div class="welcomeCon2">
                <label id="subHeading">
                    Accountant Profile
                </label>
           </div>
           <div class="welcomeCon2">
                <label id="subHeading">
                    Select Warehouse To Proceed.
                </label>
           </div>
           <div class="verticalSpace"></div>
           @isset($accountant)
                @foreach ($warehouses as $data)
                    <script>
                        var warehouseItem = document.createElement('div');
                        warehouseItem.classList.add('warehouseSelectItem');
                        warehouseItem.id = "warehouseSelectItem"+counter;
                        warehouseItem.setAttribute('onclick', 'currentWarehouse(this)');

                        //label
                        var warehouseLable = document.createElement('label');
                        warehouseLable.classList.add('warehouseLable');
                        warehouseLable.innerHTML = "{{ $data->wname }}";

                        //Img
                        var warehouseTick = document.createElement('img');
                        warehouseTick.classList.add('warehouseTick');
                        warehouseTick.setAttribute("src","icons/correct.png");
                        warehouseTick.id = "{{ $data->id }}";


                        warehouseItem.appendChild(warehouseLable);
                        warehouseItem.appendChild(warehouseTick);

                        var centerWarehouse = document.getElementById("centerWarehouseModal");
                        centerWarehouse.appendChild(warehouseItem);
                        counter = counter + 1;

                    </script>
                @endforeach 
                <div id="containerCon">
                   <button id="selectWarehouse" onclick="goHome()">Proceed</button>
                </div>
           @endisset
        </div>
     </div>
     <script>

        var accountant = "{{ $accountant ?? '' }}";
        if(accountant != ''){
            document.getElementById('warehouseSelectModalCon').style.display = "block";
        }
    </script>
</body>
</html>